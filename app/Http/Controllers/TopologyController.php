<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Liaison;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TopologyController extends Controller
{
    #[OA\Get(
        path: '/topology',
        summary: 'Topologie du réseau',
        tags: ['Topologie'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'classification', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'coffret_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'zone_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Topologie du réseau (noeuds et liens)'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $nodesQuery = Equipement::with('coffret');
        $edgesQuery = Liaison::query();

        if ($request->has('classification')) {
            $nodesQuery->where('classification', $request->classification);
        }

        if ($request->has('coffret_id')) {
            $nodesQuery->where('coffret_id', $request->coffret_id);
        }

        if ($request->has('zone_id')) {
            $nodesQuery->whereHas('coffret', function ($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        $nodes = $nodesQuery->get();
        $nodeIds = $nodes->pluck('id')->toArray();

        $edges = $edgesQuery->whereIn('from', $nodeIds)
            ->whereIn('to', $nodeIds)
            ->get();

        return ApiResponse::success([
            'nodes' => $nodes,
            'edges' => $edges,
        ]);
    }
}
