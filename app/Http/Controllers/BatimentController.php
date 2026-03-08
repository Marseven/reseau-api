<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreBatimentRequest;
use App\Http\Requests\UpdateBatimentRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BatimentController extends Controller
{
    #[OA\Get(
        path: '/batiments',
        summary: 'Lister les bâtiments',
        tags: ['Bâtiments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'zone_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Batiment::with('zone')->withCount('salles');

        if ($request->has('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $batiments = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($batiments);
    }

    #[OA\Post(
        path: '/batiments',
        summary: 'Créer un bâtiment',
        tags: ['Bâtiments'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 201, description: 'Créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreBatimentRequest $request)
    {
        $batiment = Batiment::create($request->validated());

        return ApiResponse::created($batiment, 'Bâtiment créé avec succès.');
    }

    #[OA\Get(
        path: '/batiments/{id}',
        summary: 'Détail d\'un bâtiment',
        tags: ['Bâtiments'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(Batiment $batiment)
    {
        return ApiResponse::success($batiment->load('zone.site', 'salles'));
    }

    #[OA\Put(
        path: '/batiments/{id}',
        summary: 'Modifier un bâtiment',
        tags: ['Bâtiments'],
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
    public function update(UpdateBatimentRequest $request, Batiment $batiment)
    {
        $batiment->update($request->validated());

        return ApiResponse::success($batiment, 'Bâtiment mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/batiments/{id}',
        summary: 'Supprimer un bâtiment',
        tags: ['Bâtiments'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function destroy(Batiment $batiment)
    {
        $batiment->delete();

        return ApiResponse::success(null, 'Bâtiment supprimé avec succès.');
    }
}
