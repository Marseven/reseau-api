<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoffretRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'piece' => 'sometimes|string',
            'type' => 'nullable|string|max:255',
            'long' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'zone_id' => 'nullable|exists:zones,id',
        ];
    }
}
