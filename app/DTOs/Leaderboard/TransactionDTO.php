<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;
use App\Models\PointTransaction;

class TransactionDTO extends BaseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly float $points,
        public readonly string $source,
        public readonly array $metadata,
        public readonly string $createdAt
    ) {}

    public static function fromModel(PointTransaction $transaction): self
    {
        return new self(
            id: $transaction->id,
            points: (float) $transaction->points,
            source: $transaction->source,
            metadata: $transaction->metadata ?? [],
            createdAt: $transaction->created_at->toIso8601String()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'source' => $this->source,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt,
        ];
    }
}
