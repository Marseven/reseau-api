<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Site::with('zones');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sites = $query->orderBy('name')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($sites);
    }

    public function store(StoreSiteRequest $request)
    {
        $site = Site::create($request->validated());

        return ApiResponse::created($site, 'Site créé avec succès.');
    }

    public function show(Site $site)
    {
        return ApiResponse::success($site->load('zones'));
    }

    public function update(UpdateSiteRequest $request, Site $site)
    {
        $site->update($request->validated());

        return ApiResponse::success($site, 'Site mis à jour avec succès.');
    }

    public function destroy(Site $site)
    {
        $site->delete();

        return ApiResponse::success(null, 'Site supprimé avec succès.');
    }
}
