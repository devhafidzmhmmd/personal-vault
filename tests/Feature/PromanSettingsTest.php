<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromanSettingsTest extends TestCase
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

    public function test_proman_settings_page_renders(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('settings.proman'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Proman');
    }

    public function test_proman_settings_update(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->put(route('settings.proman.update'), [
                'proman_username' => 'user@example.com',
                'proman_password' => 'secret123',
                'proman_api_key' => 'api-key-123',
            ]);

        $response->assertRedirect(route('settings.proman'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('user@example.com', $user->proman_username);
        $this->assertSame('api-key-123', $user->proman_api_key);
        $this->assertNotNull($user->getPromanPassword());
        $this->assertSame('secret123', $user->getPromanPassword());
    }

    public function test_proman_settings_validation_requires_username_and_api_key(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->put(route('settings.proman.update'), []);

        $response->assertSessionHasErrors(['proman_username', 'proman_api_key']);
    }

    public function test_unauthenticated_user_cannot_access_proman_settings(): void
    {
        $response = $this->get(route('settings.proman'));
        $response->assertRedirect(route('login'));
    }
}
