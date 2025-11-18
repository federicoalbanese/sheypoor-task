<?php

namespace App\Models;

use App\Contracts\Models\UserInterface;

/**
 * Null Object Pattern implementation for User
 *
 * Represents a non-existent user without using null checks
 */
class NullUser implements UserInterface
{
    public function getId(): int
    {
        return 0;
    }

    public function getUsername(): string
    {
        return 'Unknown';
    }

    public function isNull(): bool
    {
        return true;
    }

    public function exists(): bool
    {
        return false;
    }

    /**
     * Static factory method
     */
    public static function create(): self
    {
        return new self();
    }
}
