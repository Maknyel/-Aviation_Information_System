<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryAssignment;
use App\Models\ActivityLog;
use App\Models\FacilityRequest;
use App\Models\FacilityRequestInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    private function isAdminOrStaff(Request $request): bool
    {
        return in_array($request->user()->role->name, ['Admin', 'Staff']);
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()->role->name === 'Admin';
    }

    /**
     * Return availability of facility-request-linked items for a given date.
     * in_use = count of pending/approved requests on that date with the field checked.
     */
    public function facilityAvailability(Request $request)
    {
        $date = $request->query('date');
        $excludeId = $request->query('exclude_id');

        if (!$date) {
            return response()->json(['success' => false, 'message' => 'date parameter is required'], 422);
        }

        $items = InventoryItem::orderBy('name')->get();

        $result = [];
        foreach ($items as $item) {
            $query = FacilityRequestInventory::where('inventory_item_id', $item->id)
                ->whereHas('facilityRequest', fn($q) => $q
                    ->where('date_of_event', $date)
                    ->whereIn('status', ['pending', 'approved'])
                    ->when($excludeId, fn($q2) => $q2->where('id', '!=', $excludeId))
                );

            $inUse     = (int) $query->sum('quantity');
            $total     = $item->total_quantity;
            $available = max(0, $total - $inUse);

            $result[$item->id] = [
                'item_id'   => $item->id,
                'item_name' => $item->name,
                'total'     => $total,
                'in_use'    => $inUse,
                'available' => $available,
            ];
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function overview(Request $request)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $items = InventoryItem::all();
        $totalItems = $items->count();

        $today = Carbon::now('Asia/Manila')->toDateString();

        $totalDeployed = (int) FacilityRequestInventory::whereHas('facilityRequest', fn($q) => $q
            ->where('status', 'approved')
            ->where('date_of_event', $today)
        )->sum('quantity');

        $totalAvailable = $items->sum('total_quantity') - $totalDeployed;

        $byCategory = InventoryItem::select('category', DB::raw('count(*) as count'), DB::raw('sum(total_quantity) as total'))
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_items'     => $totalItems,
                'total_deployed'  => $totalDeployed,
                'total_available' => max(0, $totalAvailable),
                'by_category'     => $byCategory,
            ]
        ]);
    }

    public function index()
    {
        $items = InventoryItem::orderBy('name')->get();

        $today = Carbon::now('Asia/Manila')->toDateString();

        $rows = DB::table('facility_requests_inventory as fri')
            ->join('facility_requests as fr', 'fr.id', '=', 'fri.facility_request_id')
            ->where('fr.status', 'approved')
            ->whereDate('fr.date_of_event', $today)
            ->select(
                'fri.inventory_item_id',
                DB::raw('SUM(fri.quantity) as total_deployed'),
                'fr.venue_requested',
                'fr.location_room_number'
            )
            ->groupBy('fri.inventory_item_id', 'fr.id', 'fr.venue_requested', 'fr.location_room_number')
            ->get()
            ->groupBy('inventory_item_id');

        $result = $items->map(function ($item) use ($rows) {
            $group = $rows[$item->id] ?? collect();
            $deployed = (int) $group->sum('total_deployed');
            $locations = $group->map(fn($r) => $r->location_room_number
                ? "{$r->venue_requested} - {$r->location_room_number}"
                : $r->venue_requested
            )->unique()->values()->toArray();
            return array_merge($item->getAttributes(), [
                'deployed_quantity'  => $deployed,
                'available_quantity' => max(0, $item->total_quantity - $deployed),
                'locations'          => $locations,
            ]);
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function store(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'total_quantity' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'condition' => 'nullable|in:good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        $item = InventoryItem::create($validated);

        ActivityLog::log('created', 'Admin created inventory item: ' . $item->name, $item);

        return response()->json(['success' => true, 'data' => $item, 'message' => 'Inventory item created'], 201);
    }

    public function show(Request $request, $id)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item = InventoryItem::with(['activeAssignments.assignedBy'])->findOrFail($id);
        $item->available_quantity = $item->available_quantity;
        $item->deployed_quantity = $item->deployed_quantity;

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item = InventoryItem::findOrFail($id);
        $oldValues = $item->toArray();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'total_quantity' => 'sometimes|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'condition' => 'nullable|in:good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        ActivityLog::log('updated', 'Admin updated inventory item: ' . $item->name, $item, $oldValues, $item->toArray());

        return response()->json(['success' => true, 'data' => $item, 'message' => 'Inventory item updated']);
    }

    public function destroy(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item = InventoryItem::findOrFail($id);

        $activeCount = $item->activeAssignments()->count();
        if ($activeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete item with {$activeCount} active assignment(s). Return all items first."
            ], 422);
        }

        ActivityLog::log('deleted', 'Admin deleted inventory item: ' . $item->name, $item);
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Inventory item deleted']);
    }

    public function assignments(Request $request, $id)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item = InventoryItem::findOrFail($id);

        $facilityDeployments = FacilityRequestInventory::where('inventory_item_id', $item->id)
            ->whereHas('facilityRequest', fn($q) => $q
                ->whereIn('status', ['pending', 'approved'])
                ->where('date_of_event', '>=', now()->toDateString())
            )
            ->with(['facilityRequest.user'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($ri) => [
                'id'           => $ri->facilityRequest->id,
                'title'        => $ri->facilityRequest->title_of_event,
                'location'     => $ri->facilityRequest->venue_requested,
                'date'         => $ri->facilityRequest->date_of_event->format('Y-m-d'),
                'time'         => $ri->facilityRequest->time_of_event,
                'status'       => $ri->facilityRequest->status,
                'quantity'     => $ri->quantity,
                'requested_by' => $ri->facilityRequest->user?->name,
            ]);

        return response()->json([
            'success' => true,
            'data' => [],
            'facility_deployments' => $facilityDeployments,
        ]);
    }

    public function assign(Request $request, $id)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'quantity_assigned' => 'required|integer|min:1',
            'assigned_location' => 'required|string|max:255',
            'reference_type' => 'nullable|in:facility_request,work_order,manual',
            'reference_id' => 'nullable|integer',
            'expected_return_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $available = $item->available_quantity;
        if ($validated['quantity_assigned'] > $available) {
            return response()->json([
                'success' => false,
                'message' => "Only {$available} {$item->unit}(s) available. Cannot assign {$validated['quantity_assigned']}."
            ], 422);
        }

        $assignment = InventoryAssignment::create([
            ...$validated,
            'inventory_item_id' => $item->id,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        ActivityLog::log('created', "Assigned {$validated['quantity_assigned']} {$item->unit}(s) of {$item->name} to {$validated['assigned_location']}", $item);

        return response()->json([
            'success' => true,
            'data' => $assignment->load('assignedBy'),
            'message' => 'Items assigned successfully'
        ], 201);
    }

    public function returnAssignment(Request $request, $assignmentId)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $assignment = InventoryAssignment::with('inventoryItem')->findOrFail($assignmentId);

        if ($assignment->returned_at) {
            return response()->json(['success' => false, 'message' => 'Items already marked as returned'], 422);
        }

        $assignment->update(['returned_at' => now()]);

        ActivityLog::log('updated', "Returned {$assignment->quantity_assigned} {$assignment->inventoryItem->unit}(s) of {$assignment->inventoryItem->name} from {$assignment->assigned_location}", $assignment->inventoryItem);

        return response()->json(['success' => true, 'message' => 'Items marked as returned']);
    }
}
