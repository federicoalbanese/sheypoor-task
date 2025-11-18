<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
}
