<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: '/notifications',
        summary: 'Lister les notifications',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'unread_only', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste paginée des notifications'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $unreadCount = Notification::forUser($request->user()->id)->unread()->count();
        $notifications = $query->paginate($request->integer('per_page', 15));

        return ApiResponse::success([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    #[OA\Put(
        path: '/notifications/{id}/read',
        summary: 'Marquer une notification comme lue',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification marquée comme lue'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Notification non trouvée'),
        ]
    )]
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Vous ne pouvez modifier que vos propres notifications.');
        }

        $notification->markAsRead();

        return ApiResponse::success($notification);
    }

    #[OA\Put(
        path: '/notifications/read-all',
        summary: 'Marquer toutes les notifications comme lues',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Toutes les notifications marquées comme lues'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function markAllAsRead(Request $request)
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, 'Toutes les notifications ont été marquées comme lues.');
    }

    #[OA\Delete(
        path: '/notifications/{id}',
        summary: 'Supprimer une notification',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification supprimée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Notification non trouvée'),
        ]
    )]
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Vous ne pouvez supprimer que vos propres notifications.');
        }

        $notification->delete();

        return ApiResponse::success(null, 'Notification supprimée.');
    }
}
