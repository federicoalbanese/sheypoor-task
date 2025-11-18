<?php

namespace App\Http\Requests;

use App\DTOs\User\CreateUserRequest as CreateUserDTO;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9_]+$/',
            ],
        ];
    }

    public function getDTO(): CreateUserDTO
    {
        return CreateUserDTO::fromArray($this->validated());
    }
}
