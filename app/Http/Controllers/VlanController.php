<?php

namespace App\Http\Controllers;

use App\Models\Vlan;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VlanController extends Controller
{
    #[OA\Get(
        path: '/vlans',
        summary: 'Lister les VLANs',
        tags: ['VLANs'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'site_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Vlan::with('site');

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vlans = $query->orderBy('vlan_id')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($vlans);
    }

    #[OA\Post(
        path: '/vlans',
        summary: 'Créer un VLAN',
        tags: ['VLANs'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 201, description: 'Créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreVlanRequest $request)
    {
        $vlan = Vlan::create($request->validated());

        return ApiResponse::created($vlan, 'VLAN créé avec succès.');
    }

    #[OA\Get(
        path: '/vlans/{id}',
        summary: 'Détail d\'un VLAN',
        tags: ['VLANs'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(Vlan $vlan)
    {
        return ApiResponse::success($vlan->load('site'));
    }

    #[OA\Put(
        path: '/vlans/{id}',
        summary: 'Modifier un VLAN',
        tags: ['VLANs'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Modifié avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {
        $vlan->update($request->validated());

        return ApiResponse::success($vlan, 'VLAN mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/vlans/{id}',
        summary: 'Supprimer un VLAN',
        tags: ['VLANs'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function destroy(Vlan $vlan)
    {
        $vlan->delete();

        return ApiResponse::success(null, 'VLAN supprimé avec succès.');
    }
}
