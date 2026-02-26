<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomEvent extends Model
{
    use HasFactory;

    protected $fillable = ['workspace_id', 'title', 'event_date', 'description'];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
