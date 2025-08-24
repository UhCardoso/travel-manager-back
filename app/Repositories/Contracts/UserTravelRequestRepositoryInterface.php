<?php

namespace App\Repositories\Contracts;

use App\Models\TravelRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserTravelRequestRepositoryInterface
{
    /**
     * Store a new travel request
     */
    public function store(array $data): TravelRequest;

    /**
     * Get all travel requests with pagination
     */
    public function getAll(int $userId, array $params): LengthAwarePaginator;

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $userId, int $travelRequestId): ?TravelRequest;

    /**
     * Cancel a travel request
     * User can only cancel if not approved
     */
    public function cancel(int $userId, int $travelRequestId): TravelRequest;
}
