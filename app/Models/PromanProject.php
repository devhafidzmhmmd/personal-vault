<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromanProject extends Model
{
    protected $fillable = ['workspace_id', 'id_project', 'name'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class, 'proman_project_id');
    }
}
