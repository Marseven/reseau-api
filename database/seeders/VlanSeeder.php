<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\Vlan;
use Illuminate\Database\Seeder;

class VlanSeeder extends Seeder
{
    public function run(): void
    {
        $siteMoanda = Site::where('code', 'SITE-MOA')->first();
        $siteFcv = Site::where('code', 'SITE-FCV')->first();
        $siteLbv = Site::where('code', 'SITE-LBV')->first();

        $vlans = [
            ['vlan_id' => 10, 'name' => 'VLAN-MGMT', 'description' => 'VLAN de management réseau', 'site_id' => $siteMoanda?->id, 'network' => '10.0.10.0/24', 'gateway' => '10.0.10.1', 'status' => 'active'],
            ['vlan_id' => 20, 'name' => 'VLAN-DATA', 'description' => 'VLAN données utilisateurs', 'site_id' => $siteMoanda?->id, 'network' => '10.0.20.0/24', 'gateway' => '10.0.20.1', 'status' => 'active'],
            ['vlan_id' => 30, 'name' => 'VLAN-VOIP', 'description' => 'VLAN téléphonie IP', 'site_id' => $siteMoanda?->id, 'network' => '10.0.30.0/24', 'gateway' => '10.0.30.1', 'status' => 'active'],
            ['vlan_id' => 100, 'name' => 'VLAN-SERVER', 'description' => 'VLAN serveurs', 'site_id' => $siteLbv?->id, 'network' => '10.1.100.0/24', 'gateway' => '10.1.100.1', 'status' => 'active'],
            ['vlan_id' => 200, 'name' => 'VLAN-GUEST', 'description' => 'VLAN invités', 'site_id' => $siteFcv?->id, 'network' => '10.2.200.0/24', 'gateway' => '10.2.200.1', 'status' => 'active'],
        ];

        foreach ($vlans as $vlan) {
            Vlan::updateOrCreate(['vlan_id' => $vlan['vlan_id']], $vlan);
        }
    }
}
