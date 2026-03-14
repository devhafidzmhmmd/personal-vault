<?php

namespace Tests\Feature;

use App\Models\AbsenHistory;
use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AbsenTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithVault(): User
    {
        $vaultCrypto = app(VaultCryptoService::class);
        $masterPassword = 'secret-master-password';
        $salt = $vaultCrypto->generateSalt();

        return User::factory()->create([
            'master_password_hash' => Hash::make($masterPassword),
            'master_password_salt' => $salt,
        ]);
    }

    private function vaultSessionArray(User $user, Workspace $workspace): array
    {
        $vaultCrypto = app(VaultCryptoService::class);
        $key = $vaultCrypto->deriveKey('secret-master-password', $user->master_password_salt);

        return [
            'vault_key' => base64_encode($key),
            'current_workspace_id' => $workspace->id,
        ];
    }

    public function test_submit_absen_requires_settings_configured(): void
    {
        Http::fake();

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.submit'), [
                'tgl_absen' => '2026-03-10',
                'longitude' => 106.860504,
                'latitude' => -6.295123,
                'type' => 'WFA',
                'keterangan' => 'wfa',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        Http::assertNothingSent();
    }

    public function test_submit_absen_success(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $user = $this->createUserWithVault();
        $user->update([
            'proman_user_id' => 'c9b8b818-b6bc-4b88-9b04-f25cb2c92df9',
            'proman_token' => '6bc046cf-ac2e-4d63-8848-87635209f27e',
        ]);
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.submit'), [
                'tgl_absen' => '2026-03-10',
                'longitude' => 106.860504,
                'latitude' => -6.295123,
                'type' => 'WFA',
                'keterangan' => 'wfa',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'hit_count' => 1]);

        $this->assertDatabaseHas('absen_histories', [
            'user_id' => $user->id,
            'hit_count' => 1,
        ]);
        $this->assertNotNull(AbsenHistory::where('user_id', $user->id)->whereDate('tgl_absen', '2026-03-10')->first());

        Http::assertSent(function ($request) use ($user) {
            return str_contains($request->url(), 'dcktrp.jakarta.go.id')
                && str_contains($request->url(), $user->proman_user_id)
                && str_contains($request->url(), '106.860504')
                && str_contains($request->url(), '-6.295123')
                && str_contains($request->url(), 'WFA')
                && str_contains($request->url(), '2026-03-10')
                && $request->hasHeader('token', $user->proman_token);
        });
    }

    public function test_submit_absen_validates_required_fields(): void
    {
        $user = $this->createUserWithVault();
        $user->update([
            'proman_user_id' => 'test-id',
            'proman_token' => 'test-token',
        ]);
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.submit'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tgl_absen', 'longitude', 'latitude', 'type']);
    }

    public function test_submit_absen_validates_type_enum(): void
    {
        $user = $this->createUserWithVault();
        $user->update([
            'proman_user_id' => 'test-id',
            'proman_token' => 'test-token',
        ]);
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.submit'), [
                'tgl_absen' => '2026-03-10',
                'longitude' => 106.86,
                'latitude' => -6.29,
                'type' => 'INVALID',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_store_location_saves_to_user(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.locations.store'), [
                'name' => 'Kantor',
                'latitude' => -6.295123,
                'longitude' => 106.860504,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertCount(1, $user->saved_locations);
        $this->assertSame('Kantor', $user->saved_locations[0]['name']);
        $this->assertSame(-6.295123, $user->saved_locations[0]['latitude']);
        $this->assertSame(106.860504, $user->saved_locations[0]['longitude']);
    }

    public function test_submit_absen_rejects_when_already_two_hits(): void
    {
        Http::fake();

        $user = $this->createUserWithVault();
        $user->update([
            'proman_user_id' => 'test-id',
            'proman_token' => 'test-token',
        ]);
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        AbsenHistory::create([
            'user_id' => $user->id,
            'tgl_absen' => '2026-03-10',
            'hit_count' => 2,
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.submit'), [
                'tgl_absen' => '2026-03-10',
                'longitude' => 106.86,
                'latitude' => -6.29,
                'type' => 'WFA',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        Http::assertNothingSent();
    }

    public function test_submit_absen_increments_history_on_second_hit(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        $user = $this->createUserWithVault();
        $user->update([
            'proman_user_id' => 'test-id',
            'proman_token' => 'test-token',
        ]);
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        AbsenHistory::create([
            'user_id' => $user->id,
            'tgl_absen' => '2026-03-10',
            'hit_count' => 1,
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->postJson(route('absen.submit'), [
                'tgl_absen' => '2026-03-10',
                'longitude' => 106.86,
                'latitude' => -6.29,
                'type' => 'WFO',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'hit_count' => 2]);

        $history = AbsenHistory::where('user_id', $user->id)->whereDate('tgl_absen', '2026-03-10')->first();
        $this->assertNotNull($history);
        $this->assertSame(2, $history->hit_count);
    }

    public function test_unauthenticated_user_cannot_submit_absen(): void
    {
        $response = $this->postJson(route('absen.submit'), [
            'tgl_absen' => '2026-03-10',
            'longitude' => 106.86,
            'latitude' => -6.29,
            'type' => 'WFA',
        ]);

        $response->assertStatus(401);
    }
}
