<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator', 'directeur');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:maintenances,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:preventive,corrective,urgente,evolutive',
            'priority' => 'required|in:basse,moyenne,haute,critique',
            'status' => 'sometimes|in:planifiee,en_cours,terminee,annulee',
            'equipement_id' => 'nullable|exists:equipements,id',
            'coffret_id' => 'nullable|exists:coffrets,id',
            'site_id' => 'nullable|exists:sites,id',
            'technicien_id' => 'required|exists:users,id',
            'validator_id' => 'nullable|exists:users,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
