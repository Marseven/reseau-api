<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer',
            'format' => 'required|in:small,medium,large',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Au moins un ID est requis.',
            'ids.max' => 'Maximum 100 étiquettes par génération.',
            'format.required' => 'Le format est requis.',
            'format.in' => 'Le format doit être small, medium ou large.',
        ];
    }
}
