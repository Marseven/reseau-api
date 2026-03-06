<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreBatimentRequest;
use App\Http\Requests\UpdateBatimentRequest;
use Illuminate\Http\Request;

class BatimentController extends Controller
{
    public function index(Request $request)
    {
        $query = Batiment::with('zone');

        if ($request->has('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $batiments = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($batiments);
    }

    public function store(StoreBatimentRequest $request)
    {
        $batiment = Batiment::create($request->validated());

        return ApiResponse::created($batiment, 'Bâtiment créé avec succès.');
    }

    public function show(Batiment $batiment)
    {
        return ApiResponse::success($batiment->load('zone', 'salles'));
    }

    public function update(UpdateBatimentRequest $request, Batiment $batiment)
    {
        $batiment->update($request->validated());

        return ApiResponse::success($batiment, 'Bâtiment mis à jour avec succès.');
    }

    public function destroy(Batiment $batiment)
    {
        $batiment->delete();

        return ApiResponse::success(null, 'Bâtiment supprimé avec succès.');
    }
}
