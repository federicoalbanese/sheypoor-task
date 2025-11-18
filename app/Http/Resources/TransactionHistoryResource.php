<?php

namespace App\Http\Resources;

use App\DTOs\Leaderboard\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionHistoryResource extends JsonResource
{
    public function __construct(
        private readonly TransactionHistory $history
    ) {
        parent::__construct($history);
    }

    public function toArray(Request $request): array
    {
        return $this->history->toArray();
    }
}
