<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMetricRequest extends FormRequest
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
            'last_value' => 'nullable|string|max:255',
            'coffret_id' => 'required|exists:coffrets,id',
            'status' => 'required|in:active,inactive,maintenance',
        ];
    }
}
