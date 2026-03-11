<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Port;
use App\Models\Liaison;
use App\Models\System;
use App\Models\Maintenance;
use App\Models\Site;
use App\Models\Zone;
use App\Models\Batiment;
use App\Models\Salle;
use App\Models\Vlan;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    private const RESOURCE_MAP = [
        'coffrets' => Coffret::class,
        'equipements' => Equipement::class,
        'ports' => Port::class,
        'liaisons' => Liaison::class,
        'systems' => System::class,
        'maintenances' => Maintenance::class,
        'sites' => Site::class,
        'zones' => Zone::class,
        'batiments' => Batiment::class,
        'salles' => Salle::class,
        'vlans' => Vlan::class,
    ];

    private function resolveModel(string $resource): ?string
    {
        return self::RESOURCE_MAP[$resource] ?? null;
    }

    public function index(Request $request, string $resource)
    {
        $modelClass = $this->resolveModel($resource);

        if (!$modelClass) {
            return ApiResponse::error("Ressource '$resource' non reconnue.", 404);
        }

        $query = $modelClass::onlyTrashed();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                if (in_array('code', $q->getModel()->getFillable())) {
                    $q->orWhere('code', 'like', "%{$search}%");
                }
            });
        }

        $perPage = $request->integer('per_page', 20);

        return ApiResponse::success($query->latest('deleted_at')->paginate($perPage));
    }

    public function restore(string $resource, int $id)
    {
        $modelClass = $this->resolveModel($resource);

        if (!$modelClass) {
            return ApiResponse::error("Ressource '$resource' non reconnue.", 404);
        }

        $item = $modelClass::onlyTrashed()->find($id);

        if (!$item) {
            return ApiResponse::error("Élément non trouvé dans les archives.", 404);
        }

        $item->restore();

        return ApiResponse::success($item, 'Élément restauré avec succès.');
    }

    public function forceDelete(string $resource, int $id)
    {
        $modelClass = $this->resolveModel($resource);

        if (!$modelClass) {
            return ApiResponse::error("Ressource '$resource' non reconnue.", 404);
        }

        $item = $modelClass::onlyTrashed()->find($id);

        if (!$item) {
            return ApiResponse::error("Élément non trouvé dans les archives.", 404);
        }

        $item->forceDelete();

        return ApiResponse::success(null, 'Élément supprimé définitivement.');
    }
}
