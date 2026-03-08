<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoffretRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'piece' => 'required|string',
            'type' => 'nullable|string|max:255',
            'long' => 'nullable|numeric',
            'lat' => 'nullable|numeric',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'zone_id' => 'nullable|exists:zones,id',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }
}
