<?php

namespace App\Contracts\Models;

interface UserInterface
{
    /**
     * Get the user's ID
     */
    public function getId(): int;

    /**
     * Get the username
     */
    public function getUsername(): string;

    /**
     * Check if this is a null user (non-existent user)
     */
    public function isNull(): bool;

    /**
     * Check if this is a real user
     */
    public function exists(): bool;
}
