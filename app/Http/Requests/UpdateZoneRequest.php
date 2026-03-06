<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:255|unique:zones,code,' . $this->route('zone')?->id,
            'name' => 'sometimes|string|max:255',
            'floor' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'site_id' => 'sometimes|exists:sites,id',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'description' => 'nullable|string',
        ];
    }
}
