<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'sometimes|string|max:255',
            'surname' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $userId,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:50',
            'role' => 'sometimes|in:administrator,directeur,technicien,user,prestataire',
            'password' => 'sometimes|string|min:8|confirmed',
            'is_active' => 'sometimes|boolean',
            'site_id' => 'nullable|exists:sites,id',
        ];
    }
}
