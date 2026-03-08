<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SiteSeeder::class,
            ZoneSeeder::class,
            BatimentSeeder::class,
            SalleSeeder::class,
            CoffretSeeder::class,
            VlanSeeder::class,
            MaintenanceSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
