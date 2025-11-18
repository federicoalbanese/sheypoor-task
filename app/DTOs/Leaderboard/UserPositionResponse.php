<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class UserPositionResponse extends BaseDTO
{
    /**
     * @param LeaderboardEntryDTO[] $leaderboard
     */
    public function __construct(
        public readonly array $leaderboard,
        public readonly UserPositionDTO $user,
        public readonly string $updatedAt
    ) {}

    public static function create(array $leaderboard, UserPositionDTO $user, string $updatedAt): self
    {
        $dtos = array_map(
            fn($entry) => $entry instanceof LeaderboardEntryDTO
                ? $entry
                : LeaderboardEntryDTO::fromArray($entry),
            $leaderboard
        );

        return new self(
            leaderboard: $dtos,
            user: $user,
            updatedAt: $updatedAt
        );
    }

    public function toArray(): array
    {
        return [
            'leaderboard' => array_map(fn($entry) => $entry->toArray(), $this->leaderboard),
            'user' => $this->user->toArray(),
            'updated_at' => $this->updatedAt,
        ];
    }
}
