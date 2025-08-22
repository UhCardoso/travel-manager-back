<?php

namespace App\Http\Controllers;

use App\Services\AuthAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthAdminController extends Controller
{
    protected AuthAdminService $authAdminService;

    public function __construct(AuthAdminService $authAdminService)
    {
        $this->authAdminService = $authAdminService;
    }

    /**
     * Admin login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $result = $this->authAdminService->login(
            $validator['email'],
            $validator['password']
        );

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => $result,
        ], Response::HTTP_OK);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->authAdminService->logout($request->user());

        return response()->json($result, Response::HTTP_OK);
    }
}
