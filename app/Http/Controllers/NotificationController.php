<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display notification history page.
     */
    public function history()
    {
        return view('notifications.index');
    }

    /**
     * Return the latest 30 notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (DatabaseNotification $notification) =>
                $this->format($notification)
            );

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Return unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'unread_count' => $request->user()
                ->unreadNotifications()
                ->count(),
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function markAsRead(
        Request $request,
        string $id
    ): JsonResponse {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'unread_count' => $request->user()
                ->unreadNotifications()
                ->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
            'unread_count' => 0,
        ]);
    }

    /**
     * Format notification for frontend.
     */
    private function format(
        DatabaseNotification $notification
    ): array {
        return [
            'id' => $notification->id,

            'category' => $notification->data['category'] ?? 'system',

            'title' => $notification->data['title'] ?? 'Notification',

            'message' => $notification->data['message'] ?? '',

            'url' => $notification->data['url'] ?? null,

            'metadata' => $notification->data['metadata'] ?? [],

            'read_at' => $notification->read_at?->toISOString(),

            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
