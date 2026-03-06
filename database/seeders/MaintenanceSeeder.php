<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $technicien = User::where('role', 'technicien')->first();
        $admin = User::where('role', 'administrator')->first();
        $siteMoanda = Site::where('code', 'SITE-MOA')->first();
        $siteLbv = Site::where('code', 'SITE-LBV')->first();

        if (!$technicien || !$admin) {
            return;
        }

        $maintenances = [
            [
                'code' => 'MAINT-001',
                'title' => 'Vérification câblage RDC',
                'description' => 'Inspection visuelle et test de continuité du câblage réseau au rez-de-chaussée',
                'type' => 'preventive',
                'priority' => 'moyenne',
                'status' => 'planifiee',
                'technicien_id' => $technicien->id,
                'site_id' => $siteMoanda?->id,
                'scheduled_date' => '2026-03-15',
                'scheduled_time' => '09:00',
            ],
            [
                'code' => 'MAINT-002',
                'title' => 'Mise à jour firmware switches',
                'description' => 'Mise à jour du firmware sur tous les switches du datacenter',
                'type' => 'evolutive',
                'priority' => 'haute',
                'status' => 'planifiee',
                'technicien_id' => $technicien->id,
                'validator_id' => $admin->id,
                'site_id' => $siteLbv?->id,
                'scheduled_date' => '2026-03-20',
                'scheduled_time' => '22:00',
            ],
            [
                'code' => 'MAINT-003',
                'title' => 'Remplacement switch défectueux',
                'description' => 'Remplacement du switch SW-MOA-03 suite à panne',
                'type' => 'corrective',
                'priority' => 'critique',
                'status' => 'en_cours',
                'technicien_id' => $technicien->id,
                'site_id' => $siteMoanda?->id,
                'scheduled_date' => '2026-03-06',
                'started_at' => '2026-03-06 08:30:00',
            ],
        ];

        foreach ($maintenances as $maintenance) {
            Maintenance::updateOrCreate(['code' => $maintenance['code']], $maintenance);
        }
    }
}
