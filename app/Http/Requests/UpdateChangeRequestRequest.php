<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cr = $this->route('changeRequest');
        $user = $this->user();

        // Only requester or admin can edit
        if ($cr->requester_id !== $user->id && !$user->hasRole('administrator')) {
            return false;
        }

        // Only editable if en_attente or en_revision
        return in_array($cr->status, ['en_attente', 'en_revision']);
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|in:ajout_port,modification_connexion,suppression_port,changement_statut,ajout_equipement,suppression_equipement',
            'description' => 'sometimes|string|min:10',
            'justification' => 'sometimes|string|min:10',
            'intervention_date' => 'sometimes|date',
            'photo_before' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'photo_after' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }
}
