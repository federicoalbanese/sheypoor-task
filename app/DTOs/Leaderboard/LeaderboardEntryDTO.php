<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class LeaderboardEntryDTO extends BaseDTO
{
    public function __construct(
        public readonly int $rank,
        public readonly int $userId,
        public readonly string $username,
        public readonly float $score
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            rank: $data['rank'],
            userId: $data['user_id'],
            username: $data['username'] ?? 'Unknown',
            score: (float) $data['score']
        );
    }

    public function toArray(): array
    {
        return [
            'rank' => $this->rank,
            'user_id' => $this->userId,
            'username' => $this->username,
            'score' => $this->score,
        ];
    }
}
