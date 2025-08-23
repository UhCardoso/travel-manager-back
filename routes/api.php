<?php

use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::post('/register', [AuthUserController::class, 'create']);
});

// Admin authentication routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthAdminController::class, 'login']);

    // Protected admin routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/logout', [AuthAdminController::class, 'logout']);
    });
});
