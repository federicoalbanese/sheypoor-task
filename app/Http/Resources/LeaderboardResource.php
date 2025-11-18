<?php

namespace App\Http\Resources;

use App\DTOs\Leaderboard\Leaderboard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    public function __construct(
        private readonly Leaderboard $leaderboard
    ) {
        parent::__construct($leaderboard);
    }

    public function toArray(Request $request): array
    {
        return $this->leaderboard->toArray();
    }
}
