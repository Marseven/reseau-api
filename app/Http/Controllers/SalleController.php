<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSalleRequest;
use App\Http\Requests\UpdateSalleRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SalleController extends Controller
{
    #[OA\Get(
        path: '/salles',
        summary: 'Lister les salles',
        tags: ['Salles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'batiment_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Salle::with('batiment');

        if ($request->has('batiment_id')) {
            $query->where('batiment_id', $request->batiment_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $salles = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($salles);
    }

    #[OA\Post(
        path: '/salles',
        summary: 'Créer une salle',
        tags: ['Salles'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 201, description: 'Créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreSalleRequest $request)
    {
        $salle = Salle::create($request->validated());

        return ApiResponse::created($salle, 'Salle créée avec succès.');
    }

    #[OA\Get(
        path: '/salles/{id}',
        summary: 'Détail d\'une salle',
        tags: ['Salles'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(Salle $salle)
    {
        return ApiResponse::success($salle->load('batiment', 'coffrets'));
    }

    #[OA\Put(
        path: '/salles/{id}',
        summary: 'Modifier une salle',
        tags: ['Salles'],
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
    public function update(UpdateSalleRequest $request, Salle $salle)
    {
        $salle->update($request->validated());

        return ApiResponse::success($salle, 'Salle mise à jour avec succès.');
    }

    #[OA\Delete(
        path: '/salles/{id}',
        summary: 'Supprimer une salle',
        tags: ['Salles'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function destroy(Salle $salle)
    {
        $salle->delete();

        return ApiResponse::success(null, 'Salle supprimée avec succès.');
    }
}
