<?php

namespace App\Services;

use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTravelRequestRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserTravelRequestService
{
    protected UserTravelRequestRepositoryInterface $userTravelRequestRepository;

    protected UserRepositoryInterface $userRepository;

    /**
     * Constructor to inject the repositories
     */
    public function __construct(
        UserTravelRequestRepositoryInterface $userTravelRequestRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->userTravelRequestRepository = $userTravelRequestRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Store a new travel request
     */
    public function store(array $data): TravelRequestResource
    {

        $travelRequest = $this->userTravelRequestRepository->store($data);

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Get all travel requests with pagination
     */
    public function getAll(int $userId, array $params): TravelRequestCollection
    {
        $paginatedResults = $this->userTravelRequestRepository->getAll($userId, $params);

        return new TravelRequestCollection($paginatedResults);
    }

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $userId, int $travelRequestId): TravelRequestResource
    {
        $travelRequest = $this->userTravelRequestRepository->getDetails($userId, $travelRequestId);

        if (! $travelRequest) {
            throw new NotFoundHttpException('Solicitação de viagem não encontrada');
        }

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Cancel a travel request
     * User can only cancel if not approved
     */
    public function cancel(int $userId, int $travelRequestId): TravelRequestResource
    {
        try {
            $travelRequest = $this->userTravelRequestRepository->cancel($userId, $travelRequestId);

            return new TravelRequestResource($travelRequest);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }
}
