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
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChangeRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LoginAuditController;
use App\Http\Controllers\TopologyController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\LabelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Reseau Inventaire App API',
        'version' => '1.0.0',
        'description' => 'API du Reseau Inventaire - Eramet Comilog',
    ]);
});

Route::prefix('v1')->group(function () {

    // Health check (public, no auth)
    Route::get('/health', HealthController::class);

    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

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
        Route::middleware('role:administrator,directeur,technicien,user,prestataire')->group(function () {
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
            Route::get('/batiments', [BatimentController::class, 'index']);
            Route::get('/batiments/{batiment}', [BatimentController::class, 'show']);
            Route::get('/salles', [SalleController::class, 'index']);
            Route::get('/salles/{salle}', [SalleController::class, 'show']);
            Route::get('/vlans', [VlanController::class, 'index']);
            Route::get('/vlans/{vlan}', [VlanController::class, 'show']);
            Route::get('/maintenances', [MaintenanceController::class, 'index']);
            Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show']);
            Route::get('/change-requests', [ChangeRequestController::class, 'index']);
            Route::get('/change-requests/{changeRequest}', [ChangeRequestController::class, 'show']);

            // QR code resolution
            Route::get('/qr/coffret/{qrToken}', [QrCodeController::class, 'showCoffret']);
            Route::get('/qr/equipement/{qrToken}', [QrCodeController::class, 'showEquipement']);

            // Notifications
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

            // Coffret history (accessible to all authenticated users)
            Route::get('/coffrets/{coffret}/history', [ActivityLogController::class, 'coffretHistory']);

            // Login audit - own history
            Route::get('/login-audits/me', [LoginAuditController::class, 'myHistory']);

            // Network topology
            Route::get('/topology', [TopologyController::class, 'index']);

            // Analytics (admin + directeur)
            Route::middleware('role:administrator,directeur')->prefix('analytics')->group(function () {
                Route::get('/equipements-by-type', [AnalyticsController::class, 'equipementsByType']);
                Route::get('/equipements-by-classification', [AnalyticsController::class, 'equipementsByClassification']);
                Route::get('/equipements-by-status', [AnalyticsController::class, 'equipementsByStatus']);
                Route::get('/equipements-by-vendor', [AnalyticsController::class, 'equipementsByVendor']);
                Route::get('/maintenance-trends', [AnalyticsController::class, 'maintenanceTrends']);
                Route::get('/port-utilization', [AnalyticsController::class, 'portUtilization']);
                Route::get('/sites-summary', [AnalyticsController::class, 'sitesSummary']);
            });
        });

        // WRITE access for admin + directeur
        Route::middleware(['role:administrator,directeur', 'throttle:write'])->group(function () {
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
            Route::delete('/coffrets/{coffret}/photo', [CoffretController::class, 'deletePhoto']);

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

            Route::post('/batiments', [BatimentController::class, 'store']);
            Route::put('/batiments/{batiment}', [BatimentController::class, 'update']);
            Route::delete('/batiments/{batiment}', [BatimentController::class, 'destroy']);

            Route::post('/salles', [SalleController::class, 'store']);
            Route::put('/salles/{salle}', [SalleController::class, 'update']);
            Route::delete('/salles/{salle}', [SalleController::class, 'destroy']);

            Route::post('/vlans', [VlanController::class, 'store']);
            Route::put('/vlans/{vlan}', [VlanController::class, 'update']);
            Route::delete('/vlans/{vlan}', [VlanController::class, 'destroy']);

            Route::post('/maintenances', [MaintenanceController::class, 'store']);
            Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);
            Route::delete('/maintenances/{maintenance}', [MaintenanceController::class, 'destroy']);

            // Activity logs (admin + directeur only)
            Route::get('/activity-logs', [ActivityLogController::class, 'index']);

            // CSV Imports
            Route::prefix('imports')->group(function () {
                Route::post('/coffrets/csv', [ImportController::class, 'importCoffrets']);
                Route::post('/equipements/csv', [ImportController::class, 'importEquipements']);
                Route::post('/ports/csv', [ImportController::class, 'importPorts']);
                Route::post('/liaisons/csv', [ImportController::class, 'importLiaisons']);
                Route::get('/coffrets/template', [ImportController::class, 'templateCoffrets']);
                Route::get('/equipements/template', [ImportController::class, 'templateEquipements']);
                Route::get('/ports/template', [ImportController::class, 'templatePorts']);
                Route::get('/liaisons/template', [ImportController::class, 'templateLiaisons']);
            });

            // Exports & Reports (rate-limited)
            Route::middleware('throttle:export')->group(function () {
                // CSV Exports
                Route::get('/exports/equipements/csv', [ExportController::class, 'exportEquipementsCsv']);
                Route::get('/exports/coffrets/csv', [ExportController::class, 'exportCoffretsCsv']);
                Route::get('/exports/ports/csv', [ExportController::class, 'exportPortsCsv']);
                Route::get('/exports/liaisons/csv', [ExportController::class, 'exportLiaisonsCsv']);
                Route::get('/exports/activity-logs/csv', [ExportController::class, 'exportActivityLogsCsv']);

                // PDF Export
                Route::get('/exports/architecture/pdf', [ExportController::class, 'exportArchitecturePdf']);

                // Reports
                Route::get('/reports/summary', [ReportController::class, 'summary']);
                Route::get('/reports/network-status/pdf', [ReportController::class, 'networkStatus']);
                Route::get('/reports/modifications/pdf', [ReportController::class, 'modifications']);
                Route::get('/reports/interventions/pdf', [ReportController::class, 'interventions']);
                Route::get('/reports/site/{site}/architecture/pdf', [ReportController::class, 'siteArchitecturePdf']);

                // Labels (PDF)
                Route::post('/labels/coffrets', [LabelController::class, 'coffrets']);
                Route::post('/labels/equipements', [LabelController::class, 'equipements']);
            });
        });

        // Change requests - create/delete (technicien, directeur, admin)
        Route::middleware('role:administrator,directeur,technicien')->group(function () {
            Route::post('/change-requests', [ChangeRequestController::class, 'store']);
            Route::delete('/change-requests/{changeRequest}', [ChangeRequestController::class, 'destroy']);
        });

        // USER management + change request review - admin only
        Route::middleware('role:administrator')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::put('/change-requests/{changeRequest}/review', [ChangeRequestController::class, 'review']);
            Route::get('/login-audits', [LoginAuditController::class, 'index']);
        });
    });
});
