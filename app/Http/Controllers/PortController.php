<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Helpers\ApiResponse;
use App\Http\Requests\StorePortRequest;
use App\Http\Requests\UpdatePortRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PortController extends Controller
{
    #[OA\Get(
        path: '/ports',
        summary: 'Lister les ports',
        description: 'Retourne la liste paginée des ports avec l\'équipement connecté.',
        tags: ['Ports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'vlan', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des ports'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Port::with('connectedEquipment');

        if ($request->has('vlan')) {
            $query->where('vlan', $request->vlan);
        }

        if ($request->has('search')) {
            $query->where('port_label', 'like', '%' . $request->search . '%');
        }

        $ports = $query->orderBy('port_label')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($ports);
    }

    #[OA\Post(
        path: '/ports',
        summary: 'Créer un port',
        description: 'Crée un nouveau port réseau.',
        tags: ['Ports'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['port_label', 'device_name', 'poe_enabled'],
                properties: [
                    new OA\Property(property: 'port_label', type: 'string', example: 'Gi0/1'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'Switch-01'),
                    new OA\Property(property: 'poe_enabled', type: 'boolean', example: true),
                    new OA\Property(property: 'vlan', type: 'string', example: '100'),
                    new OA\Property(property: 'speed', type: 'string', example: '1Gbps'),
                    new OA\Property(property: 'connected_equipment_id', type: 'integer', example: 1),
                    new OA\Property(property: 'equipement_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'reserved'], example: 'active'),
                    new OA\Property(property: 'port_type', type: 'string', example: 'RJ45'),
                    new OA\Property(property: 'description', type: 'string', example: 'Port vers caméra IP'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Port créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StorePortRequest $request)
    {
        $port = Port::create($request->validated());

        return ApiResponse::created($port, 'Port créé avec succès.');
    }

    #[OA\Get(
        path: '/ports/{id}',
        summary: 'Afficher un port',
        description: 'Retourne les détails d\'un port avec l\'équipement connecté.',
        tags: ['Ports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du port'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Port non trouvé'),
        ]
    )]
    public function show(Port $port)
    {
        return ApiResponse::success($port->load('connectedEquipment'));
    }

    #[OA\Put(
        path: '/ports/{id}',
        summary: 'Mettre à jour un port',
        description: 'Met à jour les informations d\'un port existant.',
        tags: ['Ports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'port_label', type: 'string', example: 'Gi0/1'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'Switch-01'),
                    new OA\Property(property: 'poe_enabled', type: 'boolean', example: true),
                    new OA\Property(property: 'vlan', type: 'string', example: '100'),
                    new OA\Property(property: 'speed', type: 'string', example: '1Gbps'),
                    new OA\Property(property: 'connected_equipment_id', type: 'integer', example: 1),
                    new OA\Property(property: 'equipement_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'reserved'], example: 'active'),
                    new OA\Property(property: 'port_type', type: 'string', example: 'RJ45'),
                    new OA\Property(property: 'description', type: 'string', example: 'Port vers caméra IP'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Port mis à jour avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Port non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdatePortRequest $request, Port $port)
    {
        $port->update($request->validated());

        return ApiResponse::success($port, 'Port mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/ports/{id}',
        summary: 'Supprimer un port',
        description: 'Supprime un port existant.',
        tags: ['Ports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Port supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Port non trouvé'),
        ]
    )]
    public function destroy(Port $port)
    {
        $port->delete();

        return ApiResponse::success(null, 'Port supprimé avec succès.');
    }
}
