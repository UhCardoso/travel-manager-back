<?php

namespace App\Repositories\Eloquent;

use App\Models\TravelRequest;
use App\Repositories\Contracts\UserTravelRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * Get all travel requests with pagination and filters
     */
    public function getAll(int $userId, array $params): LengthAwarePaginator
    {
        $query = $this->model->where('user_id', $userId);

        if (isset($params['name'])) {
            $query->where('name', 'like', '%'.$params['name'].'%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['departure_date'])) {
            $query->where('departure_date', '>=', $params['departure_date']);
        }

        if (isset($params['return_date'])) {
            $query->where('return_date', '<=', $params['return_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($params['per_page'] ?? 15);
    }

    /**
     * Get the details of a travel request
     */
    public function getDetails(int $userId, int $travelRequestId): ?TravelRequest
    {
        return $this->model->where('user_id', $userId)->findOrFail($travelRequestId);
    }

    /**
     * Cancel a travel request
     * User can only cancel if not approved
     */
    public function cancel(int $userId, int $travelRequestId): TravelRequest
    {
        $travelRequest = $this->model->where('user_id', $userId)->findOrFail($travelRequestId);

        if ($travelRequest->status->value === 'approved') {
            throw new \InvalidArgumentException('Não é possível cancelar uma solicitação aprovada.');
        }

        $travelRequest->update(['status' => 'cancelled']);

        return $travelRequest->fresh();
    }
}
