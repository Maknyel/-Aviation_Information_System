<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacilityRequest;
use App\Models\WorkOrder;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Monthly request volume (for line/bar chart)
     */
    public function monthlyVolume(Request $request)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $year = $request->input('year', Carbon::now()->year);

        $facilityData = FacilityRequest::selectRaw('MONTH(date_of_event) as month, COUNT(*) as count')
            ->whereYear('date_of_event', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $workOrderData = WorkOrder::selectRaw('MONTH(date) as month, COUNT(*) as count')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $months = [];
        $facilityValues = [];
        $workOrderValues = [];

        for ($m = 1; $m <= 12; $m++) {
            $months[] = Carbon::create()->month($m)->format('M');
            $facilityValues[] = $facilityData[$m] ?? 0;
            $workOrderValues[] = $workOrderData[$m] ?? 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $months,
                'facility_requests' => $facilityValues,
                'work_orders' => $workOrderValues,
            ]
        ]);
    }

    /**
     * Average completion time for work orders
     */
    public function completionTime(Request $request)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $year = $request->input('year', Carbon::now()->year);

        $data = WorkOrder::selectRaw('MONTH(date) as month, AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
            ->whereNotNull('completed_at')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $values = [];

        for ($m = 1; $m <= 12; $m++) {
            $months[] = Carbon::create()->month($m)->format('M');
            $row = $data->firstWhere('month', $m);
            $values[] = $row ? round($row->avg_hours, 1) : 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $months,
                'values' => $values,
            ]
        ]);
    }

    /**
     * Staff performance metrics
     */
    public function staffPerformance(Request $request)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $staff = User::whereHas('role', fn($q) => $q->whereIn('name', ['Staff', 'Admin']))
            ->withCount([
                'assignedWorkOrders as total_assigned' => function ($q) use ($month, $year) {
                    $q->whereMonth('date', $month)->whereYear('date', $year);
                },
                'assignedWorkOrders as completed' => function ($q) use ($month, $year) {
                    $q->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'completed');
                },
                'assignedWorkOrders as in_progress' => function ($q) use ($month, $year) {
                    $q->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'in_progress');
                },
            ])
            ->with('skills')
            ->get()
            ->map(function ($user) {
                $avgRating = Feedback::whereIn('request_id',
                    WorkOrder::where('assigned_to', $user->id)->pluck('id')
                )->where('request_type', 'work_order')->avg('rating');

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_assigned' => $user->total_assigned,
                    'completed' => $user->completed,
                    'in_progress' => $user->in_progress,
                    'completion_rate' => $user->total_assigned > 0
                        ? round(($user->completed / $user->total_assigned) * 100, 1)
                        : 0,
                    'average_rating' => $avgRating ? round($avgRating, 1) : null,
                    'skills' => $user->skills->pluck('skill'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }

    /**
     * Facility problem hotspots
     */
    public function hotspots(Request $request)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $months = $request->input('months', 6);

        $hotspots = WorkOrder::selectRaw('location, COUNT(*) as total_orders, SUM(CASE WHEN priority = "urgent" THEN 1 ELSE 0 END) as urgent_count')
            ->where('created_at', '>=', Carbon::now()->subMonths($months))
            ->groupBy('location')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hotspots
        ]);
    }

    /**
     * Overall summary statistics
     */
    public function summary(Request $request)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $thisMonth = Carbon::now();

        return response()->json([
            'success' => true,
            'data' => [
                'total_facility_requests' => FacilityRequest::count(),
                'total_work_orders' => WorkOrder::count(),
                'facility_this_month' => FacilityRequest::whereMonth('created_at', $thisMonth->month)->whereYear('created_at', $thisMonth->year)->count(),
                'work_orders_this_month' => WorkOrder::whereMonth('created_at', $thisMonth->month)->whereYear('created_at', $thisMonth->year)->count(),
                'pending_facility' => FacilityRequest::where('status', 'pending')->count(),
                'pending_work_orders' => WorkOrder::where('status', 'pending')->count(),
                'completed_work_orders' => WorkOrder::where('status', 'completed')->count(),
                'average_feedback_rating' => round(Feedback::avg('rating') ?? 0, 1),
                'total_users' => User::count(),
            ]
        ]);
    }
}
