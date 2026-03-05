<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEquipementRequest;
use App\Http\Requests\UpdateEquipementRequest;
use Illuminate\Http\Request;

class EquipementsController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipement::with('coffret');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $equipements = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($equipements);
    }

    public function store(StoreEquipementRequest $request)
    {
        $equipement = Equipement::create($request->validated());

        return ApiResponse::created($equipement, 'Équipement créé avec succès.');
    }

    public function show(Equipement $equipement)
    {
        return ApiResponse::success($equipement->load('coffret', 'ports'));
    }

    public function update(UpdateEquipementRequest $request, Equipement $equipement)
    {
        $equipement->update($request->validated());

        return ApiResponse::success($equipement, 'Équipement mis à jour avec succès.');
    }

    public function destroy(Equipement $equipement)
    {
        $equipement->delete();

        return ApiResponse::success(null, 'Équipement supprimé avec succès.');
    }
}
