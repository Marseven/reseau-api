<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\CoffretController;
use App\Http\Controllers\EquipementsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\MetricController;
use App\Http\Controllers\LiaisonController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Reseau Inventaire App API',
        'version' => '1.0.0',
        'description' => 'API du Reseau Inventaire réalisé par JOBS-Conseil',
    ]);
});

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::middleware('role:administrator,directeur')->group(function () {
            // Statistiques
            Route::get('/stats/global', [StatistiqueController::class, 'globalStats']);
            Route::get('/stats/systems-by-type', [StatistiqueController::class, 'systemsByType']);
            Route::get('/stats/equipements-by-coffret', [StatistiqueController::class, 'equipementsByCoffret']);
            Route::get('/stats/ports-by-vlan', [StatistiqueController::class, 'portsByVlan']);

            // Ressources CRUD
            Route::apiResource('coffrets', CoffretController::class);
            Route::apiResource('equipements', EquipementsController::class);
            Route::apiResource('ports', PortController::class);
            Route::apiResource('metrics', MetricController::class);
            Route::apiResource('liaisons', LiaisonController::class);
            Route::apiResource('systems', SystemController::class);
        });
    });
});
