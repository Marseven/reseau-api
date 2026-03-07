<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approuvee,rejetee,en_revision',
            'review_comment' => 'required_if:status,rejetee,en_revision|nullable|string|min:5',
        ];
    }
}
