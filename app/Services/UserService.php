<?php

namespace App\Services;

use App\Contracts\Models\UserInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\User\CreateUserRequest;
use App\DTOs\User\UserData;
use App\Models\NullUser;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function createUser(CreateUserRequest $request): UserData
    {
        $user = $this->userRepository->create($request->username);

        return UserData::fromModel($user);
    }

    public function getUserById(int $id): UserInterface
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return NullUser::create();
        }

        return $user;
    }

    public function getUserDataById(int $id): ?UserData
    {
        $user = $this->getUserById($id);

        if ($user->isNull()) {
            return null;
        }

        return UserData::fromModel($user);
    }
}
