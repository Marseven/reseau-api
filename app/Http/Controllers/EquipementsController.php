<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEquipementRequest;
use App\Http\Requests\UpdateEquipementRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EquipementsController extends Controller
{
    #[OA\Get(
        path: '/equipements',
        summary: 'Lister les équipements',
        description: 'Retourne la liste paginée des équipements avec leur coffret associé.',
        tags: ['Équipements'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'coffret_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des équipements'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Equipement::with('coffret')->withCount('ports');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $equipements = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($equipements);
    }

    #[OA\Post(
        path: '/equipements',
        summary: 'Créer un équipement',
        description: 'Crée un nouvel équipement réseau.',
        tags: ['Équipements'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['equipement_code', 'name', 'type', 'coffret_id', 'status'],
                properties: [
                    new OA\Property(property: 'equipement_code', type: 'string', example: 'EQ-001'),
                    new OA\Property(property: 'name', type: 'string', example: 'Switch principal'),
                    new OA\Property(property: 'type', type: 'string', example: 'switch'),
                    new OA\Property(property: 'classification', type: 'string', enum: ['IT', 'OT'], example: 'IT'),
                    new OA\Property(property: 'serial_number', type: 'string', example: 'SN-123456'),
                    new OA\Property(property: 'fabricant', type: 'string', example: 'Cisco'),
                    new OA\Property(property: 'modele', type: 'string', example: 'Catalyst 2960'),
                    new OA\Property(property: 'connection_type', type: 'string', example: 'ethernet'),
                    new OA\Property(property: 'description', type: 'string', example: 'Switch de distribution'),
                    new OA\Property(property: 'direction_in_out', type: 'string', example: 'in'),
                    new OA\Property(property: 'vlan', type: 'string', example: '100'),
                    new OA\Property(property: 'ip_address', type: 'string', format: 'ipv4', example: '192.168.1.1'),
                    new OA\Property(property: 'coffret_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'maintenance'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Équipement créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreEquipementRequest $request)
    {
        $equipement = Equipement::create($request->validated());

        return ApiResponse::created($equipement, 'Équipement créé avec succès.');
    }

    #[OA\Get(
        path: '/equipements/{id}',
        summary: 'Afficher un équipement',
        description: 'Retourne les détails d\'un équipement avec son coffret et ses ports.',
        tags: ['Équipements'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de l\'équipement'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Équipement non trouvé'),
        ]
    )]
    public function show(Equipement $equipement)
    {
        return ApiResponse::success($equipement->load('coffret', 'ports'));
    }

    #[OA\Put(
        path: '/equipements/{id}',
        summary: 'Mettre à jour un équipement',
        description: 'Met à jour les informations d\'un équipement existant.',
        tags: ['Équipements'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'equipement_code', type: 'string', example: 'EQ-001'),
                    new OA\Property(property: 'name', type: 'string', example: 'Switch principal'),
                    new OA\Property(property: 'type', type: 'string', example: 'switch'),
                    new OA\Property(property: 'classification', type: 'string', enum: ['IT', 'OT'], example: 'IT'),
                    new OA\Property(property: 'serial_number', type: 'string', example: 'SN-123456'),
                    new OA\Property(property: 'fabricant', type: 'string', example: 'Cisco'),
                    new OA\Property(property: 'modele', type: 'string', example: 'Catalyst 2960'),
                    new OA\Property(property: 'connection_type', type: 'string', example: 'ethernet'),
                    new OA\Property(property: 'description', type: 'string', example: 'Switch de distribution'),
                    new OA\Property(property: 'direction_in_out', type: 'string', example: 'in'),
                    new OA\Property(property: 'vlan', type: 'string', example: '100'),
                    new OA\Property(property: 'ip_address', type: 'string', format: 'ipv4', example: '192.168.1.1'),
                    new OA\Property(property: 'coffret_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'maintenance'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Équipement mis à jour avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Équipement non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateEquipementRequest $request, Equipement $equipement)
    {
        $equipement->update($request->validated());

        return ApiResponse::success($equipement, 'Équipement mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/equipements/{id}',
        summary: 'Supprimer un équipement',
        description: 'Supprime un équipement existant.',
        tags: ['Équipements'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Équipement supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Équipement non trouvé'),
        ]
    )]
    public function destroy(Equipement $equipement)
    {
        $equipement->delete();

        return ApiResponse::success(null, 'Équipement supprimé avec succès.');
    }
}
