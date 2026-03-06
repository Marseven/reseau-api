<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:zones,code',
            'name' => 'required|string|max:255',
            'floor' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'site_id' => 'required|exists:sites,id',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'description' => 'nullable|string',
        ];
    }
}
