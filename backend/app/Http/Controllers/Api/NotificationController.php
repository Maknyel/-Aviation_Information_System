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
     * Get all notifications for staff/admin
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userRole = $user->role->name;

        // Only allow Staff and Admin to view all notifications
        if ($userRole !== 'Staff' && $userRole !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Get all non-deleted notifications
        $notifications = Notification::where('is_deleted', false)
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
        $notification = Notification::findOrFail($id);

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

        Notification::where('is_read', false)
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
        $notification = Notification::findOrFail($id);

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
        Notification::where('is_deleted', false)
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
        $count = Notification::where('is_read', false)
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
