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
            'piece' => 'sometimes|nullable|string',
            'type' => 'nullable|string|max:255',
            'long' => 'sometimes|nullable|numeric',
            'lat' => 'sometimes|nullable|numeric',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'zone_id' => 'nullable|exists:zones,id',
            'salle_id' => 'nullable|exists:salles,id',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }
}
