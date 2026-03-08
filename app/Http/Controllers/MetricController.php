<?php

namespace App\Http\Controllers;

use App\Models\Metric;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMetricRequest;
use App\Http\Requests\UpdateMetricRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MetricController extends Controller
{
    #[OA\Get(
        path: '/metrics',
        summary: 'Lister les métriques',
        description: 'Retourne la liste paginée des métriques avec leur coffret associé.',
        tags: ['Métriques'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des métriques'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Metric::with('coffret');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $metrics = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($metrics);
    }

    #[OA\Post(
        path: '/metrics',
        summary: 'Créer une métrique',
        description: 'Crée une nouvelle métrique associée à un coffret.',
        tags: ['Métriques'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['name', 'type', 'coffret_id', 'status'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Température'),
                    new OA\Property(property: 'type', type: 'string', example: 'sensor'),
                    new OA\Property(property: 'description', type: 'string', example: 'Capteur de température interne'),
                    new OA\Property(property: 'last_value', type: 'string', example: '23.5'),
                    new OA\Property(property: 'coffret_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Métrique créée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreMetricRequest $request)
    {
        $metric = Metric::create($request->validated());

        return ApiResponse::created($metric, 'Metric créée avec succès.');
    }

    #[OA\Get(
        path: '/metrics/{id}',
        summary: 'Afficher une métrique',
        description: 'Retourne les détails d\'une métrique avec son coffret associé.',
        tags: ['Métriques'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de la métrique'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Métrique non trouvée'),
        ]
    )]
    public function show(Metric $metric)
    {
        return ApiResponse::success($metric->load('coffret'));
    }

    #[OA\Put(
        path: '/metrics/{id}',
        summary: 'Mettre à jour une métrique',
        description: 'Met à jour les informations d\'une métrique existante.',
        tags: ['Métriques'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Température'),
                    new OA\Property(property: 'type', type: 'string', example: 'sensor'),
                    new OA\Property(property: 'description', type: 'string', example: 'Capteur de température interne'),
                    new OA\Property(property: 'last_value', type: 'string', example: '23.5'),
                    new OA\Property(property: 'coffret_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Métrique mise à jour avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Métrique non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateMetricRequest $request, Metric $metric)
    {
        $metric->update($request->validated());

        return ApiResponse::success($metric, 'Metric mise à jour avec succès.');
    }

    #[OA\Delete(
        path: '/metrics/{id}',
        summary: 'Supprimer une métrique',
        description: 'Supprime une métrique existante.',
        tags: ['Métriques'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Métrique supprimée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Métrique non trouvée'),
        ]
    )]
    public function destroy(Metric $metric)
    {
        $metric->delete();

        return ApiResponse::success(null, 'Metric supprimée avec succès.');
    }
}
