<?php

namespace Database\Seeders;

use App\Models\Zone;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Port;
use Illuminate\Database\Seeder;

class CoffretSeeder extends Seeder
{
    public function run(): void
    {
        $zoneMoaSS = Zone::where('code', 'ZONE-MOA-SS')->first();
        $zoneMoaLT = Zone::where('code', 'ZONE-MOA-LT')->first();
        $zoneFcvLT = Zone::where('code', 'ZONE-FCV-LT')->first();
        $zoneLbvSS = Zone::where('code', 'ZONE-LBV-SS')->first();

        $coffrets = [
            [
                'code' => 'CAB-001', 'name' => 'Baie Serveur Principale', 'piece' => 'R-201',
                'type' => '42U', 'long' => 13.1980, 'lat' => -1.5660, 'status' => 'active',
                'zone_id' => $zoneMoaSS?->id,
            ],
            [
                'code' => 'CAB-002', 'name' => 'Baie Réseau Moanda', 'piece' => 'R-102',
                'type' => '24U', 'long' => 13.1975, 'lat' => -1.5655, 'status' => 'active',
                'zone_id' => $zoneMoaLT?->id,
            ],
            [
                'code' => 'CAB-003', 'name' => 'Baie Franceville', 'piece' => 'LT-01',
                'type' => '36U', 'long' => 13.5835, 'lat' => -1.6335, 'status' => 'active',
                'zone_id' => $zoneFcvLT?->id,
            ],
            [
                'code' => 'CAB-004', 'name' => 'Baie Siège Libreville', 'piece' => 'SS-201',
                'type' => '42U', 'long' => 9.4540, 'lat' => 0.3920, 'status' => 'maintenance',
                'zone_id' => $zoneLbvSS?->id,
            ],
            [
                'code' => 'CAB-005', 'name' => 'Baie Distribution Moanda', 'piece' => 'R-105',
                'type' => '24U', 'long' => 13.1985, 'lat' => -1.5658, 'status' => 'active',
                'zone_id' => $zoneMoaSS?->id,
            ],
        ];

        foreach ($coffrets as $coffretData) {
            $coffret = Coffret::updateOrCreate(['code' => $coffretData['code']], $coffretData);

            // Add sample equipments for the first coffret
            if ($coffretData['code'] === 'CAB-001') {
                $switch = Equipement::updateOrCreate(
                    ['equipement_code' => 'EQ-1001'],
                    [
                        'name' => 'Switch Core', 'type' => 'Switch', 'classification' => 'IT',
                        'fabricant' => 'Cisco', 'modele' => 'C9300-24P', 'serial_number' => 'FCW2345L0AB',
                        'connection_type' => 'RJ45', 'description' => 'Switch core layer 3',
                        'direction_in_out' => 'OUT', 'vlan' => '10', 'ip_address' => '10.0.0.10',
                        'coffret_id' => $coffret->id, 'status' => 'active',
                    ]
                );

                $router = Equipement::updateOrCreate(
                    ['equipement_code' => 'EQ-1002'],
                    [
                        'name' => 'Router Principal', 'type' => 'Router', 'classification' => 'IT',
                        'fabricant' => 'Juniper', 'modele' => 'MX204', 'serial_number' => 'JN1234AB567',
                        'connection_type' => 'SFP+', 'description' => 'Router principal WAN',
                        'direction_in_out' => 'IN', 'vlan' => '1', 'ip_address' => '10.0.0.1',
                        'coffret_id' => $coffret->id, 'status' => 'active',
                    ]
                );

                // Ports for switch
                Port::updateOrCreate(
                    ['port_label' => 'P1', 'equipement_id' => $switch->id],
                    ['device_name' => 'Switch Core', 'poe_enabled' => true, 'vlan' => '10', 'speed' => '1G', 'status' => 'active', 'port_type' => 'RJ45']
                );
                Port::updateOrCreate(
                    ['port_label' => 'P2', 'equipement_id' => $switch->id],
                    ['device_name' => 'Switch Core', 'poe_enabled' => true, 'vlan' => '20', 'speed' => '1G', 'status' => 'active', 'port_type' => 'RJ45']
                );
                Port::updateOrCreate(
                    ['port_label' => 'P24', 'equipement_id' => $switch->id],
                    ['device_name' => 'Switch Core', 'poe_enabled' => false, 'vlan' => '1', 'speed' => '10G', 'status' => 'active', 'port_type' => 'SFP+', 'connected_equipment_id' => $router->id]
                );
            }

            if ($coffretData['code'] === 'CAB-002') {
                Equipement::updateOrCreate(
                    ['equipement_code' => 'EQ-2001'],
                    [
                        'name' => 'Switch Edge', 'type' => 'Switch', 'classification' => 'IT',
                        'fabricant' => 'HP', 'modele' => '2930F-24G', 'serial_number' => 'HP9876XY012',
                        'connection_type' => 'RJ45', 'description' => 'Switch de distribution',
                        'direction_in_out' => 'IN', 'vlan' => '20', 'ip_address' => '10.0.0.11',
                        'coffret_id' => $coffret->id, 'status' => 'active',
                    ]
                );

                Equipement::updateOrCreate(
                    ['equipement_code' => 'EQ-2002'],
                    [
                        'name' => 'AP Lobby', 'type' => 'AP', 'classification' => 'IT',
                        'fabricant' => 'Ubiquiti', 'modele' => 'UAP-AC-PRO', 'serial_number' => 'UBI5678CD',
                        'connection_type' => 'RJ45', 'description' => 'Point d\'accès lobby',
                        'direction_in_out' => 'IN', 'vlan' => '30', 'ip_address' => '10.0.30.5',
                        'coffret_id' => $coffret->id, 'status' => 'active',
                    ]
                );
            }
        }
    }
}
