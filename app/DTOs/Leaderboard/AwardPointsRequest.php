<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class AwardPointsRequest extends BaseDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly float $points,
        public readonly string $source,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            points: (float) $data['points'],
            source: $data['source'],
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'points' => $this->points,
            'source' => $this->source,
            'metadata' => $this->metadata,
        ];
    }
}
