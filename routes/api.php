<?php

use App\Http\Controllers\AuthAdminController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

// Admin authentication routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthAdminController::class, 'login']);

    // Protected admin routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/logout', [AuthAdminController::class, 'logout']);

        Route::get('/teste', function (): JsonResponse {
            return response()->json([
                'message' => 'teste do administrador',
            ]);
        });
    });
});
