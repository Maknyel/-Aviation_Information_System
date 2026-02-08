<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * List activity logs with filtering
     */
    public function index(Request $request)
    {
        $userRole = $request->user()->role->name;

        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = ActivityLog::with('user.role')->latest();

        if ($request->has('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->has('model_type') && $request->model_type !== 'all') {
            $query->where('model_type', $request->model_type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->input('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
