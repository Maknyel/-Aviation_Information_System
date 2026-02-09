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
use Illuminate\Http\Request;

class FacilityRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = FacilityRequest::with(['user.role', 'department']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
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
            'venue_requested' => 'required|string|max:255',
            'location_room_number' => 'nullable|string|max:255',
            'title_of_event' => 'required|string|max:255',
            'time_of_event' => 'required|string',
            'date_of_event' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'chair' => 'boolean',
            'podium' => 'boolean',
            'tent' => 'boolean',
            'tables' => 'boolean',
            'booths' => 'boolean',
            'sound_system' => 'boolean',
            'extension' => 'boolean',
            'microphones' => 'boolean',
            'skirting' => 'boolean',
            'flags' => 'boolean',
            'others' => 'boolean',
            'others_description' => 'nullable|string',
        ]);

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

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        // Auto-assign department from user if not specified
        if (empty($validated['department_id']) && $request->user()->department_id) {
            $validated['department_id'] = $request->user()->department_id;
        }

        $facilityRequest = FacilityRequest::create($validated);

        // Create approval workflow steps
        ApprovalStep::create([
            'request_type' => 'facility_request',
            'request_id' => $facilityRequest->id,
            'step_order' => 1,
            'approver_role' => 'Staff',
            'status' => 'pending',
        ]);
        ApprovalStep::create([
            'request_type' => 'facility_request',
            'request_id' => $facilityRequest->id,
            'step_order' => 2,
            'approver_role' => 'Admin',
            'status' => 'pending',
        ]);

        // Notify staff
        $staffUsers = User::whereHas('role', fn($q) => $q->where('name', 'Staff'))->get();
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
        $facilityRequest = FacilityRequest::with(['user.role', 'department', 'approvalSteps.approver', 'feedbacks.user'])->findOrFail($id);

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
            'venue_requested' => 'sometimes|string|max:255',
            'location_room_number' => 'nullable|string|max:255',
            'title_of_event' => 'sometimes|string|max:255',
            'time_of_event' => 'sometimes|string',
            'date_of_event' => 'sometimes|date',
            'department_id' => 'nullable|exists:departments,id',
            'chair' => 'boolean',
            'podium' => 'boolean',
            'tent' => 'boolean',
            'tables' => 'boolean',
            'booths' => 'boolean',
            'sound_system' => 'boolean',
            'extension' => 'boolean',
            'microphones' => 'boolean',
            'skirting' => 'boolean',
            'flags' => 'boolean',
            'others' => 'boolean',
            'others_description' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,rejected,canceled',
        ]);

        // Conflict check if venue/date/time changed
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

        $old = $facilityRequest->toArray();
        $facilityRequest->update($validated);

        ActivityLog::log('updated', "Updated facility request #{$id}", $facilityRequest, $old, $facilityRequest->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Facility request updated successfully',
            'data' => $facilityRequest->fresh()->load(['user.role', 'department'])
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
            'remarks' => 'nullable|string|max:1000',
        ]);

        $facilityRequest = FacilityRequest::findOrFail($id);
        $oldStatus = $facilityRequest->status;

        // Update the current pending approval step
        $currentStep = ApprovalStep::where('request_type', 'facility_request')
            ->where('request_id', $id)
            ->where('approver_role', $userRole)
            ->where('status', 'pending')
            ->first();

        if ($currentStep) {
            $stepStatus = $validated['status'] === 'rejected' ? 'rejected' : 'approved';
            $currentStep->update([
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
            EmailHelper::sendStatusUpdate($requester->email, $requester->name, 'Facility Request', $facilityRequest->id, $validated['status']);
        }

        ActivityLog::log('status_changed', "Changed facility request #{$id} status from {$oldStatus} to {$validated['status']}", $facilityRequest);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $facilityRequest->fresh()->load(['user.role', 'department', 'approvalSteps.approver'])
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
