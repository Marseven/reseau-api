<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'port_label' => 'sometimes|string|max:255',
            'device_name' => 'sometimes|string|max:255',
            'poe_enabled' => 'sometimes|boolean',
            'vlan' => 'nullable|string|max:255',
            'speed' => 'nullable|string|max:255',
            'connected_equipment_id' => 'nullable|exists:equipements,id',
        ];
    }
}
