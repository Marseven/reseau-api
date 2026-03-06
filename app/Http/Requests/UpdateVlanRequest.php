<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'vlan_id' => 'sometimes|integer|min:1|max:4094|unique:vlans,vlan_id,' . $this->route('vlan')?->id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'network' => 'nullable|string|max:255',
            'gateway' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ];
    }
}
