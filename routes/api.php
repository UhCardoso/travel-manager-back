<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', function (): JsonResponse {
    return response()->json([
        'message' => 'login teste',
    ]);
});

// Routes only for logged in users and admins
Route::middleware(['user', 'admin'])->prefix('user')->group(function () {
    Route::get('/profile', function (): JsonResponse {
        return response()->json([
            'message' => 'Perfil do usuário',
        ]);
    });
});

// Routes only for admins
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', function (): JsonResponse {
        return response()->json([
            'message' => 'perfil do administrador',
        ]);
    });
});
