<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'last_value' => 'nullable|string|max:255',
            'coffret_id' => 'sometimes|exists:coffrets,id',
            'status' => 'sometimes|boolean',
        ];
    }
}
