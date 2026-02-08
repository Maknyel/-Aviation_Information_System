<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FacilityRequestController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/profile/update', [ProfileController::class, 'update']);

    // Facility Request routes
    Route::apiResource('facility-requests', FacilityRequestController::class);
    Route::patch('/facility-requests/{id}/status', [FacilityRequestController::class, 'updateStatus']);
    Route::post('/facility-requests/check-conflict', [FacilityRequestController::class, 'checkConflict']);

    // Work Order routes
    Route::apiResource('work-orders', WorkOrderController::class);
    Route::patch('/work-orders/{id}/status', [WorkOrderController::class, 'updateStatus']);
    Route::post('/work-orders/{id}/assign', [WorkOrderController::class, 'assign']);
    Route::get('/work-orders/{id}/recommendations', [WorkOrderController::class, 'getRecommendations']);
    Route::post('/work-orders/classify-priority', [WorkOrderController::class, 'classifyPriority']);

    // Dashboard routes
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
    Route::get('/dashboard/upcoming-requests', [DashboardController::class, 'getUpcomingRequests']);
    Route::get('/dashboard/venue-usage', [DashboardController::class, 'getVenueUsage']);
    Route::get('/dashboard/maintenance-data', [DashboardController::class, 'getMaintenanceData']);
    Route::get('/dashboard/calendar-events', [DashboardController::class, 'getCalendarEvents']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);

    // Department routes
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

    // User Management routes (Admin only)
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::post('/users', [UserManagementController::class, 'store']);
    Route::get('/users/{id}', [UserManagementController::class, 'show']);
    Route::put('/users/{id}', [UserManagementController::class, 'update']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    Route::put('/users/{id}/skills', [UserManagementController::class, 'updateSkills']);
    Route::get('/roles', [UserManagementController::class, 'roles']);

    // Feedback routes
    Route::get('/feedbacks', [FeedbackController::class, 'index']);
    Route::post('/feedbacks', [FeedbackController::class, 'store']);
    Route::get('/feedbacks/summary', [FeedbackController::class, 'summary']);
    Route::get('/feedbacks/{type}/{id}', [FeedbackController::class, 'forRequest']);

    // Report routes
    Route::get('/reports/monthly-volume', [ReportController::class, 'monthlyVolume']);
    Route::get('/reports/completion-time', [ReportController::class, 'completionTime']);
    Route::get('/reports/staff-performance', [ReportController::class, 'staffPerformance']);
    Route::get('/reports/hotspots', [ReportController::class, 'hotspots']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);

    // Activity Log routes
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
