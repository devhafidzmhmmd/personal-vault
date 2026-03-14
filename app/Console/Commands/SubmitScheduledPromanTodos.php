<?php

namespace App\Console\Commands;

use App\Jobs\SubmitPromanTaskJob;
use App\Models\Todo;
use Illuminate\Console\Command;

class SubmitScheduledPromanTodos extends Command
{
    protected $signature = 'proman:submit-scheduled';

    protected $description = 'Submit todos that are done and scheduled for Proman';

    public function handle(): int
    {
        $todos = Todo::query()
            ->where('status', Todo::STATUS_DONE)
            ->whereNotNull('proman_project_id')
            ->whereNull('proman_submitted_at')
            ->where('proman_submit_scheduled_at', '<=', now())
            ->pluck('id');

        foreach ($todos as $todoId) {
            SubmitPromanTaskJob::dispatch($todoId);
        }

        $this->info("Dispatched {$todos->count()} todo(s) for Proman submit.");

        return self::SUCCESS;
    }
}
