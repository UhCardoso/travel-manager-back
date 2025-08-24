<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTravelRequest;
use App\Http\Requests\UpdateTravelRequestStatus;
use App\Http\Resources\SuccessResource;
use App\Services\AdminTravelRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Admin Travel Requests",
 *     description="API Endpoints para administradores gerenciarem solicitações de viagem"
 * )
 */
class AdminTravelRequestController extends Controller
{
    protected AdminTravelRequestService $adminTravelRequestService;

    public function __construct(AdminTravelRequestService $adminTravelRequestService)
    {
        $this->adminTravelRequestService = $adminTravelRequestService;
    }

    /**
     * Listar todas as solicitações de viagem (admin)
     *
     * @OA\Get(
     *     path="/api/admin/travel-request",
     *     tags={"Admin Travel Requests"},
     *     summary="Listar todas as solicitações de viagem",
     *     description="Lista todas as solicitações de viagem do sistema. Todos os parâmetros de filtro são opcionais.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Nome da viagem para filtrar solicitações (opcional)",
     *         required=false,
     *
     *         @OA\Schema(type="string", maxLength=255, nullable=true)
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status da solicitação de viagem (opcional)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"pending", "approved", "cancelled"}, nullable=true)
     *     ),
     *
     *     @OA\Parameter(
     *         name="departure_date",
     *         in="query",
     *         description="Data de partida para filtrar solicitações (opcional)",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date", nullable=true)
     *     ),
     *
     *     @OA\Parameter(
     *         name="return_date",
     *         in="query",
     *         description="Data de retorno para filtrar solicitações (opcional)",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date", nullable=true)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de itens por página (opcional, 1-100, padrão: 15)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15, nullable=true)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Solicitações de viagem encontradas com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Solicitações de viagem encontradas com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/TravelRequestCollection")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erros de validação",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado - requer role de admin"
     *     )
     * )
     */
    public function index(IndexTravelRequest $request): JsonResponse
    {
        $params = $request->validated();
        $travelRequests = $this->adminTravelRequestService->getAll($params);

        return (new SuccessResource([
            'message' => 'Solicitações de viagem encontradas com sucesso',
            'data' => $travelRequests,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Obter detalhes de uma solicitação de viagem (admin)
     *
     * @OA\Get(
     *     path="/api/admin/travel-request/{travelRequestId}",
     *     tags={"Admin Travel Requests"},
     *     summary="Obter detalhes de uma solicitação de viagem",
     *     description="Retorna os detalhes de uma solicitação de viagem específica com informações do usuário",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="travelRequestId",
     *         in="path",
     *         description="ID da solicitação de viagem",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da solicitação de viagem",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Solicitação de viagem encontrada com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/TravelRequestResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Solicitação de viagem não encontrada"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado - requer role de admin"
     *     )
     * )
     */
    public function show(int $travelRequestId): JsonResponse
    {
        $travelRequest = $this->adminTravelRequestService->getDetails($travelRequestId);

        return (new SuccessResource([
            'message' => 'Solicitação de viagem encontrada com sucesso',
            'data' => $travelRequest,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Atualizar o status de uma solicitação de viagem (admin)
     *
     * @OA\Patch(
     *     path="/api/admin/travel-request/{travelRequestId}",
     *     tags={"Admin Travel Requests"},
     *     summary="Atualizar status de uma solicitação de viagem",
     *     description="Atualiza o status de uma solicitação de viagem específica",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="travelRequestId",
     *         in="path",
     *         description="ID da solicitação de viagem",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"status"},
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending", "approved", "cancelled"},
     *                 example="approved",
     *                 description="Novo status da solicitação de viagem"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status da solicitação de viagem atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Status da solicitação de viagem atualizado com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/TravelRequestResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Solicitação de viagem não encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erros de validação",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado - requer role de admin"
     *     )
     * )
     */
    public function update(UpdateTravelRequestStatus $request, int $travelRequestId): JsonResponse
    {
        $data = $request->validated();
        $travelRequest = $this->adminTravelRequestService->updateStatus($travelRequestId, $data['status']);

        return (new SuccessResource([
            'message' => 'Status da solicitação de viagem atualizado com sucesso',
            'data' => $travelRequest,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }
}
