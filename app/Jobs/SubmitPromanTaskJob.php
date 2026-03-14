<?php

namespace App\Jobs;

use App\Models\PromanTask;
use App\Models\Todo;
use App\Services\PromanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SubmitPromanTaskJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $todoId
    ) {}

    public function handle(PromanService $promanService): void
    {
        $todo = Todo::with(['workspace.user', 'promanProject'])->find($this->todoId);
        if (! $todo) {
            return;
        }

        $user = $todo->workspace->user;
        if (! $todo->workspace->proman_enabled || ! $user->hasPromanCredentials()) {
            return;
        }

        if (! $todo->promanProject) {
            Log::warning('SubmitPromanTaskJob: todo has no proman_project', ['todo_id' => $todo->id]);

            return;
        }

        $ssoToken = $promanService->login($user->proman_username, $user->getPromanPassword());
        $dueDate = $todo->due_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $payload = [
            'id_project' => $todo->promanProject->id_project,
            'task' => $todo->title,
            'description' => $todo->description ?? '',
            'id_type_task' => '2',
            'priority' => 2,
            'assign_to' => $user->proman_user_id ?? '',
            'start_date' => $dueDate,
            'end_date' => $dueDate,
        ];

        $response = $promanService->submitTask($user->proman_api_key, $ssoToken, $payload);

        if (($response['status'] ?? false) !== true || empty($response['data']['id_task'] ?? null)) {
            Log::warning('SubmitPromanTaskJob: create task response invalid', ['todo_id' => $todo->id, 'response' => $response]);

            return;
        }

        $data = $response['data'];
        $idTask = $data['id_task'];
        $idProject = $data['id_project'] ?? $todo->promanProject->id_project;

        $promanTask = PromanTask::updateOrCreate(
            ['todo_id' => $todo->id],
            [
                'id_task' => $idTask,
                'id_project' => $idProject,
                'response_data' => $data,
            ]
        );

        if ($todo->status === Todo::STATUS_DONE) {
            try {
                $promanService->updateProgress($user->proman_api_key, $ssoToken, $idTask);
                $promanTask->update(['progress_completed_at' => now()]);
            } catch (\Throwable $e) {
                Log::warning('SubmitPromanTaskJob: update-progress failed', ['todo_id' => $todo->id, 'message' => $e->getMessage()]);
            }
        }

        $todo->update(['proman_submitted_at' => now()]);
    }
}
