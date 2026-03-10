<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ToolJsonToExcelTest extends TestCase
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

    public function test_tools_index_redirects_to_json_to_excel(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('tools.index'));

        $response->assertRedirect(route('tools.json-to-excel'));
    }

    public function test_json_to_excel_page_renders(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('tools.json-to-excel'));

        $response->assertStatus(200);
        $response->assertSee('JSON to Excel');
    }

    public function test_valid_json_produces_excel_download(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $json = json_encode([
            ['name' => 'John', 'email' => 'john@example.com'],
            ['name' => 'Jane', 'email' => 'jane@example.com'],
        ]);

        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertDownload('data.xlsx');
    }

    public function test_file_is_required(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), []);

        $response->assertSessionHasErrors('json_file');
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $file = UploadedFile::fake()->create('data.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
            ]);

        $response->assertSessionHasErrors('json_file');
    }

    public function test_malformed_json_returns_error(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $file = UploadedFile::fake()->createWithContent('bad.json', '{ invalid json }');

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
            ]);

        $response->assertSessionHasErrors('json_file');
    }

    public function test_single_object_json_produces_excel(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $json = json_encode(['name' => 'John', 'email' => 'john@example.com']);
        $file = UploadedFile::fake()->createWithContent('single.json', $json);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertDownload('single.xlsx');
    }

    public function test_nested_values_are_json_encoded(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $json = json_encode([
            ['name' => 'John', 'tags' => ['dev', 'admin']],
        ]);

        $file = UploadedFile::fake()->createWithContent('nested.json', $json);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertDownload('nested.xlsx');
    }

    public function test_nested_json_with_json_path_produces_excel(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $json = json_encode([
            'data' => [
                ['name' => 'John', 'email' => 'john@example.com'],
                ['name' => 'Jane', 'email' => 'jane@example.com'],
            ],
            'meta' => ['total' => 2],
        ]);

        $file = UploadedFile::fake()->createWithContent('api-response.json', $json);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
                'json_path' => 'data',
            ]);

        $response->assertStatus(200);
        $response->assertDownload('api-response.xlsx');
    }

    public function test_nested_json_with_invalid_path_returns_error(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $json = json_encode([
            'data' => [
                ['name' => 'John'],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
                'json_path' => 'nonexistent',
            ]);

        $response->assertSessionHasErrors('json_file');
    }

    public function test_nested_json_with_non_array_path_returns_error(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test Workspace']);

        $json = json_encode([
            'data' => [['name' => 'John']],
            'meta' => ['total' => 1],
        ]);

        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('tools.json-to-excel.convert'), [
                'json_file' => $file,
                'json_path' => 'meta',
            ]);

        $response->assertSessionHasErrors('json_file');
    }

    public function test_unauthenticated_user_cannot_access_tools(): void
    {
        $response = $this->get(route('tools.json-to-excel'));
        $response->assertRedirect(route('login'));
    }
}
