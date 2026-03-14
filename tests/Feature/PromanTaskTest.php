<?php

namespace Tests\Feature;

use App\Jobs\SubmitPromanTaskJob;
use App\Jobs\UpdatePromanProgressJob;
use App\Models\PromanTask;
use App\Models\Todo;
use App\Models\User;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PromanTaskTest extends TestCase
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
            'proman_username' => 'user@example.com',
            'proman_api_key' => 'api-key-123',
        ]);
        $user->proman_password = 'password123';
        $user->save();

        return $user;
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

    public function test_submit_proman_task_job_creates_proman_task_and_saves_response(): void
    {
        Http::fake([
            '*auth*' => Http::response(['status' => 'success', 'token' => 'sso-token-here'], 200),
            '*submit*' => Http::response([
                'status' => true,
                'message' => 'Task berhasil disimpan',
                'data' => [
                    'id_task' => 'e53be98e-4bea-479d-baa5-b03c2ecc3d78',
                    'id_project' => '79588779-d690-48ff-a71d-840f54426921',
                    'task' => 'Fix SKPD UKPD',
                    'description' => 'Desc',
                ],
            ], 200),
            '*update-progress*' => Http::response(['status' => true], 200),
        ]);

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);
        $project = $workspace->promanProjects()->create(['id_project' => '79588779-d690-48ff-a71d-840f54426921', 'name' => 'Proj']);
        $todo = $workspace->todos()->create([
            'title' => 'Fix SKPD UKPD',
            'description' => 'Desc',
            'status' => Todo::STATUS_DONE,
            'proman_project_id' => $project->id,
        ]);

        $job = new SubmitPromanTaskJob($todo->id);
        $job->handle(app(\App\Services\PromanService::class));

        $this->assertDatabaseHas('proman_tasks', [
            'todo_id' => $todo->id,
            'id_task' => 'e53be98e-4bea-479d-baa5-b03c2ecc3d78',
            'id_project' => '79588779-d690-48ff-a71d-840f54426921',
        ]);

        $promanTask = PromanTask::where('todo_id', $todo->id)->first();
        $this->assertNotNull($promanTask);
        $this->assertNotNull($promanTask->response_data);
        $this->assertSame('e53be98e-4bea-479d-baa5-b03c2ecc3d78', $promanTask->response_data['id_task'] ?? null);
        $this->assertNotNull($promanTask->progress_completed_at);
        $todo->refresh();
        $this->assertNotNull($todo->proman_submitted_at);
    }

    public function test_update_status_to_done_dispatches_update_proman_progress_job_when_proman_task_exists(): void
    {
        Queue::fake();

        $user = $this->createUserWithVault();
        $workspace = $user->workspaces()->create(['name' => 'Test', 'proman_enabled' => true]);
        $project = $workspace->promanProjects()->create(['id_project' => 'proj-1', 'name' => 'Proj']);
        $todo = $workspace->todos()->create([
            'title' => 'Todo',
            'status' => Todo::STATUS_IN_PROGRESS,
            'proman_project_id' => $project->id,
        ]);
        $todo->promanTask()->create([
            'id_task' => 'task-uuid-1',
            'id_project' => 'proj-1',
            'response_data' => [],
        ]);

        $this->actingAs($user)
            ->withSession($this->vaultSessionArray($user, $workspace))
            ->patch(route('todos.update-status', $todo), ['status' => Todo::STATUS_DONE]);

        Queue::assertPushed(UpdatePromanProgressJob::class, function (UpdatePromanProgressJob $job) use ($todo) {
            return $job->todoId === $todo->id;
        });
    }
}
