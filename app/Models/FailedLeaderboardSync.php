<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedLeaderboardSync extends Model
{
    protected $table = 'failed_leaderboard_syncs';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'redis_score',
        'error',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'transaction_id' => 'integer',
            'redis_score' => 'decimal:2',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
