<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromanTask extends Model
{
    protected $fillable = [
        'todo_id',
        'id_task',
        'id_project',
        'response_data',
        'progress_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'response_data' => 'array',
            'progress_completed_at' => 'datetime',
        ];
    }

    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }

    public function isCompleted(): bool
    {
        return $this->progress_completed_at !== null;
    }
}
