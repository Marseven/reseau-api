<?php

namespace Database\Seeders;

use App\Models\Batiment;
use App\Models\Salle;
use Illuminate\Database\Seeder;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        $batPrinc = Batiment::where('code', 'BAT-MOA-PRINC')->first();
        $batTech = Batiment::where('code', 'BAT-MOA-TECH')->first();
        $batAdmin = Batiment::where('code', 'BAT-FCV-ADMIN')->first();
        $batSiege = Batiment::where('code', 'BAT-LBV-SIEGE')->first();

        $salles = [
            ['code' => 'SAL-MOA-SS01', 'name' => 'Salle Serveur 01', 'batiment_id' => $batPrinc?->id, 'floor' => 'RDC', 'type' => 'salle_serveur', 'status' => 'active', 'description' => 'Salle serveur principale'],
            ['code' => 'SAL-MOA-BUR01', 'name' => 'Bureau 101', 'batiment_id' => $batPrinc?->id, 'floor' => '1er', 'type' => 'bureau', 'status' => 'active', 'description' => 'Bureau premier étage'],
            ['code' => 'SAL-MOA-LT01', 'name' => 'Local Technique 01', 'batiment_id' => $batTech?->id, 'floor' => 'RDC', 'type' => 'technique', 'status' => 'active', 'description' => 'Local technique principal'],
            ['code' => 'SAL-FCV-BUR01', 'name' => 'Bureau Direction', 'batiment_id' => $batAdmin?->id, 'floor' => '1er', 'type' => 'bureau', 'status' => 'active', 'description' => 'Bureau de la direction'],
            ['code' => 'SAL-FCV-SS01', 'name' => 'Salle Serveur', 'batiment_id' => $batAdmin?->id, 'floor' => 'RDC', 'type' => 'salle_serveur', 'status' => 'active', 'description' => 'Salle serveur Franceville'],
            ['code' => 'SAL-LBV-SS01', 'name' => 'Datacenter', 'batiment_id' => $batSiege?->id, 'floor' => '2ème', 'type' => 'salle_serveur', 'status' => 'active', 'description' => 'Datacenter du siège'],
            ['code' => 'SAL-LBV-STOCK', 'name' => 'Stockage IT', 'batiment_id' => $batSiege?->id, 'floor' => 'RDC', 'type' => 'stockage', 'status' => 'active', 'description' => 'Stockage matériel IT'],
        ];

        foreach ($salles as $salle) {
            if ($salle['batiment_id']) {
                Salle::updateOrCreate(['code' => $salle['code']], $salle);
            }
        }
    }
}
