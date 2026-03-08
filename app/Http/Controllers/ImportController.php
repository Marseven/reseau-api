<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ImportCsvRequest;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use App\Models\Port;
use App\Services\CsvImportService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function __construct(private CsvImportService $csvImportService)
    {
    }

    // ── Import endpoints ──────────────────────────────────────────

    #[OA\Post(
        path: '/imports/coffrets/csv',
        summary: 'Importer des coffrets depuis un fichier CSV',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'Fichier CSV'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Import réussi'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function importCoffrets(ImportCsvRequest $request)
    {
        $result = $this->csvImportService->import(
            file: $request->file('file'),
            modelClass: Coffret::class,
            columnMap: [
                'Code' => 'code',
                'Nom' => 'name',
                'Pièce' => 'piece',
                'Type' => 'type',
                'Status' => 'status',
                'Longitude' => 'long',
                'Latitude' => 'lat',
            ],
            rules: [
                'code' => 'required|string|max:50',
                'name' => 'required|string|max:255',
                'piece' => 'required|string|max:255',
                'type' => 'nullable|string|max:100',
                'status' => 'nullable|string|in:active,inactive,maintenance',
                'long' => 'nullable|numeric',
                'lat' => 'nullable|numeric',
            ],
            uniqueKey: 'code',
        );

        return ApiResponse::success($result, 'Import coffrets terminé.');
    }

    #[OA\Post(
        path: '/imports/equipements/csv',
        summary: 'Importer des équipements depuis un fichier CSV',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'Fichier CSV'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Import réussi'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function importEquipements(ImportCsvRequest $request)
    {
        $result = $this->csvImportService->import(
            file: $request->file('file'),
            modelClass: Equipement::class,
            columnMap: [
                'Code' => 'equipement_code',
                'Nom' => 'name',
                'Type' => 'type',
                'Classification' => 'classification',
                'IP' => 'ip_address',
                'VLAN' => 'vlan',
                'Status' => 'status',
                'Fabricant' => 'fabricant',
                'Modèle' => 'modele',
                'Numéro de série' => 'serial_number',
                'Direction' => 'direction_in_out',
                'Description' => 'description',
                'Coffret ID' => 'coffret_id',
            ],
            rules: [
                'equipement_code' => 'required|string|max:50',
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'classification' => 'nullable|string|in:IT,OT',
                'ip_address' => 'nullable|string|max:45',
                'vlan' => 'nullable|string|max:50',
                'status' => 'required|string|in:active,inactive,maintenance',
                'fabricant' => 'nullable|string|max:255',
                'modele' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'direction_in_out' => 'nullable|string|in:in,out,both',
                'description' => 'nullable|string',
                'coffret_id' => 'required|integer|exists:coffrets,id',
            ],
            uniqueKey: 'equipement_code',
        );

        return ApiResponse::success($result, 'Import équipements terminé.');
    }

    #[OA\Post(
        path: '/imports/ports/csv',
        summary: 'Importer des ports depuis un fichier CSV',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'Fichier CSV'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Import réussi'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function importPorts(ImportCsvRequest $request)
    {
        $result = $this->csvImportService->import(
            file: $request->file('file'),
            modelClass: Port::class,
            columnMap: [
                'Label' => 'port_label',
                'Device' => 'device_name',
                'Type' => 'port_type',
                'VLAN' => 'vlan',
                'Speed' => 'speed',
                'PoE' => 'poe_enabled',
                'Status' => 'status',
                'Description' => 'description',
            ],
            rules: [
                'port_label' => 'required|string|max:100',
                'device_name' => 'nullable|string|max:255',
                'port_type' => 'nullable|string|max:50',
                'vlan' => 'nullable|string|max:50',
                'speed' => 'nullable|string|max:50',
                'poe_enabled' => 'nullable|in:Oui,Non,1,0,true,false',
                'status' => 'nullable|string|in:active,inactive,maintenance',
                'description' => 'nullable|string',
            ],
            uniqueKey: 'port_label',
        );

        // Normalize poe_enabled values
        return ApiResponse::success($result, 'Import ports terminé.');
    }

    #[OA\Post(
        path: '/imports/liaisons/csv',
        summary: 'Importer des liaisons depuis un fichier CSV',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'Fichier CSV'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Import réussi'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function importLiaisons(ImportCsvRequest $request)
    {
        $result = $this->csvImportService->import(
            file: $request->file('file'),
            modelClass: Liaison::class,
            columnMap: [
                'Label' => 'label',
                'De (ID)' => 'from',
                'Vers (ID)' => 'to',
                'Média' => 'media',
                'Longueur' => 'length',
                'Status' => 'status',
            ],
            rules: [
                'label' => 'required|string|max:255',
                'from' => 'required|integer|exists:equipements,id',
                'to' => 'required|integer|exists:equipements,id',
                'media' => 'required|string|max:100',
                'length' => 'nullable|numeric',
                'status' => 'nullable|boolean',
            ],
            uniqueKey: 'label',
        );

        return ApiResponse::success($result, 'Import liaisons terminé.');
    }

    // ── Template download endpoints ───────────────────────────────

    #[OA\Get(
        path: '/imports/coffrets/template',
        summary: 'Télécharger le template CSV pour les coffrets',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV template'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function templateCoffrets(): StreamedResponse
    {
        return $this->streamTemplate('template-coffrets.csv', [
            'Code', 'Nom', 'Pièce', 'Type', 'Status', 'Longitude', 'Latitude',
        ]);
    }

    #[OA\Get(
        path: '/imports/equipements/template',
        summary: 'Télécharger le template CSV pour les équipements',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV template'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function templateEquipements(): StreamedResponse
    {
        return $this->streamTemplate('template-equipements.csv', [
            'Code', 'Nom', 'Type', 'Classification', 'IP', 'VLAN', 'Status',
            'Fabricant', 'Modèle', 'Numéro de série', 'Direction', 'Description', 'Coffret ID',
        ]);
    }

    #[OA\Get(
        path: '/imports/ports/template',
        summary: 'Télécharger le template CSV pour les ports',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV template'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function templatePorts(): StreamedResponse
    {
        return $this->streamTemplate('template-ports.csv', [
            'Label', 'Device', 'Type', 'VLAN', 'Speed', 'PoE', 'Status', 'Description',
        ]);
    }

    #[OA\Get(
        path: '/imports/liaisons/template',
        summary: 'Télécharger le template CSV pour les liaisons',
        tags: ['Imports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Fichier CSV template'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function templateLiaisons(): StreamedResponse
    {
        return $this->streamTemplate('template-liaisons.csv', [
            'Label', 'De (ID)', 'Vers (ID)', 'Média', 'Longueur', 'Status',
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────

    private function streamTemplate(string $filename, array $headers): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($handle, $headers, ';');
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
