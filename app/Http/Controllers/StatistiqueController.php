<?php

namespace App\Http\Controllers;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Port;
use App\Models\Metric;
use App\Models\Liaison;
use App\Models\System;
use App\Models\Site;
use App\Models\Zone;
use App\Models\Batiment;
use App\Models\Salle;
use App\Models\Vlan;
use App\Models\Maintenance;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class StatistiqueController extends Controller
{
    public function publicStats()
    {
        $stats = Cache::remember('stats.public', 300, function () {
            $totalEquip = Equipement::count();
            $activeEquip = Equipement::where('status', 'active')->count();

            return [
                'coffrets' => Coffret::count(),
                'equipements' => $totalEquip,
                'uptime' => $totalEquip > 0
                    ? round(($activeEquip / $totalEquip) * 100, 1)
                    : 0,
            ];
        });

        return ApiResponse::success($stats);
    }

    #[OA\Get(
        path: '/stats/global',
        summary: 'Statistiques globales',
        tags: ['Statistiques'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Statistiques globales'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function globalStats()
    {
        $stats = Cache::remember('stats.global', 300, function () {
            return [
                'sites' => [
                    'total' => Site::count(),
                    'active' => Site::where('status', 'active')->count(),
                    'inactive' => Site::where('status', '!=', 'active')->count(),
                ],
                'zones' => [
                    'total' => Zone::count(),
                    'active' => Zone::where('status', 'active')->count(),
                    'inactive' => Zone::where('status', '!=', 'active')->count(),
                ],
                'coffrets' => [
                    'total' => Coffret::count(),
                    'active' => Coffret::where('status', 'active')->count(),
                    'inactive' => Coffret::where('status', 'inactive')->count(),
                ],
                'equipements' => [
                    'total' => Equipement::count(),
                    'active' => Equipement::where('status', 'active')->count(),
                    'inactive' => Equipement::where('status', 'inactive')->count(),
                ],
                'ports' => [
                    'total' => Port::count(),
                    'poe_enabled' => Port::where('poe_enabled', true)->count(),
                ],
                'metrics' => [
                    'total' => Metric::count(),
                    'active' => Metric::where('status', true)->count(),
                    'inactive' => Metric::where('status', false)->count(),
                ],
                'liaisons' => [
                    'total' => Liaison::count(),
                    'active' => Liaison::where('status', true)->count(),
                    'inactive' => Liaison::where('status', false)->count(),
                ],
                'systems' => [
                    'total' => System::count(),
                    'active' => System::where('status', true)->count(),
                    'inactive' => System::where('status', false)->count(),
                ],
                'batiments' => [
                    'total' => Batiment::count(),
                    'active' => Batiment::where('status', 'active')->count(),
                    'inactive' => Batiment::where('status', '!=', 'active')->count(),
                ],
                'salles' => [
                    'total' => Salle::count(),
                    'active' => Salle::where('status', 'active')->count(),
                    'inactive' => Salle::where('status', '!=', 'active')->count(),
                ],
                'vlans' => [
                    'total' => Vlan::count(),
                    'active' => Vlan::where('status', 'active')->count(),
                    'inactive' => Vlan::where('status', 'inactive')->count(),
                ],
                'maintenances' => [
                    'total' => Maintenance::count(),
                    'planifiee' => Maintenance::where('status', 'planifiee')->count(),
                    'en_cours' => Maintenance::where('status', 'en_cours')->count(),
                    'terminee' => Maintenance::where('status', 'terminee')->count(),
                ],
            ];
        });

        return ApiResponse::success($stats);
    }

    #[OA\Get(
        path: '/stats/systems-by-type',
        summary: 'Systèmes par type',
        tags: ['Statistiques'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Systèmes groupés par type'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function systemsByType()
    {
        $data = Cache::remember('stats.systems_by_type', 300, function () {
            return System::selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/stats/equipements-by-coffret',
        summary: 'Équipements par coffret',
        tags: ['Statistiques'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Équipements groupés par coffret'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function equipementsByCoffret()
    {
        $data = Cache::remember('stats.equipements_by_coffret', 300, function () {
            return Equipement::selectRaw('coffret_id, count(*) as total')
                ->groupBy('coffret_id')
                ->with('coffret:id,name')
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/stats/ports-by-vlan',
        summary: 'Ports par VLAN',
        tags: ['Statistiques'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Ports groupés par VLAN'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function portsByVlan()
    {
        $data = Cache::remember('stats.ports_by_vlan', 300, function () {
            return Port::selectRaw('vlan, count(*) as total')
                ->groupBy('vlan')
                ->get();
        });

        return ApiResponse::success($data);
    }
}
