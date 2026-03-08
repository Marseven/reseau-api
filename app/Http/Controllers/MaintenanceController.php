<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MaintenanceController extends Controller
{
    #[OA\Get(
        path: '/maintenances',
        summary: 'Lister les maintenances',
        description: 'Retourne la liste paginée des maintenances avec technicien, équipement, coffret et site.',
        tags: ['Maintenances'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'technicien_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'site_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des maintenances'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Maintenance::with(['technicien', 'equipement', 'coffret', 'site']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('technicien_id')) {
            $query->where('technicien_id', $request->technicien_id);
        }

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $maintenances = $query->orderByDesc('scheduled_date')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($maintenances);
    }

    #[OA\Post(
        path: '/maintenances',
        summary: 'Créer une maintenance',
        description: 'Crée une nouvelle intervention de maintenance.',
        tags: ['Maintenances'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['code', 'title', 'type', 'priority', 'technicien_id', 'scheduled_date'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'MAINT-001'),
                    new OA\Property(property: 'title', type: 'string', example: 'Remplacement switch'),
                    new OA\Property(property: 'description', type: 'string', example: 'Remplacement du switch défectueux'),
                    new OA\Property(property: 'type', type: 'string', enum: ['preventive', 'corrective', 'urgente', 'evolutive'], example: 'corrective'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['basse', 'moyenne', 'haute', 'critique'], example: 'haute'),
                    new OA\Property(property: 'status', type: 'string', enum: ['planifiee', 'en_cours', 'terminee', 'annulee'], example: 'planifiee'),
                    new OA\Property(property: 'equipement_id', type: 'integer', example: 1),
                    new OA\Property(property: 'coffret_id', type: 'integer', example: 1),
                    new OA\Property(property: 'site_id', type: 'integer', example: 1),
                    new OA\Property(property: 'technicien_id', type: 'integer', example: 2),
                    new OA\Property(property: 'validator_id', type: 'integer', example: 3),
                    new OA\Property(property: 'scheduled_date', type: 'string', format: 'date', example: '2026-03-15'),
                    new OA\Property(property: 'scheduled_time', type: 'string', format: 'time', example: '14:00'),
                    new OA\Property(property: 'started_at', type: 'string', format: 'date-time', example: '2026-03-15T14:00:00Z'),
                    new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', example: '2026-03-15T16:00:00Z'),
                    new OA\Property(property: 'duration_minutes', type: 'integer', example: 120),
                    new OA\Property(property: 'notes', type: 'string', example: 'Intervention réalisée sans incident'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Maintenance créée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreMaintenanceRequest $request)
    {
        $maintenance = Maintenance::create($request->validated());

        return ApiResponse::created($maintenance, 'Maintenance créée avec succès.');
    }

    #[OA\Get(
        path: '/maintenances/{id}',
        summary: 'Afficher une maintenance',
        description: 'Retourne les détails d\'une maintenance avec technicien, validateur, équipement, coffret et site.',
        tags: ['Maintenances'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de la maintenance'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Maintenance non trouvée'),
        ]
    )]
    public function show(Maintenance $maintenance)
    {
        return ApiResponse::success($maintenance->load('technicien', 'validator', 'equipement', 'coffret', 'site'));
    }

    #[OA\Put(
        path: '/maintenances/{id}',
        summary: 'Mettre à jour une maintenance',
        description: 'Met à jour les informations d\'une maintenance existante. Envoie une notification si le statut passe à "en_cours".',
        tags: ['Maintenances'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'MAINT-001'),
                    new OA\Property(property: 'title', type: 'string', example: 'Remplacement switch'),
                    new OA\Property(property: 'description', type: 'string', example: 'Remplacement du switch défectueux'),
                    new OA\Property(property: 'type', type: 'string', enum: ['preventive', 'corrective', 'urgente', 'evolutive'], example: 'corrective'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['basse', 'moyenne', 'haute', 'critique'], example: 'haute'),
                    new OA\Property(property: 'status', type: 'string', enum: ['planifiee', 'en_cours', 'terminee', 'annulee'], example: 'en_cours'),
                    new OA\Property(property: 'equipement_id', type: 'integer', example: 1),
                    new OA\Property(property: 'coffret_id', type: 'integer', example: 1),
                    new OA\Property(property: 'site_id', type: 'integer', example: 1),
                    new OA\Property(property: 'technicien_id', type: 'integer', example: 2),
                    new OA\Property(property: 'validator_id', type: 'integer', example: 3),
                    new OA\Property(property: 'scheduled_date', type: 'string', format: 'date', example: '2026-03-15'),
                    new OA\Property(property: 'scheduled_time', type: 'string', format: 'time', example: '14:00'),
                    new OA\Property(property: 'started_at', type: 'string', format: 'date-time', example: '2026-03-15T14:00:00Z'),
                    new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', example: '2026-03-15T16:00:00Z'),
                    new OA\Property(property: 'duration_minutes', type: 'integer', example: 120),
                    new OA\Property(property: 'notes', type: 'string', example: 'Intervention réalisée sans incident'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Maintenance mise à jour avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Maintenance non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance)
    {
        $wasNotEnCours = $maintenance->status !== 'en_cours';

        $maintenance->update($request->validated());

        if ($wasNotEnCours && $maintenance->status === 'en_cours') {
            app(NotificationService::class)->notifyMaintenanceActive($maintenance);
        }

        return ApiResponse::success($maintenance, 'Maintenance mise à jour avec succès.');
    }

    #[OA\Delete(
        path: '/maintenances/{id}',
        summary: 'Supprimer une maintenance',
        description: 'Supprime une maintenance existante.',
        tags: ['Maintenances'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Maintenance supprimée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Maintenance non trouvée'),
        ]
    )]
    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return ApiResponse::success(null, 'Maintenance supprimée avec succès.');
    }
}
