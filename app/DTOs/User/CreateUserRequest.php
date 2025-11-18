<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;

class CreateUserRequest extends BaseDTO
{
    public function __construct(
        public readonly string $username
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username']
        );
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
        ];
    }
}
