<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $query = Zone::with('site');

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

    public function store(StoreZoneRequest $request)
    {
        $zone = Zone::create($request->validated());

        return ApiResponse::created($zone, 'Zone créée avec succès.');
    }

    public function show(Zone $zone)
    {
        return ApiResponse::success($zone->load('site', 'coffrets'));
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());

        return ApiResponse::success($zone, 'Zone mise à jour avec succès.');
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();

        return ApiResponse::success(null, 'Zone supprimée avec succès.');
    }
}
