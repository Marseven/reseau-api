<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoffretRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdministrator();
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'piece' => 'required|string',
            'long' => 'required|numeric',
            'lat' => 'required|numeric',
            'status' => 'sometimes|in:active,inactive,maintenance',
        ];
    }
}
