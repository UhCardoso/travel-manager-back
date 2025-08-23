<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Corporate Travel Manager API",
 *     description="API para gerenciamento de viagens corporativas",
 *
 *     @OA\Contact(
 *         email="admin@corporate-travel.com",
 *         name="Suporte Técnico"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor da API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(
 *     name="Admin Authentication",
 *     description="Endpoints para autenticação de administradores"
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     title="Success Response",
 *     description="Resposta padrão de sucesso",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operação realizada com sucesso"),
 *     @OA\Property(property="data", type="object", description="Dados da resposta")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Error Response",
 *     description="Resposta padrão de erro",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Erro na operação"),
 *     @OA\Property(property="errors", type="object", description="Detalhes dos erros")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     title="Validation Error Response",
 *     description="Resposta de erro de validação",
 *
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Erros de validação por campo"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UnauthenticatedResponse",
 *     title="Unauthenticated Response",
 *     description="Resposta para usuário não autenticado",
 *
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * )
 *
 * @OA\Schema(
 *     schema="UnauthorizedResponse",
 *     title="Unauthorized Response",
 *     description="Resposta para usuário não autorizado",
 *
 *     @OA\Property(property="message", type="string", example="Access denied. Admin role required.")
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
