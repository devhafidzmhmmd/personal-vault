<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Password extends Model
{
    use HasFactory;

    public const TYPE_APP = 'app';

    public const TYPE_DB = 'db';

    public const TYPE_SERVER = 'server';

    public const TYPE_OTHER = 'other';

    public static function types(): array
    {
        return [
            self::TYPE_APP => 'Aplikasi',
            self::TYPE_DB => 'Database',
            self::TYPE_SERVER => 'Server',
            self::TYPE_OTHER => 'Lainnya',
        ];
    }

    protected $fillable = [
        'workspace_id',
        'prefix_id',
        'type',
        'name',
        'username',
        'password_encrypted',
        'url',
        'notes',
    ];

    protected $hidden = ['password_encrypted'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function prefix(): BelongsTo
    {
        return $this->belongsTo(PasswordPrefix::class, 'prefix_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->prefix_id && $this->relationLoaded('prefix') && $this->prefix
            ? '['.$this->prefix->name.'] '.$this->name
            : $this->name;
    }
}
