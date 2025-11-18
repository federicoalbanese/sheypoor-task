<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    protected $table = 'leaderboard_point_transactions';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'points',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'points' => 'decimal:2',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
