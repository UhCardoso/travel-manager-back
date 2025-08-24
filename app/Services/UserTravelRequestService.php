<?php

namespace App\Services;

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
}
