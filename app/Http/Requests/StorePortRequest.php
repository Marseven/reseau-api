<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'port_label' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
            'poe_enabled' => 'required|boolean',
            'vlan' => 'nullable|string|max:255',
            'speed' => 'nullable|string|max:255',
            'connected_equipment_id' => 'nullable|exists:equipements,id',
        ];
    }
}
