<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PasswordPrefix extends Model
{
    use HasFactory;

    protected $fillable = ['workspace_id', 'name', 'shortcut_id'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function shortcut(): BelongsTo
    {
        return $this->belongsTo(Shortcut::class);
    }

    public function passwords(): HasMany
    {
        return $this->hasMany(Password::class, 'prefix_id');
    }
}
