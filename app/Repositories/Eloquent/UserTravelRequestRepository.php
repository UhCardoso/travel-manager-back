<?php

namespace App\Repositories\Eloquent;

use App\Models\TravelRequest;
use App\Repositories\Contracts\UserTravelRequestRepositoryInterface;

class UserTravelRequestRepository implements UserTravelRequestRepositoryInterface
{
    protected TravelRequest $model;

    /**
     * Constructor to inject the model
     */
    public function __construct(TravelRequest $model)
    {
        $this->model = $model;
    }

    /**
     * Store a new travel request
     */
    public function store(array $data): TravelRequest
    {
        return $this->model->create($data);
    }

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $userId, int $travelRequestId): ?TravelRequest
    {
        return $this->model->where('user_id', $userId)->findOrFail($travelRequestId);
    }
}
