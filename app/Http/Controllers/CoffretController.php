<?php

namespace App\Http\Controllers;

use App\Models\Coffret;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCoffretRequest;
use App\Http\Requests\UpdateCoffretRequest;
use Illuminate\Http\Request;

class CoffretController extends Controller
{
    public function index(Request $request)
    {
        $query = Coffret::with('equipments', 'metrics');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $coffrets = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($coffrets);
    }

    public function store(StoreCoffretRequest $request)
    {
        $coffret = Coffret::create($request->validated());

        return ApiResponse::created($coffret, 'Coffret créé avec succès.');
    }

    public function show(Coffret $coffret)
    {
        return ApiResponse::success($coffret->load('equipments', 'metrics'));
    }

    public function update(UpdateCoffretRequest $request, Coffret $coffret)
    {
        $coffret->update($request->validated());

        return ApiResponse::success($coffret, 'Coffret mis à jour avec succès.');
    }

    public function destroy(Coffret $coffret)
    {
        $coffret->delete();

        return ApiResponse::success(null, 'Coffret supprimé avec succès.');
    }
}
