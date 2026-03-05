<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLiaisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
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
        ];
    }
}
