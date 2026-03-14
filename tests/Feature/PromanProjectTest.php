<?php

namespace Tests\Feature;

use App\Models\PromanProject;
use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromanProjectTest extends TestCase
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

    public function test_proman_project_index_requires_proman_enabled(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => false]);

        $response = $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->get(route('settings.workspace.proman.index'));

        $response->assertStatus(403);
    }

    public function test_proman_project_index_and_crud(): void
    {
        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);
        $session = $this->vaultSessionArray($user, $workspace);

        $response = $this->actingAs($user)->withSession($session)->get(route('settings.workspace.proman.index'));
        $response->assertStatus(200);
        $response->assertSee('Project Proman');

        $response = $this->actingAs($user)->withSession($session)->get(route('settings.workspace.proman.create'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->withSession($session)->post(route('settings.workspace.proman.store'), [
            'id_project' => 'uuid-project-1',
            'name' => 'Project Satu',
        ]);
        $response->assertRedirect(route('settings.workspace.proman.index'));
        $response->assertSessionHas('success');

        $project = $workspace->promanProjects()->first();
        $this->assertNotNull($project);
        $this->assertSame('uuid-project-1', $project->id_project);
        $this->assertSame('Project Satu', $project->name);

        $response = $this->actingAs($user)->withSession($session)->put(route('settings.workspace.proman.update', $project), [
            'id_project' => 'uuid-project-1-updated',
            'name' => 'Project Satu Updated',
        ]);
        $response->assertRedirect(route('settings.workspace.proman.index'));
        $project->refresh();
        $this->assertSame('Project Satu Updated', $project->name);

        $response = $this->actingAs($user)->withSession($session)->delete(route('settings.workspace.proman.destroy', $project));
        $response->assertRedirect(route('settings.workspace.proman.index'));
        $this->assertNull(PromanProject::find($project->id));
    }
}
