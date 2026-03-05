<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'equipement_code' => 'sometimes|string|max:255|unique:equipements,equipement_code,' . $this->route('equipement')?->id,
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'direction_in_out' => 'nullable|string',
            'vlan' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'coffret_id' => 'sometimes|exists:coffrets,id',
            'status' => 'sometimes|in:active,inactive,maintenance',
        ];
    }
}
