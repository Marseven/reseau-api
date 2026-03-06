<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:255|unique:batiments,code,' . $this->route('batiment')?->id,
            'name' => 'sometimes|string|max:255',
            'zone_id' => 'sometimes|exists:zones,id',
            'address' => 'nullable|string|max:255',
            'floors_count' => 'nullable|integer|min:0',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'description' => 'nullable|string',
        ];
    }
}
