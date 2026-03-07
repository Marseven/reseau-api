<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Coffret;
use App\Models\Equipement;
use Illuminate\Http\JsonResponse;

class QrCodeController extends Controller
{
    public function showCoffret(string $qrToken): JsonResponse
    {
        $coffret = Coffret::with('equipments.ports', 'metrics', 'zone.site', 'salle.batiment')
            ->where('qr_token', $qrToken)
            ->firstOrFail();

        return ApiResponse::success($coffret);
    }

    public function showEquipement(string $qrToken): JsonResponse
    {
        $equipement = Equipement::with('ports', 'coffret.zone.site', 'coffret.salle.batiment')
            ->where('qr_token', $qrToken)
            ->firstOrFail();

        return ApiResponse::success($equipement);
    }
}
