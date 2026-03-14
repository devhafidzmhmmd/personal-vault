<?php

namespace Tests\Feature;

use App\Models\CustomEvent;
use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomEventSpecialTest extends TestCase
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

    public function test_special_event_appears_on_dashboard_in_other_workspace(): void
    {
        $user = $this->createUserWithVault();
        $workspaceA = $user->workspaces()->create(['name' => 'Workspace A']);
        $workspaceB = $user->workspaces()->create(['name' => 'Workspace B']);

        $eventTitle = 'Special Event Title';
        CustomEvent::create([
            'workspace_id' => $workspaceA->id,
            'title' => $eventTitle,
            'event_date' => now(),
            'event_end_date' => now(),
            'description' => null,
            'is_special' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspaceB))
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee($eventTitle);
    }

    public function test_non_special_event_does_not_appear_in_other_workspace(): void
    {
        $user = $this->createUserWithVault();
        $workspaceA = $user->workspaces()->create(['name' => 'Workspace A']);
        $workspaceB = $user->workspaces()->create(['name' => 'Workspace B']);

        $eventTitle = 'Normal Event Only In A';
        CustomEvent::create([
            'workspace_id' => $workspaceA->id,
            'title' => $eventTitle,
            'event_date' => now(),
            'event_end_date' => now(),
            'description' => null,
            'is_special' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspaceB))
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee($eventTitle);
    }

    public function test_store_and_update_event_with_is_special_flag(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test']);
        $session = $this->vaultSessionArray($user, $workspace);

        $response = $this->actingAs($user)
            ->withSession($session)
            ->post(route('custom-events.store'), [
                'title' => 'My Special Event',
                'event_date' => now()->format('Y-m-d'),
                'event_end_date' => now()->format('Y-m-d'),
                'description' => '',
                'is_special' => '1',
            ]);

        $response->assertRedirect();
        $event = CustomEvent::where('title', 'My Special Event')->first();
        $this->assertNotNull($event);
        $this->assertTrue($event->is_special);

        $response = $this->actingAs($user)
            ->withSession($session)
            ->put(route('custom-events.update', $event), [
                'title' => 'My Special Event',
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_end_date' => $event->event_end_date->format('Y-m-d'),
                'description' => '',
                'is_special' => '0',
            ]);

        $response->assertRedirect();
        $event->refresh();
        $this->assertFalse($event->is_special);
    }
}
