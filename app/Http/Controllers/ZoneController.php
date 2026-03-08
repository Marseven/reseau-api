<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ZoneController extends Controller
{
    #[OA\Get(
        path: '/zones',
        summary: 'Lister les zones',
        tags: ['Zones'],
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
        $query = Zone::with('site')->withCount(['batiments', 'coffrets']);

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $zones = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($zones);
    }

    #[OA\Post(
        path: '/zones',
        summary: 'Créer une zone',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 201, description: 'Créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreZoneRequest $request)
    {
        $zone = Zone::create($request->validated());

        return ApiResponse::created($zone, 'Zone créée avec succès.');
    }

    #[OA\Get(
        path: '/zones/{id}',
        summary: 'Détail d\'une zone',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(Zone $zone)
    {
        return ApiResponse::success($zone->load('site', 'batiments', 'coffrets'));
    }

    #[OA\Put(
        path: '/zones/{id}',
        summary: 'Modifier une zone',
        tags: ['Zones'],
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
    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());

        return ApiResponse::success($zone, 'Zone mise à jour avec succès.');
    }

    #[OA\Delete(
        path: '/zones/{id}',
        summary: 'Supprimer une zone',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function destroy(Zone $zone)
    {
        $zone->delete();

        return ApiResponse::success(null, 'Zone supprimée avec succès.');
    }
}
