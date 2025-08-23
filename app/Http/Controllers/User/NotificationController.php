<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Get notifications for the navbar dropdown
     */
    public function getNotifications(Request $request)
    {
        $user = auth()->user();

        // Clear any cached notifications first
        Cache::forget("user_notifications_{$user->id}");
        Cache::forget("notifications_user_{$user->id}");

        // Build query with fresh data
        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // If 'all' parameter is passed, get all notifications, otherwise limit to recent ones
        if (!$request->has('all')) {
            $query->limit(10);
        }

        $notifications = $query->get();
        $unread_count = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Debug logging
        Log::info('NotificationController::getNotifications', [
            'user_id' => $user->id,
            'total_notifications' => $notifications->count(),
            'unread_count' => $unread_count,
            'has_all_param' => $request->has('all'),
            'latest_notification_id' => $notifications->first() ? $notifications->first()->id : null,
            'latest_notification_created' => $notifications->first() ? $notifications->first()->created_at : null,
            'all_notification_ids' => $notifications->pluck('id')->toArray()
        ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    }    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }

            // Count notifications before deletion
            $notificationsBefore = Notification::where('user_id', $user->id)->count();

            // Use direct query to ensure all notifications are deleted
            $deleted = Notification::where('user_id', $user->id)->delete();

            // Count notifications after deletion
            $notificationsAfter = Notification::where('user_id', $user->id)->count();

            // Clear any potential cache
            Cache::forget("user_notifications_{$user->id}");

            // Debug logging
            Log::info('NotificationController::clearAll', [
                'user_id' => $user->id,
                'notifications_before' => $notificationsBefore,
                'deleted_count' => $deleted,
                'notifications_after' => $notificationsAfter
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully',
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all notifications page
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);

        return view('user.notifications.index', compact('notifications'));
    }

    /**
     * Debug method to test notification creation
     */
    public function testNotification()
    {
        $user = auth()->user();

        $notification = Notification::createNotification(
            $user->id,
            'test',
            'Test Notification',
            'This is a test notification created at ' . now()
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification created',
            'notification_id' => $notification->id
        ]);
    }
}
