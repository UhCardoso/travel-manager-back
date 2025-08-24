<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
                'error' => 'UNAUTHENTICATED',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Papel insuficiente para acessar este recurso.',
                'error' => 'INSUFFICIENT_PERMISSIONS',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
