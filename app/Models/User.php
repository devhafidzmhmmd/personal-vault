<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'master_password_hash',
        'master_password_salt',
        'proman_user_id',
        'proman_token',
        'saved_locations',
        'proman_username',
        'proman_password',
        'proman_api_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'master_password_hash',
        'master_password_salt',
        'proman_password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'saved_locations' => 'array',
        ];
    }

    public function hasAbsenSettingsConfigured(): bool
    {
        return ! empty($this->proman_user_id) && ! empty($this->proman_token);
    }

    public function hasPromanCredentials(): bool
    {
        return ! empty($this->proman_username) && ! empty($this->getPromanPassword()) && ! empty($this->proman_api_key);
    }

    public function getPromanPassword(): ?string
    {
        if (empty($this->attributes['proman_password'] ?? null)) {
            return null;
        }

        return decrypt($this->attributes['proman_password']);
    }

    public function setPromanPasswordAttribute(?string $value): void
    {
        $this->attributes['proman_password'] = $value ? encrypt($value) : null;
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    public function shortcuts(): HasMany
    {
        return $this->hasMany(Shortcut::class)->orderBy('order');
    }

    public function hasMasterPassword(): bool
    {
        return ! empty($this->master_password_hash);
    }
}
