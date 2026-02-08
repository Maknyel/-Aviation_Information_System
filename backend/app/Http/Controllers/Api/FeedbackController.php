<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * List feedbacks (admin/staff see all, users see own)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Feedback::with('user');

        if ($user->role->name !== 'Admin' && $user->role->name !== 'Staff') {
            $query->where('user_id', $user->id);
        }

        if ($request->has('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        $feedbacks = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }

    /**
     * Submit feedback for a completed request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|in:facility_request,work_order',
            'request_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if feedback already exists
        $existing = Feedback::where('request_type', $validated['request_type'])
            ->where('request_id', $validated['request_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted feedback for this request'
            ], 422);
        }

        $validated['user_id'] = $request->user()->id;

        $feedback = Feedback::create($validated);

        ActivityLog::log('created', "Submitted feedback (rating: {$feedback->rating}) for {$validated['request_type']} #{$validated['request_id']}", $feedback);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback->load('user')
        ], 201);
    }

    /**
     * Get feedback for a specific request
     */
    public function forRequest(Request $request, $type, $id)
    {
        $feedbacks = Feedback::with('user')
            ->where('request_type', $type)
            ->where('request_id', $id)
            ->get();

        $avgRating = $feedbacks->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'feedbacks' => $feedbacks,
                'average_rating' => round($avgRating, 1),
                'total' => $feedbacks->count(),
            ]
        ]);
    }

    /**
     * Get feedback summary/statistics
     */
    public function summary(Request $request)
    {
        $userRole = $request->user()->role->name;
        if ($userRole !== 'Admin' && $userRole !== 'Staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $avgRating = Feedback::avg('rating');
        $totalFeedbacks = Feedback::count();
        $ratingDistribution = Feedback::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->pluck('count', 'rating');

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => round($avgRating, 1),
                'total_feedbacks' => $totalFeedbacks,
                'distribution' => $ratingDistribution,
            ]
        ]);
    }
}
