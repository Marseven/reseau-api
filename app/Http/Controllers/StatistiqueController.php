<?php

namespace App\Http\Controllers;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Port;
use App\Models\Metric;
use App\Models\Liaison;
use App\Models\System;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Cache;

class StatistiqueController extends Controller
{
    public function globalStats()
    {
        $stats = Cache::remember('stats.global', 300, function () {
            return [
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
            ];
        });

        return ApiResponse::success($stats);
    }

    public function systemsByType()
    {
        $data = Cache::remember('stats.systems_by_type', 300, function () {
            return System::selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->get();
        });

        return ApiResponse::success($data);
    }

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
