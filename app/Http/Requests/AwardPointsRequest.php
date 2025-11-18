<?php

namespace App\Http\Requests;

use App\DTOs\Leaderboard\AwardPointsRequest as AwardPointsDTO;
use Illuminate\Foundation\Http\FormRequest;

class AwardPointsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'points' => [
                'required',
                'numeric',
                'min:0',
            ],
            'source' => [
                'required',
                'string',
                'max:100',
            ],
            'metadata' => [
                'sometimes',
                'array',
            ],
        ];
    }

    public function getDTO(): AwardPointsDTO
    {
        return  AwardPointsDTO::fromArray($this->validated());
    }
}
