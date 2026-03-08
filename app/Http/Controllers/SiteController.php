<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SiteController extends Controller
{
    #[OA\Get(
        path: '/sites',
        summary: 'Lister les sites',
        tags: ['Sites'],
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
        $query = Site::withCount('zones');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sites = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($sites);
    }

    #[OA\Post(
        path: '/sites',
        summary: 'Créer un site',
        tags: ['Sites'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 201, description: 'Créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreSiteRequest $request)
    {
        $site = Site::create($request->validated());

        return ApiResponse::created($site, 'Site créé avec succès.');
    }

    #[OA\Get(
        path: '/sites/{id}',
        summary: 'Détail d\'un site',
        tags: ['Sites'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(Site $site)
    {
        return ApiResponse::success($site->load('zones'));
    }

    #[OA\Put(
        path: '/sites/{id}',
        summary: 'Modifier un site',
        tags: ['Sites'],
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
    public function update(UpdateSiteRequest $request, Site $site)
    {
        $site->update($request->validated());

        return ApiResponse::success($site, 'Site mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/sites/{id}',
        summary: 'Supprimer un site',
        tags: ['Sites'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function destroy(Site $site)
    {
        $site->delete();

        return ApiResponse::success(null, 'Site supprimé avec succès.');
    }
}
