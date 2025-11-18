<?php

namespace App\Http\Resources;

use App\DTOs\Leaderboard\AwardPointsResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AwardPointsResource extends JsonResource
{
    public function __construct(
        private readonly AwardPointsResult $result
    ) {
        parent::__construct($result);
    }

    public function toArray(Request $request): array
    {
        return $this->result->toArray();
    }
}
