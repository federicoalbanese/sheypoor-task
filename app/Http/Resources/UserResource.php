<?php

namespace App\Http\Resources;

use App\DTOs\User\UserData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function __construct(
        private readonly UserData $userData
    ) {
        parent::__construct($userData);
    }

    public function toArray(Request $request): array
    {
        return $this->userData->toArray();
    }
}
