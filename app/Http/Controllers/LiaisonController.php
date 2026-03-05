<?php

namespace App\Http\Controllers;

use App\Models\Liaison;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreLiaisonRequest;
use App\Http\Requests\UpdateLiaisonRequest;
use Illuminate\Http\Request;

class LiaisonController extends Controller
{
    public function index(Request $request)
    {
        $query = Liaison::with('fromEquipement', 'toEquipement');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('label', 'like', '%' . $request->search . '%');
        }

        $liaisons = $query->orderBy('label')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($liaisons);
    }

    public function store(StoreLiaisonRequest $request)
    {
        $liaison = Liaison::create($request->validated());

        return ApiResponse::created($liaison, 'Liaison créée avec succès.');
    }

    public function show(Liaison $liaison)
    {
        return ApiResponse::success($liaison->load('fromEquipement', 'toEquipement'));
    }

    public function update(UpdateLiaisonRequest $request, Liaison $liaison)
    {
        $liaison->update($request->validated());

        return ApiResponse::success($liaison, 'Liaison mise à jour avec succès.');
    }

    public function destroy(Liaison $liaison)
    {
        $liaison->delete();

        return ApiResponse::success(null, 'Liaison supprimée avec succès.');
    }
}
