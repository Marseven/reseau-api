<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur', 'technicien');
    }

    public function rules(): array
    {
        return [
            'coffret_id' => [
                'required',
                'exists:coffrets,id',
                Rule::unique('change_requests')->where(function ($query) {
                    return $query->where('coffret_id', $this->coffret_id)
                        ->where('status', 'en_attente');
                }),
            ],
            'type' => 'required|in:ajout_port,modification_connexion,suppression_port,changement_statut,ajout_equipement,suppression_equipement',
            'description' => 'required|string|min:10',
            'justification' => 'required|string|min:10',
            'intervention_date' => 'required|date|after_or_equal:today',
            'photo_before' => 'nullable|image|max:5120',
            'photo_after' => 'nullable|image|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'coffret_id.unique' => 'Une demande de modification est déjà en attente pour cette baie.',
        ];
    }
}
