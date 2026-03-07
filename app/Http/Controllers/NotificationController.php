<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
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

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Vous ne pouvez modifier que vos propres notifications.');
        }

        $notification->markAsRead();

        return ApiResponse::success($notification);
    }

    public function markAllAsRead(Request $request)
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Vous ne pouvez supprimer que vos propres notifications.');
        }

        $notification->delete();

        return ApiResponse::success(null, 'Notification supprimée.');
    }
}
