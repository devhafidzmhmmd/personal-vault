<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TodoImportJsonTest extends TestCase
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

    public function test_import_json_requires_proman_enabled(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => false]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('todos.import-json'), ['json' => '[{"title": "Task 1"}]']);

        $response->assertStatus(403);
    }

    public function test_import_json_creates_todos(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);
        $project = $workspace->promanProjects()->create(['id_project' => 'proj-uuid-1', 'name' => 'Proj 1']);

        $json = json_encode([
            ['title' => 'Task A', 'description' => 'Desc A', 'due_date' => '2026-01-25'],
            ['title' => 'Task B', 'id_project' => 'proj-uuid-1'],
        ]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('todos.import-json'), ['json' => $json]);

        $response->assertRedirect(route('todos.index'));
        $response->assertSessionHas('success');

        $this->assertSame(2, $workspace->todos()->count());
        $todoA = $workspace->todos()->where('title', 'Task A')->first();
        $this->assertNotNull($todoA);
        $this->assertSame('Desc A', $todoA->description);
        $this->assertNull($todoA->proman_project_id);

        $todoB = $workspace->todos()->where('title', 'Task B')->first();
        $this->assertNotNull($todoB);
        $this->assertSame($project->id, $todoB->proman_project_id);
    }

    public function test_import_json_with_todos_key(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);

        $json = json_encode(['todos' => [['title' => 'Task 1'], ['title' => 'Task 2']]]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('todos.import-json'), ['json' => $json]);

        $response->assertRedirect(route('todos.index'));
        $this->assertSame(2, $workspace->todos()->count());
    }

    public function test_import_json_invalid_returns_error(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->post(route('todos.import-json'), ['json' => 'not valid json {']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('json');
    }
}
