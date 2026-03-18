<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CannedReplyController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\RequestCommentController;
use App\Http\Controllers\Api\RequestFileController;
use App\Http\Controllers\Api\WorkRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', DashboardController::class);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead']);

    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);

    Route::get('/requests', [WorkRequestController::class, 'index']);
    Route::post('/requests', [WorkRequestController::class, 'store']);
    Route::get('/requests/{workRequest}', [WorkRequestController::class, 'show']);
    Route::patch('/requests/{workRequest}', [WorkRequestController::class, 'update']);
    Route::post('/requests/{workRequest}/comments', [RequestCommentController::class, 'store']);
    Route::post('/requests/{workRequest}/files', [RequestFileController::class, 'store']);
    Route::get('/requests/{workRequest}/files/{requestFile}/download', [RequestFileController::class, 'download']);
    Route::post('/requests/{workRequest}/reminders', [ReminderController::class, 'send']);

    Route::get('/canned-replies', [CannedReplyController::class, 'index']);
});
