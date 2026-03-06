<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:administrator,directeur,technicien,user',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'sometimes|boolean',
            'site_id' => 'nullable|exists:sites,id',
        ];
    }
}
