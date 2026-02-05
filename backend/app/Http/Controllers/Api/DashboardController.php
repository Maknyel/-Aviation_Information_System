<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacilityRequest;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role->name === 'Admin';
        $today = Carbon::today();

        // Base queries - filter by user if not admin
        $facilityQuery = $isAdmin ? FacilityRequest::query() : FacilityRequest::where('user_id', $user->id);
        $workOrderQuery = $isAdmin ? WorkOrder::query() : WorkOrder::where('user_id', $user->id);

        $statistics = [
            // Facility Request Stats
            'facility_requests_today' => (clone $facilityQuery)->whereDate('date_of_event', $today)->count(),
            'facility_pending_requests' => (clone $facilityQuery)->where('status', 'pending')->count(),
            'facility_approved_requests' => (clone $facilityQuery)->where('status', 'approved')->count(),

            // Work Order Stats
            'work_orders_today' => (clone $workOrderQuery)->whereDate('date', $today)->count(),
            'pending_maintenance' => (clone $workOrderQuery)->where('status', 'pending')->count(),
            'urgent_repairs' => (clone $workOrderQuery)->where('priority', 'urgent')->where('status', '!=', 'completed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Get upcoming requests (both facility and work orders)
     */
    public function getUpcomingRequests(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role->name === 'Admin';
        $today = Carbon::today();

        // Facility Requests
        $facilityQuery = FacilityRequest::with('user.role')
            ->where('date_of_event', '>=', $today)
            ->where('status', '!=', 'canceled');

        if (!$isAdmin) {
            $facilityQuery->where('user_id', $user->id);
        }

        $facilityRequests = $facilityQuery->orderBy('date_of_event', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'type' => 'facility',
                    'title' => $request->venue_requested,
                    'subtitle' => $request->title_of_event,
                    'date' => $request->date_of_event,
                    'time' => $request->time_of_event,
                    'status' => $request->status,
                    'user' => $request->user->name ?? 'Unknown',
                ];
            });

        // Work Orders
        $workOrderQuery = WorkOrder::with('user.role')
            ->where('date', '>=', $today)
            ->where('status', '!=', 'canceled');

        if (!$isAdmin) {
            $workOrderQuery->where('user_id', $user->id);
        }

        $workOrders = $workOrderQuery->orderBy('date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'work_order',
                    'title' => $order->location,
                    'subtitle' => substr($order->description_of_problem, 0, 50),
                    'date' => $order->date,
                    'time' => $order->time,
                    'status' => $order->status,
                    'priority' => $order->priority,
                    'user' => $order->user->name ?? 'Unknown',
                ];
            });

        // Combine and sort by date
        $upcoming = $facilityRequests->concat($workOrders)
            ->sortBy('date')
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $upcoming
        ]);
    }

    /**
     * Get venue usage chart data
     */
    public function getVenueUsage(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role->name === 'Admin';

        $query = FacilityRequest::query();

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Group by venue
        $venueData = $query->selectRaw('venue_requested, COUNT(*) as count')
            ->where('status', '!=', 'canceled')
            ->groupBy('venue_requested')
            ->get();

        $labels = $venueData->pluck('venue_requested')->toArray();
        $data = $venueData->pluck('count')->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $data
            ]
        ]);
    }

    /**
     * Get maintenance chart data (work orders by location)
     */
    public function getMaintenanceData(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role->name === 'Admin';

        $query = WorkOrder::query();

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Group by location
        $maintenanceData = $query->selectRaw('location, COUNT(*) as count')
            ->where('status', '!=', 'canceled')
            ->groupBy('location')
            ->get();

        $labels = $maintenanceData->pluck('location')->toArray();
        $data = $maintenanceData->pluck('count')->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $data
            ]
        ]);
    }

    /**
     * Get calendar events (both facility requests and work orders)
     */
    public function getCalendarEvents(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role->name === 'Admin';
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Facility Requests
        $facilityQuery = FacilityRequest::query()
            ->whereYear('date_of_event', $year)
            ->whereMonth('date_of_event', $month)
            ->where('status', '!=', 'canceled');

        if (!$isAdmin) {
            $facilityQuery->where('user_id', $user->id);
        }

        $facilityEvents = $facilityQuery->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'type' => 'facility',
                'title' => $request->venue_requested,
                'date' => $request->date_of_event->format('Y-m-d'),
                'status' => $request->status,
            ];
        });

        // Work Orders
        $workOrderQuery = WorkOrder::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', '!=', 'canceled');

        if (!$isAdmin) {
            $workOrderQuery->where('user_id', $user->id);
        }

        $workOrderEvents = $workOrderQuery->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'type' => 'work_order',
                'title' => $order->location,
                'date' => $order->date->format('Y-m-d'),
                'status' => $order->status,
                'priority' => $order->priority,
            ];
        });

        $events = $facilityEvents->concat($workOrderEvents)->values();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }
}
