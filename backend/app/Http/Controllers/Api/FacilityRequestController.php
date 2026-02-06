<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacilityRequest;
use Illuminate\Http\Request;

class FacilityRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FacilityRequest::with('user.role');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // For students/staff, only show their own requests
        if ($request->user()->role->name !== 'Admin') {
            $query->where('user_id', $request->user()->id);
        }

        $facilityRequests = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $facilityRequests
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_requested' => 'required|string|max:255',
            'location_room_number' => 'nullable|string|max:255',
            'title_of_event' => 'required|string|max:255',
            'time_of_event' => 'required|string',
            'date_of_event' => 'required|date',
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

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $facilityRequest = FacilityRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Facility request created successfully',
            'data' => $facilityRequest->load('user.role')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $facilityRequest = FacilityRequest::with('user.role')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $facilityRequest
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $facilityRequest = FacilityRequest::findOrFail($id);

        // Only allow admin to update or the owner of the request
        if ($request->user()->role->name !== 'Admin' && $facilityRequest->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this request'
            ], 403);
        }

        $validated = $request->validate([
            'venue_requested' => 'sometimes|string|max:255',
            'location_room_number' => 'nullable|string|max:255',
            'title_of_event' => 'sometimes|string|max:255',
            'time_of_event' => 'sometimes|string',
            'date_of_event' => 'sometimes|date',
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

        $facilityRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Facility request updated successfully',
            'data' => $facilityRequest->fresh()->load('user.role')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $facilityRequest = FacilityRequest::findOrFail($id);

        // Only allow admin to delete or the owner of the request
        if ($request->user()->role->name !== 'Admin' && $facilityRequest->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this request'
            ], 403);
        }

        $facilityRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Facility request deleted successfully'
        ]);
    }

    /**
     * Update status of facility request (Admin and Staff only)
     */
    public function updateStatus(Request $request, $id)
    {
        $userRole = $request->user()->role->name;

        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins and staff can update request status'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,canceled'
        ]);

        $facilityRequest = FacilityRequest::findOrFail($id);
        $facilityRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $facilityRequest->fresh()->load('user.role')
        ]);
    }
}
