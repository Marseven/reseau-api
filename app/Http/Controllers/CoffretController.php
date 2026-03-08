<?php

namespace App\Http\Controllers;

use App\Models\Coffret;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCoffretRequest;
use App\Http\Requests\UpdateCoffretRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class CoffretController extends Controller
{
    #[OA\Get(
        path: '/coffrets',
        summary: 'Lister les coffrets',
        description: 'Retourne la liste paginée des coffrets avec leurs équipements, métriques et zone.',
        tags: ['Coffrets'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'zone_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'salle_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des coffrets'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Coffret::with('zone')->withCount('equipments');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $coffrets = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($coffrets);
    }

    #[OA\Post(
        path: '/coffrets',
        summary: 'Créer un coffret',
        description: 'Crée un nouveau coffret. Supporte l\'upload de photo.',
        tags: ['Coffrets'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['code', 'name', 'piece'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'COF-001'),
                    new OA\Property(property: 'name', type: 'string', example: 'Coffret principal'),
                    new OA\Property(property: 'piece', type: 'string', example: 'Salle serveur'),
                    new OA\Property(property: 'type', type: 'string', example: 'mural'),
                    new OA\Property(property: 'long', type: 'number', format: 'float', example: 2.345),
                    new OA\Property(property: 'lat', type: 'number', format: 'float', example: 48.856),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'maintenance'], example: 'active'),
                    new OA\Property(property: 'zone_id', type: 'integer', example: 1),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Coffret créé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreCoffretRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('coffrets/photos', 'public');
        }

        $coffret = Coffret::create($data);

        return ApiResponse::created($coffret, 'Coffret créé avec succès.');
    }

    #[OA\Get(
        path: '/coffrets/{id}',
        summary: 'Afficher un coffret',
        description: 'Retourne les détails d\'un coffret avec ses équipements, métriques et zone.',
        tags: ['Coffrets'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du coffret'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Coffret non trouvé'),
        ]
    )]
    public function show(Coffret $coffret)
    {
        return ApiResponse::success($coffret->load('equipments', 'metrics', 'zone'));
    }

    #[OA\Put(
        path: '/coffrets/{id}',
        summary: 'Mettre à jour un coffret',
        description: 'Met à jour les informations d\'un coffret existant. Supporte le remplacement de photo.',
        tags: ['Coffrets'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'COF-001'),
                    new OA\Property(property: 'name', type: 'string', example: 'Coffret principal'),
                    new OA\Property(property: 'piece', type: 'string', example: 'Salle serveur'),
                    new OA\Property(property: 'type', type: 'string', example: 'mural'),
                    new OA\Property(property: 'long', type: 'number', format: 'float', example: 2.345),
                    new OA\Property(property: 'lat', type: 'number', format: 'float', example: 48.856),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'maintenance'], example: 'active'),
                    new OA\Property(property: 'zone_id', type: 'integer', example: 1),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Coffret mis à jour avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Coffret non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateCoffretRequest $request, Coffret $coffret)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($coffret->photo) {
                Storage::disk('public')->delete($coffret->photo);
            }
            $data['photo'] = $request->file('photo')->store('coffrets/photos', 'public');
        }

        $coffret->update($data);

        return ApiResponse::success($coffret, 'Coffret mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/coffrets/{id}/photo',
        summary: 'Supprimer la photo d\'un coffret',
        description: 'Supprime la photo associée à un coffret.',
        tags: ['Coffrets'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Photo supprimée avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Coffret non trouvé'),
        ]
    )]
    public function deletePhoto(Coffret $coffret)
    {
        if ($coffret->photo) {
            Storage::disk('public')->delete($coffret->photo);
            $coffret->update(['photo' => null]);
        }

        return ApiResponse::success($coffret, 'Photo supprimée avec succès.');
    }

    #[OA\Delete(
        path: '/coffrets/{id}',
        summary: 'Supprimer un coffret',
        description: 'Supprime un coffret existant.',
        tags: ['Coffrets'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Coffret supprimé avec succès'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Coffret non trouvé'),
        ]
    )]
    public function destroy(Coffret $coffret)
    {
        $coffret->delete();

        return ApiResponse::success(null, 'Coffret supprimé avec succès.');
    }
}
