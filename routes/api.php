<?php

use App\Http\Controllers\AdminTravelRequestController;
use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\UserTravelRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::post('/register', [AuthUserController::class, 'store']);
    Route::post('/login', [AuthUserController::class, 'login']);

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

// Admin authentication routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthAdminController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/logout', [AuthAdminController::class, 'logout']);

        Route::middleware(['auth:sanctum'])->prefix('travel-request')->group(function () {
            Route::get('/all', [AdminTravelRequestController::class, 'index']);
            Route::get('/{travelRequestId}/details', [AdminTravelRequestController::class, 'show']);
            Route::patch('/{travelRequestId}/update', [AdminTravelRequestController::class, 'update']);
        });
    });
});
