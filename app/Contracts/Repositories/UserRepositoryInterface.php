<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function create(string $username): User;

    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByIds(array $ids): Collection;
}
