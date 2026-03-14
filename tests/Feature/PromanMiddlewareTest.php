<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromanMiddlewareTest extends TestCase
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

    public function test_proman_routes_return_403_when_workspace_proman_disabled(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => false]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('settings.workspace.proman.index'));

        $response->assertStatus(403);
    }

    public function test_proman_routes_accessible_when_workspace_proman_enabled(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('settings.workspace.proman.index'));

        $response->assertStatus(200);
    }
}
