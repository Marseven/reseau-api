<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Coffret;
use App\Models\Equipement;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class QrCodeController extends Controller
{
    #[OA\Get(
        path: '/qr/coffret/{qrToken}',
        summary: 'Résoudre un QR code coffret',
        tags: ['QR Codes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'qrToken', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du coffret'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'QR code non trouvé'),
        ]
    )]
    public function showCoffret(string $qrToken): JsonResponse
    {
        $coffret = Coffret::with('equipments.ports', 'metrics', 'zone.site', 'salle.batiment')
            ->where('qr_token', $qrToken)
            ->firstOrFail();

        return ApiResponse::success($coffret);
    }

    #[OA\Get(
        path: '/qr/equipement/{qrToken}',
        summary: 'Résoudre un QR code équipement',
        tags: ['QR Codes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'qrToken', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de l\'équipement'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 404, description: 'QR code non trouvé'),
        ]
    )]
    public function showEquipement(string $qrToken): JsonResponse
    {
        $equipement = Equipement::with('ports', 'coffret.zone.site', 'coffret.salle.batiment')
            ->where('qr_token', $qrToken)
            ->firstOrFail();

        return ApiResponse::success($equipement);
    }
}
