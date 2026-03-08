<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/users',
        summary: 'Lister les utilisateurs',
        security: [['sanctum' => []]],
        tags: ['Utilisateurs'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'role', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des utilisateurs'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($users);
    }

    #[OA\Post(
        path: '/users',
        summary: 'Créer un utilisateur',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Utilisateurs'],
        responses: [
            new OA\Response(response: 201, description: 'Utilisateur créé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return ApiResponse::created($user, 'Utilisateur créé avec succès.');
    }

    #[OA\Get(
        path: '/users/{id}',
        summary: "Détail d'un utilisateur",
        security: [['sanctum' => []]],
        tags: ['Utilisateurs'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: "Détail de l'utilisateur"),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
        ]
    )]
    public function show(User $user)
    {
        return ApiResponse::success($user->load('site'));
    }

    #[OA\Put(
        path: '/users/{id}',
        summary: 'Modifier un utilisateur',
        security: [['sanctum' => []]],
        tags: ['Utilisateurs'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Utilisateur mis à jour'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return ApiResponse::success($user, 'Utilisateur mis à jour avec succès.');
    }

    #[OA\Delete(
        path: '/users/{id}',
        summary: 'Désactiver un utilisateur',
        security: [['sanctum' => []]],
        tags: ['Utilisateurs'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Utilisateur désactivé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé'),
        ]
    )]
    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);

        return ApiResponse::success(null, 'Utilisateur désactivé avec succès.');
    }
}
