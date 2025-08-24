<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelTravelRequest;
use App\Http\Requests\IndexTravelRequest;
use App\Http\Requests\StoreTravelRequest;
use App\Http\Resources\SuccessResource;
use App\Services\UserTravelRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Travel Requests",
 *     description="API Endpoints para gerenciamento de solicitações de viagem"
 * )
 */
class UserTravelRequestController extends Controller
{
    protected UserTravelRequestService $userTravelRequestService;

    public function __construct(UserTravelRequestService $userTravelRequestService)
    {
        $this->userTravelRequestService = $userTravelRequestService;
    }

    /**
     * Criar uma nova solicitação de viagem
     *
     * @OA\Post(
     *     path="/api/user/travel-request",
     *     tags={"Travel Requests"},
     *     summary="Criar nova solicitação de viagem",
     *     description="Cria uma nova solicitação de viagem para o usuário autenticado",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "country", "departure_date", "return_date"},
     *
     *             @OA\Property(property="name", type="string", maxLength=255, example="Viagem para Barcelona"),
     *             @OA\Property(property="country", type="string", maxLength=255, example="Espanha"),
     *             @OA\Property(property="departure_date", type="string", format="date", example="2025-08-01"),
     *             @OA\Property(property="return_date", type="string", format="date", example="2025-08-07"),
     *             @OA\Property(property="town", type="string", maxLength=255, example="Barcelona", nullable=true),
     *             @OA\Property(property="state", type="string", maxLength=255, example="Catalunha", nullable=true),
     *             @OA\Property(property="region", type="string", maxLength=255, example="Nordeste", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Solicitação de viagem criada com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Solicitação de viagem criada com sucesso"),
     *             @OA\Property(property="data", type="object")
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
     *     )
     * )
     */
    public function store(StoreTravelRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $result = $this->userTravelRequestService->store($data);

        return (new SuccessResource([
            'message' => 'Solicitação de viagem criada com sucesso',
            'data' => $result,
        ]))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Listar solicitações de viagem do usuário
     *
     * @OA\Get(
     *     path="/api/user/travel-request",
     *     tags={"Travel Requests"},
     *     summary="Listar solicitações de viagem",
     *     description="Lista todas as solicitações de viagem do usuário autenticado. Todos os parâmetros de filtro são opcionais.",
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
     *     )
     * )
     */
    public function index(IndexTravelRequest $request): JsonResponse
    {
        $params = $request->validated();
        $travelRequests = $this->userTravelRequestService->getAll(Auth::id(), $params);

        return (new SuccessResource([
            'message' => 'Solicitações de viagem encontradas com sucesso',
            'data' => $travelRequests,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/user/travel-request/{travelRequestId}",
     *     tags={"Travel Requests"},
     *     summary="Obter detalhes de uma solicitação de viagem",
     *     description="Retorna os detalhes de uma solicitação de viagem específica",
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
     *     )
     * )
     */
    public function show(int $travelRequestId): JsonResponse
    {
        $travelRequest = $this->userTravelRequestService->getDetails(Auth::id(), $travelRequestId);

        return (new SuccessResource([
            'message' => 'Solicitação de viagem encontrada com sucesso',
            'data' => $travelRequest,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Cancelar uma solicitação de viagem (usuário)
     *
     * @OA\Patch(
     *     path="/api/user/travel-request/{travelRequestId}",
     *     tags={"Travel Requests"},
     *     summary="Cancelar uma solicitação de viagem",
     *     description="Cancela uma solicitação de viagem específica do usuário (não pode cancelar se aprovada)",
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
     *                 enum={"cancelled"},
     *                 example="cancelled",
     *                 description="Status para cancelar a solicitação de viagem"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Solicitação de viagem cancelada com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Solicitação de viagem cancelada com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/TravelRequestResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação de negócio",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Não é possível cancelar uma solicitação aprovada.")
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
     *     )
     * )
     */
    public function update(CancelTravelRequest $request, int $travelRequestId): JsonResponse
    {

        $request->validated();
        $travelRequest = $this->userTravelRequestService->cancel(Auth::id(), $travelRequestId);

        return (new SuccessResource([
            'message' => 'Solicitação de viagem cancelada com sucesso',
            'data' => $travelRequest,
        ]))->response()->setStatusCode(Response::HTTP_OK);
    }
}
