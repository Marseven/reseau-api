<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabelRequest;
use App\Models\Coffret;
use App\Models\Equipement;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use OpenApi\Attributes as OA;

class LabelController extends Controller
{
    #[OA\Post(
        path: '/labels/coffrets',
        summary: 'Générer des étiquettes PDF pour des coffrets',
        tags: ['Labels'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ids', 'format'],
                properties: [
                    new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3]),
                    new OA\Property(property: 'format', type: 'string', enum: ['small', 'medium', 'large'], example: 'medium'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function coffrets(LabelRequest $request)
    {
        $coffrets = Coffret::with('zone.site', 'salle')
            ->whereIn('id', $request->ids)
            ->get();

        $items = $coffrets->map(fn ($c) => [
            'qr' => $this->generateQrBase64($this->buildUrl('coffret', $c->qr_token)),
            'code' => $c->code,
            'name' => $c->name,
            'type' => $c->type,
            'zone' => $c->zone?->name,
            'site' => $c->zone?->site?->name,
            'salle' => $c->salle?->name,
        ]);

        $pdf = Pdf::loadView('exports.labels', [
            'items' => $items,
            'format' => $request->format,
            'entityType' => 'Coffret',
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ])->setPaper('a4');

        return $pdf->download('etiquettes-coffrets.pdf');
    }

    #[OA\Post(
        path: '/labels/equipements',
        summary: 'Générer des étiquettes PDF pour des équipements',
        tags: ['Labels'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ids', 'format'],
                properties: [
                    new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3]),
                    new OA\Property(property: 'format', type: 'string', enum: ['small', 'medium', 'large'], example: 'medium'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Fichier PDF'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function equipements(LabelRequest $request)
    {
        $equipements = Equipement::with('coffret.zone.site')
            ->whereIn('id', $request->ids)
            ->get();

        $items = $equipements->map(fn ($e) => [
            'qr' => $this->generateQrBase64($this->buildUrl('equipement', $e->qr_token)),
            'code' => $e->equipement_code,
            'name' => $e->name,
            'type' => $e->type,
            'zone' => $e->coffret?->zone?->name,
            'site' => $e->coffret?->zone?->site?->name,
            'salle' => $e->coffret?->name,
        ]);

        $pdf = Pdf::loadView('exports.labels', [
            'items' => $items,
            'format' => $request->format,
            'entityType' => 'Équipement',
            'generatedAt' => now()->format('d/m/Y H:i'),
            'generatedBy' => auth()->user()?->name,
        ])->setPaper('a4');

        return $pdf->download('etiquettes-equipements.pdf');
    }

    private function generateQrBase64(string $content): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 5,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($content);
    }

    private function buildUrl(string $type, ?string $qrToken): string
    {
        $baseUrl = config('app.url');
        return "{$baseUrl}/qr/{$type}/{$qrToken}";
    }
}
