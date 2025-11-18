<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardScore extends Model
{
    protected $table = 'leaderboard_scores';

    protected $fillable = [
        'user_id',
        'score',
        'last_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'score' => 'decimal:2',
            'last_transaction_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
