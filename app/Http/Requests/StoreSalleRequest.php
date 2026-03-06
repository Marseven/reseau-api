<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:salles,code',
            'name' => 'required|string|max:255',
            'batiment_id' => 'required|exists:batiments,id',
            'floor' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:salle_serveur,bureau,technique,stockage',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'description' => 'nullable|string',
        ];
    }
}
