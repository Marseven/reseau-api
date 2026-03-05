<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Helpers\ApiResponse;
use App\Http\Requests\StorePortRequest;
use App\Http\Requests\UpdatePortRequest;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::with('connectedEquipment');

        if ($request->has('vlan')) {
            $query->where('vlan', $request->vlan);
        }

        if ($request->has('search')) {
            $query->where('port_label', 'like', '%' . $request->search . '%');
        }

        $ports = $query->orderBy('port_label')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($ports);
    }

    public function store(StorePortRequest $request)
    {
        $port = Port::create($request->validated());

        return ApiResponse::created($port, 'Port créé avec succès.');
    }

    public function show(Port $port)
    {
        return ApiResponse::success($port->load('connectedEquipment'));
    }

    public function update(UpdatePortRequest $request, Port $port)
    {
        $port->update($request->validated());

        return ApiResponse::success($port, 'Port mis à jour avec succès.');
    }

    public function destroy(Port $port)
    {
        $port->delete();

        return ApiResponse::success(null, 'Port supprimé avec succès.');
    }
}
