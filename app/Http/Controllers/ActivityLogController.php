<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Coffret;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ActivityLogController extends Controller
{
    private const ENTITY_MAP = [
        'coffret' => \App\Models\Coffret::class,
        'equipement' => \App\Models\Equipement::class,
        'change_request' => \App\Models\ChangeRequest::class,
        'maintenance' => \App\Models\Maintenance::class,
        'port' => \App\Models\Port::class,
        'liaison' => \App\Models\Liaison::class,
        'metric' => \App\Models\Metric::class,
        'system' => \App\Models\System::class,
    ];

    #[OA\Get(
        path: '/activity-logs',
        summary: 'Lister les logs d\'activité',
        tags: ['Activité'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'entity_type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'entity_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'action', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'user_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des logs d\'activité'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->has('entity_type')) {
            $entityClass = self::ENTITY_MAP[$request->entity_type] ?? $request->entity_type;
            $query->where('entity_type', $entityClass);
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->integer('entity_id'));
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return ApiResponse::success($query->paginate($request->integer('per_page', 15)));
    }

    #[OA\Get(
        path: '/coffrets/{coffret}/history',
        summary: 'Historique d\'un coffret',
        tags: ['Activité'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'coffret', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Historique du coffret'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'Coffret non trouvé'),
        ]
    )]
    public function coffretHistory(Coffret $coffret, Request $request)
    {
        $equipmentIds = $coffret->equipments()->pluck('id')->toArray();

        $query = ActivityLog::with('user')
            ->where(function ($q) use ($coffret, $equipmentIds) {
                $q->where(function ($sub) use ($coffret) {
                    $sub->where('entity_type', Coffret::class)
                        ->where('entity_id', $coffret->id);
                });

                if (!empty($equipmentIds)) {
                    $q->orWhere(function ($sub) use ($equipmentIds) {
                        $sub->where('entity_type', \App\Models\Equipement::class)
                            ->whereIn('entity_id', $equipmentIds);
                    });
                }
            })
            ->orderByDesc('created_at');

        return ApiResponse::success($query->paginate($request->integer('per_page', 15)));
    }
}
