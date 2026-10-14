<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\ApprovalStep;
use App\Services\ConflictDetector;
use App\Helpers\EmailHelper;
use App\Models\FacilityRequestInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacilityRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = FacilityRequest::with(['user.role', 'department', 'requestItems.inventoryItem', 'assignee']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->user()->role->name !== 'Admin' && $request->user()->role->name !== 'Staff') {
            $query->where('user_id', $request->user()->id);
        }

        $facilityRequests = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $facilityRequests
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_requested'      => 'required|string|max:255',
            'location_room_number' => 'nullable|string|max:255',
            'title_of_event'       => 'required|string|max:255',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'date_of_event'        => 'required|date',
            'department_id'        => 'nullable|exists:departments,id',
            'items'                => 'nullable|array',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'attachment'           => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        // time_of_event mirrors the start time for older screens/reports
        $validated['time_of_event'] = $validated['start_time'];

        // The requested date and time must not overlap an existing request
        if ($existing = $this->conflictingRequest($validated['date_of_event'], $validated['start_time'], $validated['end_time'])) {
            return $this->conflictResponse($existing);
        }

        // Conflict detection
        $conflict = ConflictDetector::check(
            $validated['venue_requested'],
            $validated['date_of_event'],
            $validated['time_of_event']
        );

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Venue conflict detected',
                'conflict' => $conflict,
            ], 409);
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);
        unset($validated['attachment']);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('facility-attachments', 'public');
        }

        $validated['user_id'] = $request->user()->id;
        $validated['status']  = 'pending';

        if (empty($validated['department_id']) && $request->user()->department_id) {
            $validated['department_id'] = $request->user()->department_id;
        }

        $facilityRequest = FacilityRequest::create($validated);

        foreach ($items as $item) {
            FacilityRequestInventory::create([
                'facility_request_id' => $facilityRequest->id,
                'inventory_item_id'   => $item['inventory_item_id'],
                'quantity'            => $item['quantity'],
            ]);
        }

        // Create approval workflow step (either Staff or Admin may act on it;
        // approver_role is updated to whoever actually approves/rejects it)
        ApprovalStep::create([
            'request_type' => 'facility_request',
            'request_id' => $facilityRequest->id,
            'step_order' => 1,
            'approver_role' => 'Staff/Admin',
            'status' => 'pending',
        ]);

        // Notify staff and admins (both can act on facility requests)
        $staffUsers = User::whereHas('role', fn($q) => $q->whereIn('name', ['Staff', 'Admin']))->get();
        foreach ($staffUsers as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'facility_request',
                'reference_id' => $facilityRequest->id,
                'title' => 'New Facility Request',
                'message' => $request->user()->name . ' has submitted a new facility request for ' . $validated['venue_requested'],
                'is_read' => false,
                'is_deleted' => false,
            ]);
        }

        ActivityLog::log('created', "Submitted facility request for {$validated['venue_requested']}", $facilityRequest);

        return response()->json([
            'success' => true,
            'message' => 'Facility request created successfully',
            'data' => $facilityRequest->load(['user.role', 'department', 'approvalSteps'])
        ], 201);
    }

    public function show($id)
    {
        $facilityRequest = FacilityRequest::with(['user.role', 'department', 'approvalSteps.approver', 'feedbacks.user', 'requestItems.inventoryItem', 'assignee'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $facilityRequest
        ]);
    }

    public function update(Request $request, $id)
    {
        $facilityRequest = FacilityRequest::findOrFail($id);

        if ($request->user()->role->name !== 'Admin' && $facilityRequest->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to update this request'], 403);
        }

        $validated = $request->validate([
            'venue_requested'      => 'sometimes|string|max:255',
            'location_room_number' => 'nullable|string|max:255',
            'title_of_event'       => 'sometimes|string|max:255',
            'start_time'           => 'sometimes|date_format:H:i',
            'end_time'             => 'sometimes|date_format:H:i',
            'date_of_event'        => 'sometimes|date',
            'department_id'        => 'nullable|exists:departments,id',
            'status'               => 'sometimes|in:pending,approved,rejected,canceled',
            'items'                => 'sometimes|array',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'attachment'           => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if (isset($validated['start_time'])) {
            $validated['time_of_event'] = $validated['start_time'];
        }

        $effectiveStart = $validated['start_time'] ?? $facilityRequest->start_time ?? $facilityRequest->time_of_event;
        $effectiveEnd = $validated['end_time'] ?? $facilityRequest->end_time;
        if ($effectiveEnd && $effectiveStart && $effectiveEnd <= $effectiveStart) {
            return response()->json([
                'success' => false,
                'message' => 'The end time must be after the start time.',
                'errors' => ['end_time' => ['The end time must be after the start time.']],
            ], 422);
        }

        if ((isset($validated['date_of_event']) || isset($validated['start_time']) || isset($validated['end_time']))
            && $effectiveStart && $effectiveEnd
            && ($existing = $this->conflictingRequest(
                $validated['date_of_event'] ?? $facilityRequest->date_of_event->format('Y-m-d'),
                $effectiveStart,
                $effectiveEnd,
                $facilityRequest->id
            ))) {
            return $this->conflictResponse($existing);
        }

        if (isset($validated['venue_requested']) || isset($validated['date_of_event']) || isset($validated['time_of_event'])) {
            $conflict = ConflictDetector::check(
                $validated['venue_requested'] ?? $facilityRequest->venue_requested,
                $validated['date_of_event'] ?? $facilityRequest->date_of_event->format('Y-m-d'),
                $validated['time_of_event'] ?? $facilityRequest->time_of_event,
                $facilityRequest->id
            );

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venue conflict detected',
                    'conflict' => $conflict,
                ], 409);
            }
        }

        $items = $validated['items'] ?? null;
        unset($validated['items']);
        unset($validated['attachment']);

        if ($request->hasFile('attachment')) {
            if ($facilityRequest->attachment_path) {
                Storage::disk('public')->delete($facilityRequest->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')->store('facility-attachments', 'public');
        }

        $old = $facilityRequest->toArray();
        $facilityRequest->update($validated);

        if ($items !== null) {
            $facilityRequest->requestItems()->delete();
            foreach ($items as $item) {
                FacilityRequestInventory::create([
                    'facility_request_id' => $facilityRequest->id,
                    'inventory_item_id'   => $item['inventory_item_id'],
                    'quantity'            => $item['quantity'],
                ]);
            }
        }

        ActivityLog::log('updated', "Updated facility request #{$id}", $facilityRequest, $old, $facilityRequest->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Facility request updated successfully',
            'data' => $facilityRequest->fresh()->load(['user.role', 'department', 'requestItems.inventoryItem'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $facilityRequest = FacilityRequest::findOrFail($id);

        if ($request->user()->role->name !== 'Admin' && $facilityRequest->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete this request'], 403);
        }

        ActivityLog::log('deleted', "Deleted facility request #{$id} ({$facilityRequest->venue_requested})", $facilityRequest);

        $facilityRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Facility request deleted successfully'
        ]);
    }

    /**
     * Update status (Admin and Staff only) with approval workflow
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $userRole = $user->role->name;

        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Only admins and staff can update request status'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,canceled',
            'remarks' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);

        $facilityRequest = FacilityRequest::findOrFail($id);
        $oldStatus = $facilityRequest->status;

        // Update the current pending approval step with whoever actually acted on it
        $currentStep = ApprovalStep::where('request_type', 'facility_request')
            ->where('request_id', $id)
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();

        if ($currentStep) {
            $stepStatus = $validated['status'] === 'rejected' ? 'rejected' : 'approved';
            $currentStep->update([
                'approver_role' => $userRole,
                'status' => $stepStatus,
                'approver_id' => $user->id,
                'remarks' => $validated['remarks'] ?? null,
                'acted_at' => now(),
            ]);
        }

        $facilityRequest->update(['status' => $validated['status']]);

        // Notify the requester
        Notification::create([
            'user_id' => $facilityRequest->user_id,
            'type' => 'facility_request',
            'reference_id' => $facilityRequest->id,
            'title' => 'Facility Request ' . ucfirst($validated['status']),
            'message' => "Your facility request for {$facilityRequest->venue_requested} has been {$validated['status']} by {$user->name}.",
            'is_read' => false,
            'is_deleted' => false,
        ]);

        // Send status update email to requester
        $requester = User::find($facilityRequest->user_id);
        if ($requester) {
            EmailHelper::sendStatusUpdate($requester->email, $requester->name, 'Facility Request', $facilityRequest->id, $validated['status'], $validated['remarks'] ?? null);
        }

        ActivityLog::log('status_changed', "Changed facility request #{$id} status from {$oldStatus} to {$validated['status']}", $facilityRequest);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $facilityRequest->fresh()->load(['user.role', 'department', 'approvalSteps.approver'])
        ]);
    }

    /**
     * Assign a facility request to a staff member
     */
    public function assign(Request $request, $id)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $facilityRequest = FacilityRequest::findOrFail($id);
        $facilityRequest->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
        ]);

        $assignee = User::find($validated['assigned_to']);

        // Notify the assigned staff
        Notification::create([
            'user_id' => $validated['assigned_to'],
            'type' => 'facility_request',
            'reference_id' => $facilityRequest->id,
            'title' => 'Facility Request Assigned',
            'message' => "You have been assigned to the facility request for {$facilityRequest->venue_requested} by {$request->user()->name}.",
            'is_read' => false,
            'is_deleted' => false,
        ]);

        // Send assignment email to staff
        EmailHelper::sendAssignment($assignee->email, $assignee->name, 'Facility Request', $facilityRequest->id, $facilityRequest->title_of_event);

        ActivityLog::log('assigned', "Assigned facility request #{$id} to {$assignee->name}", $facilityRequest);

        return response()->json([
            'success' => true,
            'message' => "Facility request assigned to {$assignee->name}",
            'data' => $facilityRequest->fresh()->load(['user.role', 'department', 'assignee'])
        ]);
    }

    /**
     * Add extra equipment to an existing request (owner, Staff or Admin).
     * Quantities are merged into the same request and counted against inventory
     * immediately; they cannot exceed what is still available on the event date.
     */
    public function addExtraItems(Request $request, $id)
    {
        $user = $request->user();
        $facilityRequest = FacilityRequest::findOrFail($id);

        $isStaffOrAdmin = in_array($user->role->name, ['Admin', 'Staff']);
        if (!$isStaffOrAdmin && $facilityRequest->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to add equipment to this request'], 403);
        }

        if (!in_array($facilityRequest->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Extra equipment can only be added to pending or approved requests.',
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id|distinct',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $date = $facilityRequest->date_of_event->format('Y-m-d');
        $added = [];

        DB::transaction(function () use ($validated, $facilityRequest, $date, &$added) {
            foreach ($validated['items'] as $row) {
                $item = \App\Models\InventoryItem::lockForUpdate()->findOrFail($row['inventory_item_id']);

                // Everything already reserved on this date, this request included
                $inUse = (int) FacilityRequestInventory::where('inventory_item_id', $item->id)
                    ->whereHas('facilityRequest', fn($q) => $q
                        ->whereDate('date_of_event', $date)
                        ->whereIn('status', ['pending', 'approved'])
                    )
                    ->sum('quantity');
                $available = max(0, $item->total_quantity - $inUse);

                if ($row['quantity'] > $available) {
                    abort(response()->json([
                        'success' => false,
                        'message' => "Only {$available} {$item->unit} of {$item->name} available on this date (you asked for {$row['quantity']}).",
                    ], 422));
                }

                $existing = FacilityRequestInventory::where('facility_request_id', $facilityRequest->id)
                    ->where('inventory_item_id', $item->id)
                    ->first();

                if ($existing) {
                    $existing->increment('quantity', $row['quantity']);
                } else {
                    FacilityRequestInventory::create([
                        'facility_request_id' => $facilityRequest->id,
                        'inventory_item_id' => $item->id,
                        'quantity' => $row['quantity'],
                    ]);
                }

                $added[] = "{$row['quantity']} {$item->name}";
            }
        });

        $summary = implode(', ', $added);

        // Let the other side know: staff/admins when the requester adds, the requester when staff/admin adds
        if ($isStaffOrAdmin && $facilityRequest->user_id !== $user->id) {
            $recipients = collect([$facilityRequest->user_id]);
        } else {
            $recipients = User::whereHas('role', fn($q) => $q->whereIn('name', ['Staff', 'Admin']))
                ->where('id', '!=', $user->id)->pluck('id');
        }
        foreach ($recipients as $recipientId) {
            Notification::create([
                'user_id' => $recipientId,
                'type' => 'facility_request',
                'reference_id' => $facilityRequest->id,
                'title' => 'Extra Equipment Added',
                'message' => "{$user->name} added extra equipment ({$summary}) to the facility request for {$facilityRequest->venue_requested}.",
                'is_read' => false,
                'is_deleted' => false,
            ]);
        }

        ActivityLog::log('updated', "Added extra equipment to facility request #{$id}: {$summary}", $facilityRequest);

        return response()->json([
            'success' => true,
            'message' => 'Extra equipment added to the request',
            'data' => $facilityRequest->fresh()->load(['user.role', 'department', 'approvalSteps.approver', 'requestItems.inventoryItem', 'assignee']),
        ]);
    }

    /**
     * Inventory availability check for a specific facility request.
     */
    public function inventoryCheck(Request $request, $id)
    {
        $facilityRequest = FacilityRequest::with('requestItems.inventoryItem')->findOrFail($id);

        $inventoryStatus = [];
        foreach ($facilityRequest->requestItems as $requestItem) {
            $item = $requestItem->inventoryItem;

            // Sum qty from other pending/approved requests on same date for this item
            $inUse = (int) FacilityRequestInventory::where('inventory_item_id', $item->id)
                ->whereHas('facilityRequest', fn($q) => $q
                    ->where('date_of_event', $facilityRequest->date_of_event)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('id', '!=', $facilityRequest->id)
                )
                ->sum('quantity');

            $total     = $item->total_quantity;
            $available = max(0, $total - $inUse);

            // Events using this item on the same date
            $otherEvents = FacilityRequestInventory::where('inventory_item_id', $item->id)
                ->whereHas('facilityRequest', fn($q) => $q
                    ->where('date_of_event', $facilityRequest->date_of_event)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('id', '!=', $facilityRequest->id)
                )
                ->with('facilityRequest.user')
                ->get()
                ->map(fn($ri) => [
                    'title' => $ri->facilityRequest->title_of_event,
                    'venue' => $ri->facilityRequest->venue_requested,
                    'time'  => $ri->facilityRequest->time_of_event,
                    'user'  => $ri->facilityRequest->user?->name,
                    'qty'   => $ri->quantity,
                ]);

            $inventoryStatus[] = [
                'item_id'      => $item->id,
                'label'        => $item->name,
                'total'        => $total,
                'in_use'       => $inUse,
                'available'    => $available,
                'other_events' => $otherEvents,
            ];
        }

        $otherBookings = FacilityRequest::where('venue_requested', $facilityRequest->venue_requested)
            ->where('date_of_event', $facilityRequest->date_of_event)
            ->where('id', '!=', $facilityRequest->id)
            ->whereIn('status', ['pending', 'approved'])
            ->with('user')
            ->get(['id', 'title_of_event', 'time_of_event', 'status', 'user_id']);

        return response()->json([
            'success' => true,
            'data' => [
                'inventory'               => $inventoryStatus,
                'other_bookings_same_day' => $otherBookings,
            ]
        ]);
    }

    /**
     * First pending/approved facility request on the given date whose time overlaps start-end.
     * Back-to-back requests (one ends exactly when the next starts) do not conflict.
     * Older requests without an end time are treated as a single moment at their start time.
     */
    private function conflictingRequest(string $date, string $start, string $end, ?int $excludeId = null): ?FacilityRequest
    {
        return FacilityRequest::whereDate('date_of_event', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    $q2->whereNotNull('end_time')
                        ->whereRaw('COALESCE(start_time, time_of_event) < ?', [$end])
                        ->where('end_time', '>', $start);
                })->orWhere(function ($q2) use ($start, $end) {
                    $q2->whereNull('end_time')
                        ->whereRaw('COALESCE(start_time, time_of_event) >= ?', [$start])
                        ->whereRaw('COALESCE(start_time, time_of_event) < ?', [$end]);
                });
            })
            ->first();
    }

    private function conflictMessage(FacilityRequest $existing): string
    {
        $fmt = fn(?string $t) => $t ? date('g:i A', strtotime($t)) : null;
        $start = $fmt($existing->start_time ?: $existing->time_of_event);
        $end = $fmt($existing->end_time);
        $range = $end ? "{$start} - {$end}" : $start;

        return "The selected date and time conflicts with an existing facility request ({$range}). Please select a different date or time.";
    }

    private function conflictResponse(FacilityRequest $existing)
    {
        $message = $this->conflictMessage($existing);

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => ['date_of_event' => [$message]],
        ], 422);
    }

    /**
     * Tell the requester whether a date and time range overlaps an existing facility request.
     */
    public function dateCheck(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'exclude_id' => 'nullable|integer',
        ]);

        $existing = $this->conflictingRequest($request->date, $request->start_time, $request->end_time, $request->exclude_id);

        return response()->json([
            'success' => true,
            'has_request' => $existing !== null,
            'message' => $existing ? $this->conflictMessage($existing) : null,
        ]);
    }

    /**
     * Check for venue conflicts
     */
    public function checkConflict(Request $request)
    {
        $request->validate([
            'venue' => 'required|string',
            'date' => 'required|date',
            'time' => 'required|string',
            'exclude_id' => 'nullable|integer',
        ]);

        $conflict = ConflictDetector::check(
            $request->venue,
            $request->date,
            $request->time,
            $request->exclude_id
        );

        $bookings = ConflictDetector::getBookingsForDate($request->venue, $request->date);

        return response()->json([
            'success' => true,
            'has_conflict' => $conflict !== null,
            'conflict' => $conflict,
            'bookings_on_date' => $bookings,
        ]);
    }
}
