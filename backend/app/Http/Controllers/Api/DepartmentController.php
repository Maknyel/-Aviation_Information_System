<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['users', 'facilityRequests', 'workOrders'])->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:20|unique:departments',
            'description' => 'nullable|string|max:255',
        ]);

        $department = Department::create($validated);

        ActivityLog::log('created', "Created department: {$department->name}", $department);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $department = Department::findOrFail($id);
        $old = $department->toArray();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:departments,name,' . $id,
            'code' => 'sometimes|string|max:20|unique:departments,code,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        $department->update($validated);

        ActivityLog::log('updated', "Updated department: {$department->name}", $department, $old, $department->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department->fresh()
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $department = Department::findOrFail($id);

        ActivityLog::log('deleted', "Deleted department: {$department->name}", $department);

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully'
        ]);
    }
}
