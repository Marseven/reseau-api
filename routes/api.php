<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\CoffretController;
use App\Http\Controllers\EquipementsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\MetricController;
use App\Http\Controllers\LiaisonController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Reseau Inventaire App API',
        'version' => '1.0.0',
        'description' => 'API du Reseau Inventaire - Eramet Comilog',
    ]);
});

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login']);

    // 2FA challenge (public, rate-limited)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/2fa/challenge', [AuthController::class, 'verifyTwoFactorLogin']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // 2FA management (authenticated)
        Route::post('/auth/2fa/setup', [TwoFactorController::class, 'setup']);
        Route::post('/auth/2fa/verify', [TwoFactorController::class, 'verify']);
        Route::post('/auth/2fa/disable', [TwoFactorController::class, 'disable']);
        Route::get('/auth/2fa/recovery-codes', [TwoFactorController::class, 'recoveryCodes']);
        Route::post('/auth/2fa/recovery-codes/regenerate', [TwoFactorController::class, 'regenerateRecoveryCodes']);

        // READ access for all authenticated roles
        Route::middleware('role:administrator,directeur,technicien,user')->group(function () {
            Route::get('/sites', [SiteController::class, 'index']);
            Route::get('/sites/{site}', [SiteController::class, 'show']);
            Route::get('/zones', [ZoneController::class, 'index']);
            Route::get('/zones/{zone}', [ZoneController::class, 'show']);
            Route::get('/coffrets', [CoffretController::class, 'index']);
            Route::get('/coffrets/{coffret}', [CoffretController::class, 'show']);
            Route::get('/equipements', [EquipementsController::class, 'index']);
            Route::get('/equipements/{equipement}', [EquipementsController::class, 'show']);
            Route::get('/ports', [PortController::class, 'index']);
            Route::get('/ports/{port}', [PortController::class, 'show']);
            Route::get('/liaisons', [LiaisonController::class, 'index']);
            Route::get('/liaisons/{liaison}', [LiaisonController::class, 'show']);
            Route::get('/metrics', [MetricController::class, 'index']);
            Route::get('/metrics/{metric}', [MetricController::class, 'show']);
            Route::get('/systems', [SystemController::class, 'index']);
            Route::get('/systems/{system}', [SystemController::class, 'show']);
        });

        // WRITE access for admin + directeur
        Route::middleware('role:administrator,directeur')->group(function () {
            // Statistiques
            Route::get('/stats/global', [StatistiqueController::class, 'globalStats']);
            Route::get('/stats/systems-by-type', [StatistiqueController::class, 'systemsByType']);
            Route::get('/stats/equipements-by-coffret', [StatistiqueController::class, 'equipementsByCoffret']);
            Route::get('/stats/ports-by-vlan', [StatistiqueController::class, 'portsByVlan']);

            // CUD operations on resources
            Route::post('/sites', [SiteController::class, 'store']);
            Route::put('/sites/{site}', [SiteController::class, 'update']);
            Route::delete('/sites/{site}', [SiteController::class, 'destroy']);

            Route::post('/zones', [ZoneController::class, 'store']);
            Route::put('/zones/{zone}', [ZoneController::class, 'update']);
            Route::delete('/zones/{zone}', [ZoneController::class, 'destroy']);

            Route::post('/coffrets', [CoffretController::class, 'store']);
            Route::put('/coffrets/{coffret}', [CoffretController::class, 'update']);
            Route::delete('/coffrets/{coffret}', [CoffretController::class, 'destroy']);

            Route::post('/equipements', [EquipementsController::class, 'store']);
            Route::put('/equipements/{equipement}', [EquipementsController::class, 'update']);
            Route::delete('/equipements/{equipement}', [EquipementsController::class, 'destroy']);

            Route::post('/ports', [PortController::class, 'store']);
            Route::put('/ports/{port}', [PortController::class, 'update']);
            Route::delete('/ports/{port}', [PortController::class, 'destroy']);

            Route::post('/liaisons', [LiaisonController::class, 'store']);
            Route::put('/liaisons/{liaison}', [LiaisonController::class, 'update']);
            Route::delete('/liaisons/{liaison}', [LiaisonController::class, 'destroy']);

            Route::post('/metrics', [MetricController::class, 'store']);
            Route::put('/metrics/{metric}', [MetricController::class, 'update']);
            Route::delete('/metrics/{metric}', [MetricController::class, 'destroy']);

            Route::post('/systems', [SystemController::class, 'store']);
            Route::put('/systems/{system}', [SystemController::class, 'update']);
            Route::delete('/systems/{system}', [SystemController::class, 'destroy']);
        });

        // USER management - admin only
        Route::middleware('role:administrator')->group(function () {
            Route::apiResource('users', UserController::class);
        });
    });
});
