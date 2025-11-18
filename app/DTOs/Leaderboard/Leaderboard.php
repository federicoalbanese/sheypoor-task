<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class Leaderboard extends BaseDTO
{
    /**
     * @param LeaderboardEntryDTO[] $leaderboard
     */
    public function __construct(
        public readonly array $leaderboard,
        public readonly string $updatedAt
    ) {}

    public static function create(array $entries, string $updatedAt): self
    {
        $dtos = array_map(
            fn($entry) => $entry instanceof LeaderboardEntryDTO
                ? $entry
                : LeaderboardEntryDTO::fromArray($entry),
            $entries
        );

        return new self(
            leaderboard: $dtos,
            updatedAt: $updatedAt
        );
    }

    public function toArray(): array
    {
        return [
            'leaderboard' => array_map(fn($entry) => $entry->toArray(), $this->leaderboard),
            'updated_at' => $this->updatedAt,
        ];
    }
}
