<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    public static function created($data = null, string $message = 'Ressource créée avec succès.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function error(string $message = 'Erreur.', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    public static function unauthorized(string $message = 'Non authentifié.'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'Non autorisé.'): JsonResponse
    {
        return self::error($message, 403);
    }

    public static function notFound(string $message = 'Ressource introuvable.'): JsonResponse
    {
        return self::error($message, 404);
    }
}
