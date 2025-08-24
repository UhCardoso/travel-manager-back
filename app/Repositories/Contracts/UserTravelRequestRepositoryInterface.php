<?php

namespace App\Repositories\Contracts;

use App\Models\TravelRequest;

interface UserTravelRequestRepositoryInterface
{
    /**
     * Store a new travel request
     */
    public function store(array $data): TravelRequest;

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $userId, int $travelRequestId): ?TravelRequest;
}
