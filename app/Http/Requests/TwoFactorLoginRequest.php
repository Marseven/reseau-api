<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'two_factor_token' => 'required|string',
            'code' => 'required_without:recovery_code|string|size:6',
            'recovery_code' => 'required_without:code|string',
        ];
    }
}
