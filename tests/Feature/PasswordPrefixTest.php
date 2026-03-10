<?php

namespace Tests\Feature;

use App\Models\Password;
use App\Models\PasswordPrefix;
use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordPrefixTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithVault(): User
    {
        $vaultCrypto = app(VaultCryptoService::class);
        $masterPassword = 'secret-master-password';
        $salt = $vaultCrypto->generateSalt();

        $user = User::factory()->create([
            'master_password_hash' => Hash::make($masterPassword),
            'master_password_salt' => $salt,
        ]);

        return $user;
    }

    private function vaultSessionArray(User $user, Workspace $workspace): array
    {
        $vaultCrypto = app(VaultCryptoService::class);
        $masterPassword = 'secret-master-password';
        $key = $vaultCrypto->deriveKey($masterPassword, $user->master_password_salt);

        return [
            'vault_key' => base64_encode($key),
            'current_workspace_id' => $workspace->id,
        ];
    }

    public function test_password_prefix_index_requires_vault_and_redirects_without_workspace(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('password-prefixes.index'));
        $response->assertRedirect(route('workspace.select'));
    }

    public function test_password_prefix_index_shows_list(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $prefix = $workspace->passwordPrefixes()->create(['name' => 'Work']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('password-prefixes.index'));

        $response->assertStatus(200);
        $response->assertSee('Work');
    }

    public function test_password_prefix_create_and_store(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('password-prefixes.store'), [
                'name' => 'Personal',
                'shortcut_id' => '',
            ]);

        $response->assertRedirect(route('password-prefixes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('password_prefixes', [
            'workspace_id' => $workspace->id,
            'name' => 'Personal',
            'shortcut_id' => null,
        ]);
    }

    public function test_password_prefix_store_with_optional_shortcut(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $shortcut = $workspace->shortcuts()->create([
            'user_id' => $user->id,
            'title' => 'Gmail',
            'url' => 'https://gmail.com',
            'order' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('password-prefixes.store'), [
                'name' => 'Work',
                'shortcut_id' => (string) $shortcut->id,
            ]);

        $response->assertRedirect(route('password-prefixes.index'));

        $prefix = PasswordPrefix::where('workspace_id', $workspace->id)->where('name', 'Work')->first();
        $this->assertNotNull($prefix);
        $this->assertSame($shortcut->id, $prefix->shortcut_id);
    }

    public function test_password_prefix_edit_and_update(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $prefix = $workspace->passwordPrefixes()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->put(route('password-prefixes.update', $prefix), [
                'name' => 'New Name',
                'shortcut_id' => '',
            ]);

        $response->assertRedirect(route('password-prefixes.index'));
        $prefix->refresh();
        $this->assertSame('New Name', $prefix->name);
    }

    public function test_password_prefix_destroy(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $prefix = $workspace->passwordPrefixes()->create(['name' => 'To Delete']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->delete(route('password-prefixes.destroy', $prefix));

        $response->assertRedirect(route('password-prefixes.index'));
        $this->assertDatabaseMissing('password_prefixes', ['id' => $prefix->id]);
    }

    public function test_password_can_have_prefix_and_display_name_format(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $prefix = $workspace->passwordPrefixes()->create(['name' => 'Work']);

        $vaultCrypto = app(VaultCryptoService::class);
        $key = $vaultCrypto->deriveKey('secret-master-password', $user->master_password_salt);
        $encrypted = $vaultCrypto->encrypt('plainpassword', $key);

        $password = $workspace->passwords()->create([
            'prefix_id' => $prefix->id,
            'type' => 'app',
            'name' => 'Gmail',
            'username' => 'user@example.com',
            'password_encrypted' => $encrypted,
            'url' => null,
            'notes' => null,
        ]);

        $password->load('prefix');
        $this->assertSame('[Work] Gmail', $password->display_name);
    }

    public function test_password_without_prefix_shows_name_only(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $vaultCrypto = app(VaultCryptoService::class);
        $key = $vaultCrypto->deriveKey('secret-master-password', $user->master_password_salt);
        $encrypted = $vaultCrypto->encrypt('plainpassword', $key);

        $password = $workspace->passwords()->create([
            'prefix_id' => null,
            'type' => 'app',
            'name' => 'Netflix',
            'username' => null,
            'password_encrypted' => $encrypted,
            'url' => null,
            'notes' => null,
        ]);

        $this->assertSame('Netflix', $password->display_name);
    }

    public function test_passwords_index_filter_by_prefix(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $prefixWork = $workspace->passwordPrefixes()->create(['name' => 'Work']);
        $prefixPersonal = $workspace->passwordPrefixes()->create(['name' => 'Personal']);

        $vaultCrypto = app(VaultCryptoService::class);
        $key = $vaultCrypto->deriveKey('secret-master-password', $user->master_password_salt);
        $encrypted = $vaultCrypto->encrypt('secret', $key);

        $workspace->passwords()->create([
            'prefix_id' => $prefixWork->id,
            'type' => 'app',
            'name' => 'Gmail',
            'username' => null,
            'password_encrypted' => $encrypted,
            'url' => null,
            'notes' => null,
        ]);
        $workspace->passwords()->create([
            'prefix_id' => $prefixPersonal->id,
            'type' => 'app',
            'name' => 'Netflix',
            'username' => null,
            'password_encrypted' => $encrypted,
            'url' => null,
            'notes' => null,
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('passwords.index', ['prefix_id' => $prefixWork->id]));

        $response->assertStatus(200);
        $response->assertSee('[Work] Gmail');
        $response->assertDontSee('[Personal] Netflix');
    }

    public function test_password_store_with_prefix_id(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $prefix = $workspace->passwordPrefixes()->create(['name' => 'Work']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('passwords.store'), [
                'type' => 'app',
                'name' => 'Slack',
                'prefix_id' => (string) $prefix->id,
                'username' => 'dev',
                'password' => 'plainsecret',
                'url' => '',
                'notes' => '',
            ]);

        $response->assertRedirect(route('passwords.index'));

        $password = Password::where('workspace_id', $workspace->id)->where('name', 'Slack')->first();
        $this->assertNotNull($password);
        $this->assertSame($prefix->id, $password->prefix_id);
    }
}
