<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AbsenSettingsTest extends TestCase
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

    public function test_absen_settings_page_renders(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('settings.absen'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Absensi');
    }

    public function test_absen_settings_update(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->put(route('settings.absen.update'), [
                'proman_user_id' => 'c9b8b818-b6bc-4b88-9b04-f25cb2c92df9',
                'proman_token' => '6bc046cf-ac2e-4d63-8848-87635209f27e',
            ]);

        $response->assertRedirect(route('settings.absen'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('c9b8b818-b6bc-4b88-9b04-f25cb2c92df9', $user->proman_user_id);
        $this->assertSame('6bc046cf-ac2e-4d63-8848-87635209f27e', $user->proman_token);
    }

    public function test_absen_settings_validation_requires_fields(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->put(route('settings.absen.update'), []);

        $response->assertSessionHasErrors(['proman_user_id', 'proman_token']);
    }

    public function test_unauthenticated_user_cannot_access_absen_settings(): void
    {
        $response = $this->get(route('settings.absen'));
        $response->assertRedirect(route('login'));
    }
}
