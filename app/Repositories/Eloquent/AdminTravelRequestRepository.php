<?php

namespace App\Repositories\Eloquent;

use App\Models\TravelRequest;
use App\Repositories\Contracts\AdminTravelRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminTravelRequestRepository implements AdminTravelRequestRepositoryInterface
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
     * Get all travel requests with pagination and filters (admin)
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (isset($params['name']) && ! empty($params['name'])) {
            $query->where('name', 'like', '%'.$params['name'].'%');
        }

        if (isset($params['status']) && ! empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['departure_date']) && ! empty($params['departure_date'])) {
            $query->where('departure_date', '>=', $params['departure_date']);
        }

        if (isset($params['return_date']) && ! empty($params['return_date'])) {
            $query->where('return_date', '<=', $params['return_date']);
        }

        $perPage = $params['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $travelRequestId): ?TravelRequest
    {
        return $this->model->with('user')->findOrFail($travelRequestId);
    }

    /**
     * Update the status of a travel request
     */
    public function updateStatus(int $travelRequestId, string $status): TravelRequest
    {
        $travelRequest = $this->model->findOrFail($travelRequestId);
        $travelRequest->update(['status' => $status]);

        return $travelRequest->fresh();
    }
}
