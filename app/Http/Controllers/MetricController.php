<?php

namespace App\Http\Controllers;

use App\Models\Metric;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMetricRequest;
use App\Http\Requests\UpdateMetricRequest;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    public function index(Request $request)
    {
        $query = Metric::with('coffret');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $metrics = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($metrics);
    }

    public function store(StoreMetricRequest $request)
    {
        $metric = Metric::create($request->validated());

        return ApiResponse::created($metric, 'Metric créée avec succès.');
    }

    public function show(Metric $metric)
    {
        return ApiResponse::success($metric->load('coffret'));
    }

    public function update(UpdateMetricRequest $request, Metric $metric)
    {
        $metric->update($request->validated());

        return ApiResponse::success($metric, 'Metric mise à jour avec succès.');
    }

    public function destroy(Metric $metric)
    {
        $metric->delete();

        return ApiResponse::success(null, 'Metric supprimée avec succès.');
    }
}
