<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class AwardPointsResult extends BaseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly int $transactionId,
        public readonly float $newScore,
        public readonly float $pointsAwarded,
        public readonly string $source,
        public readonly ?int $rank
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            transactionId: $data['transaction_id'],
            newScore: (float) $data['new_score'],
            pointsAwarded: (float) $data['points_awarded'],
            source: $data['source'],
            rank: $data['rank'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'new_score' => $this->newScore,
            'points_awarded' => $this->pointsAwarded,
            'source' => $this->source,
            'rank' => $this->rank,
        ];
    }
}
