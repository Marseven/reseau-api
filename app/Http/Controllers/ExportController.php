<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use App\Models\Port;
use App\Models\Site;
use App\Models\Zone;
use App\Models\Batiment;
use App\Models\Salle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenApi\Attributes as OA;

class ExportController extends Controller
{
    #[OA\Get(
        path: '/exports/equipements/csv',
        summary: 'Exporter les équipements en CSV',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'site_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function exportEquipementsCsv(Request $request): StreamedResponse
    {
        $query = Equipement::with('coffret.zone.site');

        if ($request->filled('site_id')) {
            $query->whereHas('coffret.zone', function ($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });
        }

        $rows = $query->get()->map(fn ($e) => [
            $e->equipement_code,
            $e->name,
            $e->type,
            $e->classification,
            $e->ip_address,
            $e->vlan,
            $e->status,
            $e->coffret?->name,
            $e->coffret?->zone?->site?->name,
        ]);

        return $this->streamCsv(
            'equipements.csv',
            ['Code', 'Nom', 'Type', 'Classification', 'IP', 'VLAN', 'Status', 'Armoire', 'Site'],
            $rows
        );
    }

    #[OA\Get(
        path: '/exports/coffrets/csv',
        summary: 'Exporter les coffrets en CSV',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'site_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function exportCoffretsCsv(Request $request): StreamedResponse
    {
        $query = Coffret::with('zone.site', 'salle');

        if ($request->filled('site_id')) {
            $query->whereHas('zone', function ($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });
        }

        $rows = $query->get()->map(fn ($c) => [
            $c->code,
            $c->name,
            $c->piece,
            $c->type,
            $c->status,
            $c->zone?->name,
            $c->salle?->name,
            $c->zone?->site?->name,
        ]);

        return $this->streamCsv(
            'coffrets.csv',
            ['Code', 'Nom', 'Pièce', 'Type', 'Status', 'Zone', 'Salle', 'Site'],
            $rows
        );
    }

    #[OA\Get(
        path: '/exports/ports/csv',
        summary: 'Exporter les ports en CSV',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'equipement_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function exportPortsCsv(Request $request): StreamedResponse
    {
        $query = Port::with('equipement');

        if ($request->filled('equipement_id')) {
            $query->where('equipement_id', $request->equipement_id);
        }

        $rows = $query->get()->map(fn ($p) => [
            $p->port_label,
            $p->device_name,
            $p->port_type,
            $p->vlan,
            $p->speed,
            $p->poe_enabled ? 'Oui' : 'Non',
            $p->status,
            $p->equipement?->name,
        ]);

        return $this->streamCsv(
            'ports.csv',
            ['Label', 'Device', 'Type', 'VLAN', 'Speed', 'PoE', 'Status', 'Équipement'],
            $rows
        );
    }

    #[OA\Get(
        path: '/exports/liaisons/csv',
        summary: 'Exporter les liaisons en CSV',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function exportLiaisonsCsv(): StreamedResponse
    {
        $rows = Liaison::all()->map(fn ($l) => [
            $l->label,
            $l->from,
            $l->to,
            $l->media,
            $l->length,
            $l->status,
        ]);

        return $this->streamCsv(
            'liaisons.csv',
            ['Label', 'De', 'Vers', 'Média', 'Longueur', 'Status'],
            $rows
        );
    }

    #[OA\Get(
        path: '/exports/activity-logs/csv',
        summary: 'Exporter les logs d\'activité en CSV',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function exportActivityLogsCsv(Request $request): StreamedResponse
    {
        $query = ActivityLog::with('user');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $rows = $query->orderBy('created_at', 'desc')->get()->map(fn ($log) => [
            $log->created_at->format('d/m/Y H:i:s'),
            $log->user?->name,
            $log->action,
            class_basename($log->entity_type),
            $log->entity_id,
            $log->ip_address,
        ]);

        return $this->streamCsv(
            'historique.csv',
            ['Date', 'Utilisateur', 'Action', 'Type entité', 'ID', 'IP'],
            $rows
        );
    }

    #[OA\Get(
        path: '/exports/architecture/pdf',
        summary: 'Exporter l\'architecture réseau en PDF',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function exportArchitecturePdf()
    {
        $sites = Site::with('zones.coffrets.equipments')->get();

        $counts = [
            'sites' => Site::count(),
            'zones' => Zone::count(),
            'batiments' => Batiment::count(),
            'salles' => Salle::count(),
            'coffrets' => Coffret::count(),
            'equipements' => Equipement::count(),
        ];

        $pdf = Pdf::loadView('exports.architecture', [
            'sites' => $sites,
            'counts' => $counts,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('architecture-reseau.pdf');
    }

    private function streamCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, is_array($row) ? $row : $row->toArray(), ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
