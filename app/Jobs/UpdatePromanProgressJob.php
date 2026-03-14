<?php

namespace App\Jobs;

use App\Models\Todo;
use App\Services\PromanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePromanProgressJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $todoId
    ) {}

    public function handle(PromanService $promanService): void
    {
        $todo = Todo::with(['workspace.user', 'promanTask'])->find($this->todoId);
        if (! $todo) {
            return;
        }

        $promanTask = $todo->promanTask;
        if (! $promanTask || $promanTask->progress_completed_at !== null) {
            return;
        }

        $user = $todo->workspace->user;
        if (! $user->hasPromanCredentials()) {
            return;
        }

        $ssoToken = $promanService->login($user->proman_username, $user->getPromanPassword());
        $promanService->updateProgress($user->proman_api_key, $ssoToken, $promanTask->id_task);
        $promanTask->update(['progress_completed_at' => now()]);
    }
}
