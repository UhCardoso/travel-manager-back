<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\SuccessResource;
use App\Services\AuthUserService;
use Illuminate\Http\JsonResponse;
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
     *     tags={"User Authentication"},
     *     summary="Registra um novo usuário",
     *     description="Cria um novo usuário no sistema",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do usuário",
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Nome do usuário"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 maxLength=255,
     *                 description="Email do usuário"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 minLength=8,
     *                 description="Senha do usuário"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 description="Confirmação da senha"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Usuário criado com sucesso"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Os dados fornecidos são inválidos."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     )
     * )
     */
    public function create(CreateUserRequest $request): JsonResponse
    {
        $result = $this->authUserService->create($request->validated());

        return (new SuccessResource([
            'message' => 'Usuário criado com sucesso',
            'data' => $result,
        ]))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
