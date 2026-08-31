<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\FacilityRequest;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications belonging to the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('is_deleted', false)
            ->where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Enhance notifications with reference data
        $notifications = $notifications->map(function ($notification) {
            if ($notification->type === 'facility_request' && $notification->reference_id) {
                $facilityRequest = FacilityRequest::with('user')->find($notification->reference_id);
                $notification->reference_data = $facilityRequest;
            } elseif ($notification->type === 'work_order' && $notification->reference_id) {
                $workOrder = WorkOrder::with('user')->find($notification->reference_id);
                $notification->reference_data = $workOrder;
            }
            return $notification;
        });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->where('is_deleted', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete (soft delete) a notification
     */
    public function delete(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);

        $notification->update(['is_deleted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Clear all notifications (soft delete)
     */
    public function clearAll(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_deleted', false)
            ->update(['is_deleted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->where('is_deleted', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }
}
