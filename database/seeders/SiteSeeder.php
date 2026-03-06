<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'code' => 'SITE-MOA',
                'name' => 'Site Moanda',
                'address' => 'Zone Industrielle Moanda',
                'city' => 'Moanda',
                'country' => 'Gabon',
                'longitude' => 13.1979,
                'latitude' => -1.5659,
                'status' => 'active',
                'description' => 'Site principal de production - Moanda',
            ],
            [
                'code' => 'SITE-FCV',
                'name' => 'Site Franceville',
                'address' => 'Boulevard Léon Mba',
                'city' => 'Franceville',
                'country' => 'Gabon',
                'longitude' => 13.5833,
                'latitude' => -1.6333,
                'status' => 'active',
                'description' => 'Site administratif - Franceville',
            ],
            [
                'code' => 'SITE-LBV',
                'name' => 'Site Libreville',
                'address' => 'Quartier des Affaires',
                'city' => 'Libreville',
                'country' => 'Gabon',
                'longitude' => 9.4536,
                'latitude' => 0.3924,
                'status' => 'active',
                'description' => 'Siège social - Libreville',
            ],
        ];

        foreach ($sites as $site) {
            Site::updateOrCreate(['code' => $site['code']], $site);
        }
    }
}
