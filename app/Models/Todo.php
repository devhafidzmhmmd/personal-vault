<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Todo extends Model
{
    use HasFactory;

    public const STATUS_TODO = 'todo';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    protected $fillable = [
        'workspace_id',
        'shortcut_id',
        'proman_project_id',
        'title',
        'description',
        'status',
        'due_date',
        'position',
        'proman_submit_scheduled_at',
        'proman_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'position' => 'integer',
            'proman_submit_scheduled_at' => 'datetime',
            'proman_submitted_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function shortcut(): BelongsTo
    {
        return $this->belongsTo(Shortcut::class);
    }

    public function promanProject(): BelongsTo
    {
        return $this->belongsTo(PromanProject::class);
    }

    public function promanTask(): HasOne
    {
        return $this->hasOne(PromanTask::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_TODO => __('Todo'),
            self::STATUS_IN_PROGRESS => __('Sedang dikerjakan'),
            self::STATUS_DONE => __('Selesai'),
        ];
    }
}
