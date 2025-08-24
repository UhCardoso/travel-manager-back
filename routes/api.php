<?php

use App\Http\Controllers\AdminTravelRequestController;
use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\UserTravelRequestController;
use Illuminate\Support\Facades\Route;

// User routes
Route::prefix('user')->group(function () {
    Route::post('/register', [AuthUserController::class, 'store']);
    Route::post('/login', [AuthUserController::class, 'login']);

    // User routes with authentication and role:user
    Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
        Route::post('/logout', [AuthUserController::class, 'logout']);

        // Travel requests routes
        Route::middleware(['auth:sanctum'])->prefix('travel-request')->group(function () {
            Route::post('/create', [UserTravelRequestController::class, 'store']);
            Route::get('/all', [UserTravelRequestController::class, 'index']);
            Route::get('/{travelRequestId}/details', [UserTravelRequestController::class, 'show']);
            Route::patch('/{travelRequestId}/cancel', [UserTravelRequestController::class, 'update']);
        });
    });

});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthAdminController::class, 'login']);

    // Admin routes with authentication and role:admin
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/logout', [AuthAdminController::class, 'logout']);

        // Travel requests routes
        Route::middleware(['auth:sanctum'])->prefix('travel-request')->group(function () {
            Route::get('/all', [AdminTravelRequestController::class, 'index']);
            Route::get('/{travelRequestId}/details', [AdminTravelRequestController::class, 'show']);
            Route::patch('/{travelRequestId}/update', [AdminTravelRequestController::class, 'update']);
        });
    });
});
