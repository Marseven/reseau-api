<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vendor' => 'nullable|string',
            'endpoint' => 'nullable|string',
            'monitored_scope' => 'nullable|string',
            'status' => 'required|boolean',
        ];
    }
}
