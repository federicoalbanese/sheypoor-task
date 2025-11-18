<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class UserPositionDTO extends BaseDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $username,
        public readonly ?int $rank,
        public readonly float $score
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            username: $data['username'] ?? 'Unknown',
            rank: $data['rank'] ?? null,
            score: (float) $data['score']
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'username' => $this->username,
            'rank' => $this->rank,
            'score' => $this->score,
        ];
    }
}
