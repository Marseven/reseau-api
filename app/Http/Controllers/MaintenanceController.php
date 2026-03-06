<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with(['technicien', 'equipement', 'coffret', 'site']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('technicien_id')) {
            $query->where('technicien_id', $request->technicien_id);
        }

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $maintenances = $query->orderByDesc('scheduled_date')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($maintenances);
    }

    public function store(StoreMaintenanceRequest $request)
    {
        $maintenance = Maintenance::create($request->validated());

        return ApiResponse::created($maintenance, 'Maintenance créée avec succès.');
    }

    public function show(Maintenance $maintenance)
    {
        return ApiResponse::success($maintenance->load('technicien', 'validator', 'equipement', 'coffret', 'site'));
    }

    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance)
    {
        $maintenance->update($request->validated());

        return ApiResponse::success($maintenance, 'Maintenance mise à jour avec succès.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return ApiResponse::success(null, 'Maintenance supprimée avec succès.');
    }
}
