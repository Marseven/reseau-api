<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ActivityLog;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\Port;
use App\Models\Site;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    #[OA\Get(
        path: '/reports/summary',
        summary: 'Résumé des activités et maintenances',
        tags: ['Rapports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Résumé des rapports'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function summary(Request $request)
    {
        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $logsQuery = ActivityLog::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $totalLogs = (clone $logsQuery)->count();
        $byAction = (clone $logsQuery)
            ->selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action');

        $maintenancesQuery = Maintenance::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $totalMaintenances = (clone $maintenancesQuery)->count();
        $byStatus = (clone $maintenancesQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return ApiResponse::success([
            'period' => ['from' => $from, 'to' => $to],
            'modifications' => [
                'total' => $totalLogs,
                'by_action' => $byAction,
            ],
            'interventions' => [
                'total' => $totalMaintenances,
                'by_status' => $byStatus,
            ],
            'sites_count' => Site::count(),
        ], 'Résumé des rapports.');
    }

    #[OA\Get(
        path: '/reports/network-status/pdf',
        summary: 'Rapport PDF du statut réseau',
        tags: ['Rapports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function networkStatus()
    {
        $sites = Site::with('zones.coffrets.equipments.ports')->get();

        $siteStats = $sites->map(function ($site) {
            $zones = $site->zones;
            $coffrets = $zones->flatMap->coffrets;
            $equipements = $coffrets->flatMap->equipments;
            $ports = $equipements->flatMap->ports;

            return [
                'name' => $site->name,
                'zones_total' => $zones->count(),
                'zones_active' => $zones->where('status', 'active')->count(),
                'coffrets_total' => $coffrets->count(),
                'coffrets_active' => $coffrets->where('status', 'active')->count(),
                'equipements_total' => $equipements->count(),
                'equipements_active' => $equipements->where('status', 'active')->count(),
                'ports_total' => $ports->count(),
            ];
        });

        $pdf = Pdf::loadView('exports.network-status', [
            'siteStats' => $siteStats,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ]);

        return $pdf->download('statut-reseau.pdf');
    }

    #[OA\Get(
        path: '/reports/modifications/pdf',
        summary: 'Rapport PDF des modifications',
        tags: ['Rapports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function modifications(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        $logs = ActivityLog::with('user')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

        $logsByDate = $logs->groupBy(fn ($log) => $log->created_at->format('d/m/Y'));

        $byAction = $logs->groupBy('action')->map->count();
        $byUser = $logs->groupBy(fn ($log) => $log->user?->name ?? 'N/A')->map->count();

        $summary = [
            'total' => $logs->count(),
            'by_action' => $byAction->toArray(),
            'by_user' => $byUser->toArray(),
        ];

        $pdf = Pdf::loadView('exports.modifications', [
            'logsByDate' => $logsByDate,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ]);

        return $pdf->download('modifications.pdf');
    }

    #[OA\Get(
        path: '/reports/interventions/pdf',
        summary: 'Rapport PDF des interventions',
        tags: ['Rapports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function interventions(Request $request)
    {
        $query = Maintenance::with(['technicien', 'site']);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('technicien_id')) {
            $query->where('technicien_id', $request->technicien_id);
        }

        $maintenances = $query->orderBy('scheduled_date', 'desc')->get();

        $grouped = $maintenances->groupBy('technicien_id');

        $technicienSummaries = $grouped->map(function ($items, $techId) {
            $tech = $items->first()->technicien;
            return [
                'name' => $tech?->name ?? 'Non assigné',
                'total' => $items->count(),
                'terminee' => $items->where('status', 'terminee')->count(),
                'en_cours' => $items->where('status', 'en_cours')->count(),
                'planifiee' => $items->where('status', 'planifiee')->count(),
                'maintenances' => $items,
            ];
        })->values();

        $pdf = Pdf::loadView('exports.interventions', [
            'technicienSummaries' => $technicienSummaries,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ]);

        return $pdf->download('interventions.pdf');
    }

    #[OA\Get(
        path: '/reports/site/{site}/architecture/pdf',
        summary: 'Rapport PDF de l\'architecture d\'un site',
        tags: ['Rapports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'site', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Site non trouvé'),
        ]
    )]
    public function siteArchitecturePdf(Site $site)
    {
        $site->load('zones.coffrets.equipments');

        $counts = [
            'sites' => 1,
            'zones' => $site->zones->count(),
            'batiments' => 0,
            'salles' => 0,
            'coffrets' => $site->zones->flatMap->coffrets->count(),
            'equipements' => $site->zones->flatMap->coffrets->flatMap->equipments->count(),
        ];

        $pdf = Pdf::loadView('exports.architecture', [
            'sites' => collect([$site]),
            'counts' => $counts,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("architecture-{$site->code}.pdf");
    }
}
