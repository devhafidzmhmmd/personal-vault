<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'proman_enabled'];

    protected function casts(): array
    {
        return [
            'proman_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passwords(): HasMany
    {
        return $this->hasMany(Password::class);
    }

    public function passwordPrefixes(): HasMany
    {
        return $this->hasMany(PasswordPrefix::class);
    }

    public function shortcuts(): HasMany
    {
        return $this->hasMany(Shortcut::class)->orderBy('order');
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class)->orderBy('position')->orderBy('due_date');
    }

    public function customEvents(): HasMany
    {
        return $this->hasMany(CustomEvent::class)->orderBy('event_date');
    }

    public function promanProjects(): HasMany
    {
        return $this->hasMany(PromanProject::class)->orderBy('name');
    }
}
