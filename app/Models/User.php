<?php

namespace App\Models;

use App\Contracts\Models\UserInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model implements UserInterface
{
    use HasFactory;

    protected $fillable = [
        'username',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function leaderboardScore(): HasOne
    {
        return $this->hasOne(LeaderboardScore::class);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function isNull(): bool
    {
        return false;
    }

    public function exists(): bool
    {
        return true;
    }
}
