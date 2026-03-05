<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSystemRequest;
use App\Http\Requests\UpdateSystemRequest;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function index(Request $request)
    {
        $query = System::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $systems = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($systems);
    }

    public function store(StoreSystemRequest $request)
    {
        $system = System::create($request->validated());

        return ApiResponse::created($system, 'Système créé avec succès.');
    }

    public function show(System $system)
    {
        return ApiResponse::success($system);
    }

    public function update(UpdateSystemRequest $request, System $system)
    {
        $system->update($request->validated());

        return ApiResponse::success($system, 'Système mis à jour avec succès.');
    }

    public function destroy(System $system)
    {
        $system->delete();

        return ApiResponse::success(null, 'Système supprimé avec succès.');
    }
}
