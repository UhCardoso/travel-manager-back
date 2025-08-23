<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Resources\SuccessResource;
use App\Services\AuthAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Admin Authentication",
 *     description="Endpoints para autenticação de administradores"
 * )
 */
class AuthAdminController extends Controller
{
    protected AuthAdminService $authAdminService;

    public function __construct(AuthAdminService $authAdminService)
    {
        $this->authAdminService = $authAdminService;
    }

    /**
     * @OA\Post(
     *     path="/api/admin/login",
     *     operationId="adminLogin",
     *     tags={"Admin Authentication"},
     *     summary="Login de administrador",
     *     description="Autentica um usuário administrador e retorna um token de acesso",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminLoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *
     *         @OA\JsonContent(
     *             allOf={
     *
     *                 @OA\Schema(ref="#/components/schemas/SuccessResourceData"),
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", example="Login realizado com sucesso"),
     *                     @OA\Property(
     *                         property="data",
     *                         ref="#/components/schemas/AuthResourceData"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(
     *             allOf={
     *
     *                 @OA\Schema(ref="#/components/schemas/ValidationErrorResponse"),
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", example="Os dados fornecidos são inválidos."),
     *                     @OA\Property(
     *                         property="errors",
     *                         ref="#/components/schemas/AdminLoginRequestErrors"
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $result = $this->authAdminService->login(
            $request->email,
            $request->password
        );

        return (new SuccessResource([
            'message' => 'Login realizado com sucesso',
            'data' => $result,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/logout",
     *     operationId="adminLogout",
     *     tags={"Admin Authentication"},
     *     summary="Logout de administrador",
     *     description="Encerra a sessão do administrador e invalida o token de acesso",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *
     *         @OA\JsonContent(
     *             allOf={
     *
     *                 @OA\Schema(ref="#/components/schemas/SuccessResourceData"),
     *                 @OA\Schema(
     *
     *                     @OA\Property(property="message", example="Sessão encerrada com sucesso.")
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Não autorizado",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authAdminService->logout($request->user());

        $result = $this->authAdminService->logout($request->user());

        return response()->json($result, Response::HTTP_OK);
    }
}
