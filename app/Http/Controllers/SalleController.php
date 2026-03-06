<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSalleRequest;
use App\Http\Requests\UpdateSalleRequest;
use Illuminate\Http\Request;

class SalleController extends Controller
{
    public function index(Request $request)
    {
        $query = Salle::with('batiment');

        if ($request->has('batiment_id')) {
            $query->where('batiment_id', $request->batiment_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $salles = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($salles);
    }

    public function store(StoreSalleRequest $request)
    {
        $salle = Salle::create($request->validated());

        return ApiResponse::created($salle, 'Salle créée avec succès.');
    }

    public function show(Salle $salle)
    {
        return ApiResponse::success($salle->load('batiment', 'coffrets'));
    }

    public function update(UpdateSalleRequest $request, Salle $salle)
    {
        $salle->update($request->validated());

        return ApiResponse::success($salle, 'Salle mise à jour avec succès.');
    }

    public function destroy(Salle $salle)
    {
        $salle->delete();

        return ApiResponse::success(null, 'Salle supprimée avec succès.');
    }
}
