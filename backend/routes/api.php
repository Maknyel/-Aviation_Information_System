<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FacilityRequestController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/profile/update', [ProfileController::class, 'update']);

    // Facility Request routes
    Route::apiResource('facility-requests', FacilityRequestController::class);
    Route::patch('/facility-requests/{id}/status', [FacilityRequestController::class, 'updateStatus']);

    // Work Order routes
    Route::apiResource('work-orders', WorkOrderController::class);
    Route::patch('/work-orders/{id}/status', [WorkOrderController::class, 'updateStatus']);

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
});
