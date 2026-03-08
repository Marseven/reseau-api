<?php

namespace App\Http\Controllers;

use App\Models\Liaison;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreLiaisonRequest;
use App\Http\Requests\UpdateLiaisonRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LiaisonController extends Controller
{
    #[OA\Get(
        path: '/liaisons',
        summary: 'Lister les liaisons',
        description: 'Retourne la liste paginée des liaisons avec les équipements source et destination.',
        tags: ['Liaisons'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des liaisons'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Liaison::with('fromEquipement', 'toEquipement');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('label', 'like', '%' . $request->search . '%');
        }

        $liaisons = $query->orderBy('label')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($liaisons);
    }

    #[OA\Post(
        path: '/liaisons',
        summary: 'Créer une liaison',
        description: 'Crée une nouvelle liaison entre deux équipements.',
        tags: ['Liaisons'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['from', 'to', 'label', 'media', 'status'],
                properties: [
                    new OA\Property(property: 'from', type: 'integer', description: 'ID équipement source', example: 1),
                    new OA\Property(property: 'to', type: 'integer', description: 'ID équipement destination', example: 2),
                    new OA\Property(property: 'label', type: 'string', example: 'Liaison fibre A-B'),
                    new OA\Property(property: 'media', type: 'string', example: 'fibre optique'),
                    new OA\Property(property: 'length', type: 'integer', example: 150),
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'from_port_id', type: 'integer', example: 1),
                    new OA\Property(property: 'to_port_id', type: 'integer', example: 2),
                    new OA\Property(property: 'status_label', type: 'string', enum: ['active', 'inactive', 'maintenance'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Liaison créée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreLiaisonRequest $request)
    {
        $liaison = Liaison::create($request->validated());

        return ApiResponse::created($liaison, 'Liaison créée avec succès.');
    }

    #[OA\Get(
        path: '/liaisons/{id}',
        summary: 'Afficher une liaison',
        description: 'Retourne les détails d\'une liaison avec les équipements source et destination.',
        tags: ['Liaisons'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de la liaison'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Liaison non trouvée'),
        ]
    )]
    public function show(Liaison $liaison)
    {
        return ApiResponse::success($liaison->load('fromEquipement', 'toEquipement'));
    }

    #[OA\Put(
        path: '/liaisons/{id}',
        summary: 'Mettre à jour une liaison',
        description: 'Met à jour les informations d\'une liaison existante.',
        tags: ['Liaisons'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'from', type: 'integer', description: 'ID équipement source', example: 1),
                    new OA\Property(property: 'to', type: 'integer', description: 'ID équipement destination', example: 2),
                    new OA\Property(property: 'label', type: 'string', example: 'Liaison fibre A-B'),
                    new OA\Property(property: 'media', type: 'string', example: 'fibre optique'),
                    new OA\Property(property: 'length', type: 'integer', example: 150),
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'from_port_id', type: 'integer', example: 1),
                    new OA\Property(property: 'to_port_id', type: 'integer', example: 2),
                    new OA\Property(property: 'status_label', type: 'string', enum: ['active', 'inactive', 'maintenance'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Liaison mise à jour avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Liaison non trouvée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateLiaisonRequest $request, Liaison $liaison)
    {
        $liaison->update($request->validated());

        return ApiResponse::success($liaison, 'Liaison mise à jour avec succès.');
    }

    #[OA\Delete(
        path: '/liaisons/{id}',
        summary: 'Supprimer une liaison',
        description: 'Supprime une liaison existante.',
        tags: ['Liaisons'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liaison supprimée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Liaison non trouvée'),
        ]
    )]
    public function destroy(Liaison $liaison)
    {
        $liaison->delete();

        return ApiResponse::success(null, 'Liaison supprimée avec succès.');
    }
}
