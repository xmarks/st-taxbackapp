<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\UserController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User Management
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);
    Route::get('/user/dashboard', [UserController::class, 'dashboard']);

    // Receipt Management
    Route::post('/receipts/scan', [ReceiptController::class, 'scan']);
    Route::post('/receipts/validate', [ReceiptController::class, 'validate']);
    Route::get('/receipts', [ReceiptController::class, 'index']);
    Route::get('/receipts/stats', [ReceiptController::class, 'stats']);
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show']);
});

// Legacy route for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');