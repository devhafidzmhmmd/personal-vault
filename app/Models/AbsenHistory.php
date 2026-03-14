<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenHistory extends Model
{
    public const MAX_HITS_PER_DAY = 2;

    protected $fillable = [
        'user_id',
        'tgl_absen',
        'hit_count',
    ];

    protected function casts(): array
    {
        return [
            'tgl_absen' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
