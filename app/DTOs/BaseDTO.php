<?php

namespace App\DTOs;

use JsonSerializable;

abstract class BaseDTO implements JsonSerializable
{
    /**
     * Convert DTO to array
     */
    abstract public function toArray(): array;

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert DTO to JSON string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
