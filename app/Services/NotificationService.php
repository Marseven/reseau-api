<?php

namespace App\Services;

use App\Models\ChangeRequest;
use App\Models\Maintenance;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notifyNewChangeRequest(ChangeRequest $changeRequest): void
    {
        $admins = User::where('role', 'administrator')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'modification_request',
                'title' => "Nouvelle demande {$changeRequest->code}",
                'message' => "Une nouvelle demande de modification a été soumise pour la baie {$changeRequest->coffret->name}.",
                'data' => [
                    'change_request_id' => $changeRequest->id,
                    'change_request_code' => $changeRequest->code,
                    'coffret_id' => $changeRequest->coffret_id,
                ],
            ]);
        }
    }

    public function notifyChangeRequestReviewed(ChangeRequest $changeRequest): void
    {
        $typeMap = [
            'approuvee' => 'modification_approved',
            'rejetee' => 'modification_rejected',
            'en_revision' => 'modification_request',
        ];

        $statusLabels = [
            'approuvee' => 'approuvée',
            'rejetee' => 'rejetée',
            'en_revision' => 'mise en révision',
        ];

        $type = $typeMap[$changeRequest->status] ?? 'modification_request';
        $label = $statusLabels[$changeRequest->status] ?? $changeRequest->status;

        Notification::create([
            'user_id' => $changeRequest->requester_id,
            'type' => $type,
            'title' => "Demande {$changeRequest->code} {$label}",
            'message' => "Votre demande de modification {$changeRequest->code} a été {$label}.",
            'data' => [
                'change_request_id' => $changeRequest->id,
                'change_request_code' => $changeRequest->code,
                'status' => $changeRequest->status,
            ],
        ]);
    }

    public function notifyMaintenanceActive(Maintenance $maintenance): void
    {
        if (!$maintenance->technicien_id) {
            return;
        }

        Notification::create([
            'user_id' => $maintenance->technicien_id,
            'type' => 'intervention_active',
            'title' => "Intervention {$maintenance->code} activée",
            'message' => "L'intervention \"{$maintenance->title}\" est maintenant en cours.",
            'data' => [
                'maintenance_id' => $maintenance->id,
                'maintenance_code' => $maintenance->code,
            ],
        ]);
    }
}
