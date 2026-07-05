<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationApiController;












Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/student/notifications', [NotificationApiController::class, 'index']);
    Route::post('/student/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::get('/student/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
});
