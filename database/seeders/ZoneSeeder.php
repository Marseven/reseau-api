<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        $siteMoanda = Site::where('code', 'SITE-MOA')->first();
        $siteFcv = Site::where('code', 'SITE-FCV')->first();
        $siteLbv = Site::where('code', 'SITE-LBV')->first();

        $zones = [
            // Moanda
            ['code' => 'ZONE-MOA-A', 'name' => 'Bâtiment A', 'floor' => 'RDC', 'building' => 'Bâtiment Principal', 'site_id' => $siteMoanda?->id, 'status' => 'active', 'description' => 'Bâtiment principal - Rez-de-chaussée'],
            ['code' => 'ZONE-MOA-LT', 'name' => 'Local Technique', 'floor' => 'Sous-sol', 'building' => 'Bâtiment Principal', 'site_id' => $siteMoanda?->id, 'status' => 'active', 'description' => 'Local technique principal'],
            ['code' => 'ZONE-MOA-SS', 'name' => 'Salle Serveur', 'floor' => 'RDC', 'building' => 'Annexe', 'site_id' => $siteMoanda?->id, 'status' => 'active', 'description' => 'Salle serveur climatisée'],
            // Franceville
            ['code' => 'ZONE-FCV-A', 'name' => 'Bâtiment A', 'floor' => '1er', 'building' => 'Bureau Administratif', 'site_id' => $siteFcv?->id, 'status' => 'active', 'description' => 'Bureau administratif 1er étage'],
            ['code' => 'ZONE-FCV-LT', 'name' => 'Local Technique', 'floor' => 'RDC', 'building' => 'Bureau Administratif', 'site_id' => $siteFcv?->id, 'status' => 'active', 'description' => 'Local technique Franceville'],
            // Libreville
            ['code' => 'ZONE-LBV-SS', 'name' => 'Salle Serveur', 'floor' => '2ème', 'building' => 'Siège Social', 'site_id' => $siteLbv?->id, 'status' => 'active', 'description' => 'Salle serveur du siège'],
            ['code' => 'ZONE-LBV-LT', 'name' => 'Local Technique', 'floor' => 'RDC', 'building' => 'Siège Social', 'site_id' => $siteLbv?->id, 'status' => 'active', 'description' => 'Local technique du siège'],
        ];

        foreach ($zones as $zone) {
            if ($zone['site_id']) {
                Zone::updateOrCreate(['code' => $zone['code']], $zone);
            }
        }
    }
}
