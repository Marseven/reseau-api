<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        return ApiResponse::success(Setting::allAsKeyValue());
    }

    public function update(UpdateSettingsRequest $request)
    {
        Setting::bulkUpdate($request->validated());

        return ApiResponse::success(Setting::allAsKeyValue(), 'Paramètres mis à jour.');
    }
}
