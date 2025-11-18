<?php

namespace App\DTOs\Leaderboard;

use App\DTOs\BaseDTO;

class TransactionHistory extends BaseDTO
{
    /**
     * @param TransactionDTO[] $transactions
     */
    public function __construct(
        public readonly int $userId,
        public readonly array $transactions
    ) {}

    public static function create(int $userId, array $transactions): self
    {
        $dtos = array_map(
            fn($transaction) => $transaction instanceof TransactionDTO
                ? $transaction
                : TransactionDTO::fromModel($transaction),
            $transactions
        );

        return new self(
            userId: $userId,
            transactions: $dtos
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'transactions' => array_map(fn($tx) => $tx->toArray(), $this->transactions),
        ];
    }
}
