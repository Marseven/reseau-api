<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLiaisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'from' => 'sometimes|exists:equipements,id',
            'to' => 'sometimes|exists:equipements,id',
            'label' => 'sometimes|string|max:255',
            'media' => 'sometimes|string|max:255',
            'length' => 'nullable|integer',
            'status' => 'sometimes|boolean',
        ];
    }
}
