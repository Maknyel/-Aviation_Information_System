<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Notification;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\ApprovalStep;
use App\Services\PriorityClassifier;
use App\Services\SmartAssigner;
use App\Helpers\EmailHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['user.role', 'department', 'assignee']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
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

        $workOrders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $workOrders
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string',
            'description_of_problem' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'requisitioner' => 'required|string|max:255',
            'priority' => 'nullable|in:urgent,high,medium,low',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        // Rule-based priority auto-assignment if not manually set
        if (empty($validated['priority'])) {
            $validated['priority'] = PriorityClassifier::classify(
                $validated['description_of_problem'],
                $validated['location']
            );
        }

        // Auto-assign department from user if not specified
        if (empty($validated['department_id']) && $request->user()->department_id) {
            $validated['department_id'] = $request->user()->department_id;
        }

        // Handle image upload (direct to public, no symlink needed)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/work_orders'), $imageName);
            $validated['image'] = 'work_orders/' . $imageName;
        }

        $workOrder = WorkOrder::create($validated);

        // Create approval workflow steps
        ApprovalStep::create([
            'request_type' => 'work_order',
            'request_id' => $workOrder->id,
            'step_order' => 1,
            'approver_role' => 'Staff',
            'status' => 'pending',
        ]);
        ApprovalStep::create([
            'request_type' => 'work_order',
            'request_id' => $workOrder->id,
            'step_order' => 2,
            'approver_role' => 'Admin',
            'status' => 'pending',
        ]);

        // Notify staff
        $staffUsers = User::whereHas('role', fn($q) => $q->where('name', 'Staff'))->get();
        foreach ($staffUsers as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'type' => 'work_order',
                'reference_id' => $workOrder->id,
                'title' => 'New Work Order',
                'message' => $request->user()->name . ' has submitted a new work order for ' . $validated['location'],
                'is_read' => false,
                'is_deleted' => false,
            ]);
        }

        ActivityLog::log('created', "Submitted work order for {$validated['location']} (priority: {$validated['priority']})", $workOrder);

        return response()->json([
            'success' => true,
            'message' => 'Work order created successfully',
            'data' => $workOrder->load(['user.role', 'department', 'approvalSteps']),
            'auto_priority' => $validated['priority'],
        ], 201);
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with(['user.role', 'department', 'assignee', 'approvalSteps.approver', 'feedbacks.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $workOrder
        ]);
    }

    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($request->user()->role->name !== 'Admin' && $workOrder->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to update this work order'], 403);
        }

        $validated = $request->validate([
            'location' => 'sometimes|string|max:255',
            'room_number' => 'nullable|string|max:255',
            'date' => 'sometimes|date',
            'time' => 'sometimes|string',
            'description_of_problem' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'requisitioner' => 'sometimes|string|max:255',
            'priority' => 'sometimes|in:urgent,high,medium,low',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'sometimes|in:pending,in_progress,completed,canceled',
        ]);

        if ($request->hasFile('image')) {
            if ($workOrder->image) {
                $oldPath = public_path('storage/' . $workOrder->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/work_orders'), $imageName);
            $validated['image'] = 'work_orders/' . $imageName;
        }

        $old = $workOrder->toArray();
        $workOrder->update($validated);

        ActivityLog::log('updated', "Updated work order #{$id}", $workOrder, $old, $workOrder->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Work order updated successfully',
            'data' => $workOrder->fresh()->load(['user.role', 'department', 'assignee'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($request->user()->role->name !== 'Admin' && $workOrder->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete this work order'], 403);
        }

        ActivityLog::log('deleted', "Deleted work order #{$id} ({$workOrder->location})", $workOrder);

        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order deleted successfully'
        ]);
    }

    /**
     * Update status with approval workflow
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $userRole = $user->role->name;

        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Only admins and staff can update work order status'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,in_progress,completed,canceled',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $oldStatus = $workOrder->status;

        // Update approval step
        $currentStep = ApprovalStep::where('request_type', 'work_order')
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

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
        }

        if (!empty($validated['remarks'])) {
            $updateData['admin_remarks'] = $validated['remarks'];
        }

        $workOrder->update($updateData);

        // Notify the requester
        Notification::create([
            'user_id' => $workOrder->user_id,
            'type' => 'work_order',
            'reference_id' => $workOrder->id,
            'title' => 'Work Order ' . ucfirst(str_replace('_', ' ', $validated['status'])),
            'message' => "Your work order for {$workOrder->location} has been " . str_replace('_', ' ', $validated['status']) . " by {$user->name}.",
            'is_read' => false,
            'is_deleted' => false,
        ]);

        // Send status update email to requester
        $requester = User::find($workOrder->user_id);
        if ($requester) {
            EmailHelper::sendStatusUpdate($requester->email, $requester->name, 'Work Order', $workOrder->id, $validated['status']);
        }

        ActivityLog::log('status_changed', "Changed work order #{$id} status from {$oldStatus} to {$validated['status']}", $workOrder);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $workOrder->fresh()->load(['user.role', 'department', 'assignee', 'approvalSteps.approver'])
        ]);
    }

    /**
     * Assign a work order to a staff member
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

        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
        ]);

        $assignee = User::find($validated['assigned_to']);

        // Notify the assigned staff
        Notification::create([
            'user_id' => $validated['assigned_to'],
            'type' => 'work_order',
            'reference_id' => $workOrder->id,
            'title' => 'Work Order Assigned',
            'message' => "You have been assigned to work order at {$workOrder->location} by {$request->user()->name}.",
            'is_read' => false,
            'is_deleted' => false,
        ]);

        // Send assignment email to staff
        EmailHelper::sendAssignment($assignee->email, $assignee->name, 'Work Order', $workOrder->id, $workOrder->description_of_problem);

        ActivityLog::log('assigned', "Assigned work order #{$id} to {$assignee->name}", $workOrder);

        return response()->json([
            'success' => true,
            'message' => "Work order assigned to {$assignee->name}",
            'data' => $workOrder->fresh()->load(['user.role', 'department', 'assignee'])
        ]);
    }

    /**
     * Get smart assignment recommendations
     */
    public function getRecommendations(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        $recommendations = SmartAssigner::recommend(
            $workOrder->description_of_problem,
            $workOrder->location
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    /**
     * Classify priority for a description (preview)
     */
    public function classifyPriority(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'location' => 'nullable|string',
        ]);

        $priority = PriorityClassifier::classify(
            $request->description,
            $request->location ?? ''
        );

        return response()->json([
            'success' => true,
            'priority' => $priority,
        ]);
    }
}
