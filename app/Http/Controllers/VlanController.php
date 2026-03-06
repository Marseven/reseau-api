<?php

namespace App\Http\Controllers;

use App\Models\Vlan;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
use Illuminate\Http\Request;

class VlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Vlan::with('site');

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vlans = $query->orderBy('vlan_id')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($vlans);
    }

    public function store(StoreVlanRequest $request)
    {
        $vlan = Vlan::create($request->validated());

        return ApiResponse::created($vlan, 'VLAN créé avec succès.');
    }

    public function show(Vlan $vlan)
    {
        return ApiResponse::success($vlan->load('site'));
    }

    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {
        $vlan->update($request->validated());

        return ApiResponse::success($vlan, 'VLAN mis à jour avec succès.');
    }

    public function destroy(Vlan $vlan)
    {
        $vlan->delete();

        return ApiResponse::success(null, 'VLAN supprimé avec succès.');
    }
}
