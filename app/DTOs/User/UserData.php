<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use App\Models\User;

class UserData extends BaseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $createdAt
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            createdAt: $user->created_at->toIso8601String()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'created_at' => $this->createdAt,
        ];
    }
}
