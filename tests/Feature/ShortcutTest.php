<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShortcutTest extends TestCase
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

    public function test_shortcut_store_fetches_and_saves_favicon(): void
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response(
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='),
                200,
                ['Content-Type' => 'image/png']
            ),
        ]);

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('shortcuts.store'), [
                'title' => 'Google',
                'url' => 'https://www.google.com',
                'icon' => '',
            ]);

        $response->assertRedirect(route('shortcuts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shortcuts', [
            'workspace_id' => $workspace->id,
            'title' => 'Google',
            'url' => 'https://www.google.com',
        ]);

        $shortcut = $workspace->shortcuts()->where('url', 'https://www.google.com')->first();
        $this->assertNotNull($shortcut->favicon_path);
        $this->assertTrue(Storage::disk('public')->exists($shortcut->favicon_path));
    }

    public function test_shortcut_store_continues_when_favicon_fetch_fails(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('shortcuts.store'), [
                'title' => 'Example',
                'url' => 'https://example.com',
                'icon' => '🔗',
            ]);

        $response->assertRedirect(route('shortcuts.index'));

        $shortcut = $workspace->shortcuts()->where('url', 'https://example.com')->first();
        $this->assertNotNull($shortcut);
        $this->assertNull($shortcut->favicon_path);
        $this->assertSame('🔗', $shortcut->icon);
    }

    public function test_shortcut_update_refetches_favicon_when_url_changes(): void
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response(
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='),
                200,
                ['Content-Type' => 'image/png']
            ),
        ]);

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $shortcut = $workspace->shortcuts()->create([
            'user_id' => $user->id,
            'title' => 'Old',
            'url' => 'https://old.example.com',
            'favicon_path' => 'shortcuts/favicons/old.png',
            'order' => 0,
        ]);
        Storage::disk('public')->put('shortcuts/favicons/old.png', 'old-favicon');

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->put(route('shortcuts.update', $shortcut), [
                'title' => 'Google',
                'url' => 'https://www.google.com',
                'icon' => '',
            ]);

        $response->assertRedirect(route('shortcuts.index'));

        $shortcut->refresh();
        $this->assertSame('https://www.google.com', $shortcut->url);
        $this->assertNotNull($shortcut->favicon_path);
        $this->assertStringContainsString('google.com', $shortcut->favicon_path);
        $this->assertFalse(Storage::disk('public')->exists('shortcuts/favicons/old.png'));
    }

    public function test_shortcut_destroy_deletes_favicon_from_storage(): void
    {
        Storage::fake('public');
        $faviconPath = 'shortcuts/favicons/test-123.png';
        Storage::disk('public')->put($faviconPath, 'favicon-content');

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);
        $shortcut = $workspace->shortcuts()->create([
            'user_id' => $user->id,
            'title' => 'Test',
            'url' => 'https://example.com',
            'favicon_path' => $faviconPath,
            'order' => 0,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($faviconPath));

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->delete(route('shortcuts.destroy', $shortcut));

        $response->assertRedirect(route('shortcuts.index'));
        $this->assertModelMissing($shortcut);
        $this->assertFalse(Storage::disk('public')->exists($faviconPath));
    }
}
