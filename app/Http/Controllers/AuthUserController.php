<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\SuccessResource;
use App\Services\AuthUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="User Authentication",
 *     description="Endpoints para autenticação de usuários"
 * )
 */
class AuthUserController extends Controller
{
    protected AuthUserService $authUserService;

    public function __construct(AuthUserService $authUserService)
    {
        $this->authUserService = $authUserService;
    }

    /**
     * @OA\Post(
     *     path="/api/user/register",
     *     operationId="registerUser",
     *     tags={"User Profile"},
     *     summary="Registra um novo usuário",
     *     description="Cria um novo usuário no sistema",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do usuário",
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreUserRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $result = $this->authUserService->store($request->validated());

        return (new SuccessResource([
            'message' => 'Usuário criado com sucesso',
            'data' => $result,
        ]))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/api/user/login",
     *     operationId="userLogin",
     *     tags={"User Authentication"},
     *     summary="Login de usuário",
     *     description="Autentica um usuário e retorna um token de acesso",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserLoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")
     *     )
     * )
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $result = $this->authUserService->login($request->email, $request->password);

        return (new SuccessResource([
            'message' => 'Login realizado com sucesso',
            'data' => $result,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/user/logout",
     *     operationId="userLogout",
     *     tags={"User Authentication"},
     *     summary="Logout de usuário",
     *     description="Realiza o logout do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authUserService->logout($request->user());

        return (new SuccessResource([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }
}
