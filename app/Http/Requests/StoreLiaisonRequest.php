<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLiaisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'from' => 'required|exists:equipements,id',
            'to' => 'required|exists:equipements,id',
            'label' => 'required|string|max:255',
            'media' => 'required|string|max:255',
            'length' => 'nullable|integer',
            'status' => 'required|boolean',
            'from_port_id' => 'nullable|exists:ports,id',
            'to_port_id' => 'nullable|exists:ports,id',
            'status_label' => 'sometimes|in:active,inactive,maintenance',
        ];
    }
}
