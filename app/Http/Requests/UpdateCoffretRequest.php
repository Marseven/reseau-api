<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoffretRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'piece' => 'sometimes|string',
            'long' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'status' => 'sometimes|in:active,inactive,maintenance',
        ];
    }
}
