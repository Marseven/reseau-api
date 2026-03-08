<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSystemRequest;
use App\Http\Requests\UpdateSystemRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SystemController extends Controller
{
    #[OA\Get(
        path: '/systems',
        summary: 'Lister les systèmes',
        tags: ['Systèmes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = System::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $systems = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($systems);
    }

    #[OA\Post(
        path: '/systems',
        summary: 'Créer un système',
        tags: ['Systèmes'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 201, description: 'Créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreSystemRequest $request)
    {
        $system = System::create($request->validated());

        return ApiResponse::created($system, 'Système créé avec succès.');
    }

    #[OA\Get(
        path: '/systems/{id}',
        summary: 'Détail d\'un système',
        tags: ['Systèmes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(System $system)
    {
        return ApiResponse::success($system);
    }

    #[OA\Put(
        path: '/systems/{id}',
        summary: 'Modifier un système',
        tags: ['Systèmes'],
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
    public function update(UpdateSystemRequest $request, System $system)
    {
        $system->update($request->validated());

        return ApiResponse::success($system, 'Système mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/systems/{id}',
        summary: 'Supprimer un système',
        tags: ['Systèmes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function destroy(System $system)
    {
        $system->delete();

        return ApiResponse::success(null, 'Système supprimé avec succès.');
    }
}
