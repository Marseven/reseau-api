<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'equipement_code' => 'required|string|max:255|unique:equipements,equipement_code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'classification' => 'sometimes|in:IT,OT',
            'serial_number' => 'nullable|string|max:255',
            'fabricant' => 'nullable|string|max:255',
            'modele' => 'nullable|string|max:255',
            'connection_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'direction_in_out' => 'nullable|string',
            'vlan' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'coffret_id' => 'required|exists:coffrets,id',
            'status' => 'required|in:active,inactive,maintenance',
        ];
    }
}
