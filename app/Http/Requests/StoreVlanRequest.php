<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'vlan_id' => 'required|integer|min:1|max:4094|unique:vlans,vlan_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'network' => 'nullable|string|max:255',
            'gateway' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ];
    }
}
