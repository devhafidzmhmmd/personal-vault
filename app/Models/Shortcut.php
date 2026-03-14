<?php

namespace App\Models;

use App\Services\FaviconService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Shortcut extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'workspace_id', 'title', 'url', 'icon', 'favicon_path', 'order'];

    protected static function booted(): void
    {
        static::deleting(function (Shortcut $shortcut): void {
            if ($shortcut->favicon_path) {
                app(FaviconService::class)->delete($shortcut->favicon_path);
            }
        });
    }

    protected $casts = [
        'order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function passwordPrefixes(): HasMany
    {
        return $this->hasMany(PasswordPrefix::class);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        if (! $this->favicon_path) {
            return null;
        }

        return Storage::disk('public')->url($this->favicon_path);
    }
}
