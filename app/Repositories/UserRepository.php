<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function create(string $username): User
    {
        return User::create(['username' => $username]);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function findByIds(array $ids): Collection
    {
        return User::whereIn('id', $ids)->get()->keyBy('id');
    }
}
