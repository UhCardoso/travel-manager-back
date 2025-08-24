<?php

namespace App\Repositories\Contracts;

use App\Models\TravelRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminTravelRequestRepositoryInterface
{
    /**
     * Get all travel requests with pagination and filters (admin)
     */
    public function getAll(array $params): LengthAwarePaginator;

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $travelRequestId): ?TravelRequest;

    /**
     * Update the status of a travel request
     */
    public function updateStatus(int $travelRequestId, string $status): TravelRequest;
}
