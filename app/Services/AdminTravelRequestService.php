<?php

namespace App\Services;

use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Repositories\Contracts\AdminTravelRequestRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminTravelRequestService
{
    protected AdminTravelRequestRepositoryInterface $adminTravelRequestRepository;

    /**
     * Constructor to inject the repository
     */
    public function __construct(AdminTravelRequestRepositoryInterface $adminTravelRequestRepository)
    {
        $this->adminTravelRequestRepository = $adminTravelRequestRepository;
    }

    /**
     * Get all travel requests with pagination and filter
     */
    public function getAll(array $params): TravelRequestCollection
    {
        $paginatedResults = $this->adminTravelRequestRepository->getAll($params);

        return new TravelRequestCollection($paginatedResults);
    }

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $travelRequestId): TravelRequestResource
    {
        $travelRequest = $this->adminTravelRequestRepository->getDetails($travelRequestId);

        if (! $travelRequest) {
            throw new NotFoundHttpException('Solicitação de viagem não encontrada');
        }

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Update the status of a travel request
     */
    public function updateStatus(int $travelRequestId, string $status): TravelRequestResource
    {
        $travelRequest = $this->adminTravelRequestRepository->updateStatus($travelRequestId, $status);

        return new TravelRequestResource($travelRequest);
    }
}
