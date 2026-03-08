<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\Port;
use App\Models\Site;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class AnalyticsController extends Controller
{
    #[OA\Get(
        path: '/analytics/equipements-by-type',
        summary: 'Répartition des équipements par type',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function equipementsByType()
    {
        $data = Cache::remember('analytics.equipements_by_type', 300, function () {
            return Equipement::select('type', DB::raw('count(*) as count'))
                ->whereNotNull('type')
                ->groupBy('type')
                ->orderByDesc('count')
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/analytics/equipements-by-classification',
        summary: 'Répartition des équipements par classification (IT/OT)',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function equipementsByClassification()
    {
        $data = Cache::remember('analytics.equipements_by_classification', 300, function () {
            return Equipement::select('classification', DB::raw('count(*) as count'))
                ->whereNotNull('classification')
                ->groupBy('classification')
                ->orderByDesc('count')
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/analytics/equipements-by-status',
        summary: 'Répartition des équipements par statut',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function equipementsByStatus()
    {
        $data = Cache::remember('analytics.equipements_by_status', 300, function () {
            return Equipement::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->orderByDesc('count')
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/analytics/equipements-by-vendor',
        summary: 'Top 10 des fabricants d\'équipements',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function equipementsByVendor()
    {
        $data = Cache::remember('analytics.equipements_by_vendor', 300, function () {
            return Equipement::select('fabricant', DB::raw('count(*) as count'))
                ->whereNotNull('fabricant')
                ->where('fabricant', '!=', '')
                ->groupBy('fabricant')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/analytics/maintenance-trends',
        summary: 'Tendances de maintenance sur les 12 derniers mois',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function maintenanceTrends()
    {
        $data = Cache::remember('analytics.maintenance_trends', 300, function () {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                return Maintenance::select(
                    DB::raw("strftime('%Y-%m', scheduled_date) as month"),
                    DB::raw('count(*) as count')
                )
                    ->where('scheduled_date', '>=', now()->subMonths(12)->startOfMonth())
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
            }

            return Maintenance::select(
                DB::raw("DATE_FORMAT(scheduled_date, '%Y-%m') as month"),
                DB::raw('count(*) as count')
            )
                ->where('scheduled_date', '>=', now()->subMonths(12)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/analytics/port-utilization',
        summary: 'Taux d\'utilisation des ports réseau',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function portUtilization()
    {
        $data = Cache::remember('analytics.port_utilization', 300, function () {
            $total = Port::count();
            $connected = Port::whereNotNull('connected_equipment_id')->count();

            return [
                'total' => $total,
                'connected' => $connected,
                'free' => $total - $connected,
                'utilization_percent' => $total > 0 ? round(($connected / $total) * 100, 1) : 0,
            ];
        });

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/analytics/sites-summary',
        summary: 'Résumé par site (zones, coffrets, équipements, maintenances)',
        tags: ['Analytics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Données analytiques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function sitesSummary()
    {
        $data = Cache::remember('analytics.sites_summary', 300, function () {
            return Site::withCount('zones')
                ->get()
                ->map(function ($site) {
                    $coffretIds = Coffret::whereHas('zone', fn ($q) => $q->where('site_id', $site->id))
                        ->pluck('id');

                    $coffretsCount = $coffretIds->count();
                    $equipementsCount = $coffretsCount > 0 ? Equipement::whereIn('coffret_id', $coffretIds)->count() : 0;
                    $maintenanceActiveCount = Maintenance::where('site_id', $site->id)
                        ->whereIn('status', ['planifiee', 'en_cours'])
                        ->count();

                    return [
                        'id' => $site->id,
                        'name' => $site->name,
                        'code' => $site->code,
                        'zones_count' => $site->zones_count,
                        'coffrets_count' => $coffretsCount,
                        'equipements_count' => $equipementsCount,
                        'maintenance_active_count' => $maintenanceActiveCount,
                    ];
                });
        });

        return ApiResponse::success($data);
    }
}
