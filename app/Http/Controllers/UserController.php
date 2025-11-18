<?php

namespace App\Http\Controllers;

use App\DTOs\User\CreateUserRequest as CreateUserDTO;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function store(CreateUserRequest $request): JsonResource
    {
        $createUserDTO = CreateUserDTO::fromArray($request->validated());
        $userData = $this->userService->createUser($createUserDTO);

        return (new UserResource($userData))
            ->response()
            ->setStatusCode(201);
    }
}
