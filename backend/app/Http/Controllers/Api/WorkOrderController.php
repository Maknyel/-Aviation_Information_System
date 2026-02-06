<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with('user.role');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority if provided
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // For students/staff, only show their own work orders
        if ($request->user()->role->name !== 'Admin') {
            $query->where('user_id', $request->user()->id);
        }

        $workOrders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $workOrders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'priority' => 'required|in:urgent,high,medium,low',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/work_orders', $imageName);
            $validated['image'] = 'work_orders/' . $imageName;
        }

        $workOrder = WorkOrder::create($validated);

        // Create notification for staff only
        $staffUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'Staff');
        })->get();

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

        return response()->json([
            'success' => true,
            'message' => 'Work order created successfully',
            'data' => $workOrder->load('user.role')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $workOrder = WorkOrder::with('user.role')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $workOrder
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        // Only allow admin to update or the owner of the work order
        if ($request->user()->role->name !== 'Admin' && $workOrder->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this work order'
            ], 403);
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
            'status' => 'sometimes|in:pending,in_progress,completed,canceled',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($workOrder->image) {
                \Storage::delete('public/' . $workOrder->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/work_orders', $imageName);
            $validated['image'] = 'work_orders/' . $imageName;
        }

        $workOrder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Work order updated successfully',
            'data' => $workOrder->fresh()->load('user.role')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        // Only allow admin to delete or the owner of the work order
        if ($request->user()->role->name !== 'Admin' && $workOrder->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this work order'
            ], 403);
        }

        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order deleted successfully'
        ]);
    }

    /**
     * Update status of work order (Admin and Staff only)
     */
    public function updateStatus(Request $request, $id)
    {
        $userRole = $request->user()->role->name;

        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins and staff can update work order status'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,in_progress,completed,canceled'
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $workOrder->fresh()->load('user.role')
        ]);
    }
}
