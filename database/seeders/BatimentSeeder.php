<?php

namespace Database\Seeders;

use App\Models\Zone;
use App\Models\Batiment;
use Illuminate\Database\Seeder;

class BatimentSeeder extends Seeder
{
    public function run(): void
    {
        $zoneMoaA = Zone::where('code', 'ZONE-MOA-A')->first();
        $zoneMoaLt = Zone::where('code', 'ZONE-MOA-LT')->first();
        $zoneMoaSs = Zone::where('code', 'ZONE-MOA-SS')->first();
        $zoneFcvA = Zone::where('code', 'ZONE-FCV-A')->first();
        $zoneLbvSs = Zone::where('code', 'ZONE-LBV-SS')->first();

        $batiments = [
            ['code' => 'BAT-MOA-PRINC', 'name' => 'Bâtiment Principal', 'zone_id' => $zoneMoaA?->id, 'address' => 'Avenue Principale, Moanda', 'floors_count' => 3, 'status' => 'active', 'description' => 'Bâtiment principal du site de Moanda'],
            ['code' => 'BAT-MOA-TECH', 'name' => 'Bâtiment Technique', 'zone_id' => $zoneMoaLt?->id, 'address' => 'Zone Technique, Moanda', 'floors_count' => 1, 'status' => 'active', 'description' => 'Local technique et salle serveur'],
            ['code' => 'BAT-MOA-ANNEX', 'name' => 'Annexe', 'zone_id' => $zoneMoaSs?->id, 'address' => 'Zone Annexe, Moanda', 'floors_count' => 2, 'status' => 'active', 'description' => 'Bâtiment annexe'],
            ['code' => 'BAT-FCV-ADMIN', 'name' => 'Bureau Administratif', 'zone_id' => $zoneFcvA?->id, 'address' => 'Rue Centrale, Franceville', 'floors_count' => 2, 'status' => 'active', 'description' => 'Bureau administratif de Franceville'],
            ['code' => 'BAT-LBV-SIEGE', 'name' => 'Siège Social', 'zone_id' => $zoneLbvSs?->id, 'address' => 'Boulevard du Bord de Mer, Libreville', 'floors_count' => 5, 'status' => 'active', 'description' => 'Siège social à Libreville'],
        ];

        foreach ($batiments as $batiment) {
            if ($batiment['zone_id']) {
                Batiment::updateOrCreate(['code' => $batiment['code']], $batiment);
            }
        }
    }
}
