<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:255|unique:salles,code,' . $this->route('salle')?->id,
            'name' => 'sometimes|string|max:255',
            'batiment_id' => 'sometimes|exists:batiments,id',
            'floor' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:salle_serveur,bureau,technique,stockage',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'description' => 'nullable|string',
        ];
    }
}
