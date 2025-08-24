<?php

use App\Enums\TravelRequestStatus;
use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Contracts\AdminTravelRequestRepositoryInterface;
use App\Services\AdminTravelRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->adminTravelRequestRepository = \Mockery::mock(AdminTravelRequestRepositoryInterface::class);
    $this->adminTravelRequestService = new AdminTravelRequestService($this->adminTravelRequestRepository);
    $this->admin = User::factory()->create(['role' => \App\Enums\UserRole::ADMIN->value]);
    $this->user = User::factory()->create(['role' => \App\Enums\UserRole::USER->value]);
});

afterEach(function () {
    \Mockery::close();
});

test('service gets all travel requests with pagination', function () {
    $travelRequests = TravelRequest::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    $paginator = new LengthAwarePaginator(
        $travelRequests,
        3,
        15,
        1
    );

    $this->adminTravelRequestRepository
        ->shouldReceive('getAll')
        ->once()
        ->with([])
        ->andReturn($paginator);

    $result = $this->adminTravelRequestService->getAll([]);

    expect($result)->toBeInstanceOf(TravelRequestCollection::class);
});

test('service gets all travel requests with filters', function () {
    $travelRequests = TravelRequest::factory()->count(1)->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $paginator = new LengthAwarePaginator(
        $travelRequests,
        1,
        15,
        1
    );

    $this->adminTravelRequestRepository
        ->shouldReceive('getAll')
        ->once()
        ->with(['status' => TravelRequestStatus::PENDING->value])
        ->andReturn($paginator);

    $result = $this->adminTravelRequestService->getAll([
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    expect($result)->toBeInstanceOf(TravelRequestCollection::class);
});

test('service gets travel request details successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->adminTravelRequestRepository
        ->shouldReceive('getDetails')
        ->once()
        ->with($travelRequest->id)
        ->andReturn($travelRequest);

    $result = $this->adminTravelRequestService->getDetails($travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);
});

test('service throws exception when travel request not found', function () {
    $this->adminTravelRequestRepository
        ->shouldReceive('getDetails')
        ->once()
        ->with(999)
        ->andReturn(null);

    expect(fn () => $this->adminTravelRequestService->getDetails(999))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

test('service updates travel request status successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $this->adminTravelRequestRepository
        ->shouldReceive('updateStatus')
        ->once()
        ->with($travelRequest->id, TravelRequestStatus::APPROVED->value)
        ->andReturn($travelRequest);

    $result = $this->adminTravelRequestService->updateStatus($travelRequest->id, TravelRequestStatus::APPROVED->value);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);
});

test('service handles different status updates', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $this->adminTravelRequestRepository
        ->shouldReceive('updateStatus')
        ->once()
        ->with($travelRequest->id, TravelRequestStatus::CANCELLED->value)
        ->andReturn($travelRequest);

    $result = $this->adminTravelRequestService->updateStatus($travelRequest->id, TravelRequestStatus::CANCELLED->value);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);
});

test('service handles pagination parameters', function () {
    $travelRequests = TravelRequest::factory()->count(5)->create([
        'user_id' => $this->user->id,
    ]);

    $paginator = new LengthAwarePaginator(
        $travelRequests,
        5,
        5,
        1
    );

    $this->adminTravelRequestRepository
        ->shouldReceive('getAll')
        ->once()
        ->with(['per_page' => 5])
        ->andReturn($paginator);

    $result = $this->adminTravelRequestService->getAll(['per_page' => 5]);

    expect($result)->toBeInstanceOf(TravelRequestCollection::class);
});

test('service handles date filter parameters', function () {
    $travelRequests = TravelRequest::factory()->count(1)->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-01-01',
    ]);

    $paginator = new LengthAwarePaginator(
        $travelRequests,
        1,
        15,
        1
    );

    $this->adminTravelRequestRepository
        ->shouldReceive('getAll')
        ->once()
        ->with(['departure_date' => '2025-01-01'])
        ->andReturn($paginator);

    $result = $this->adminTravelRequestService->getAll(['departure_date' => '2025-01-01']);

    expect($result)->toBeInstanceOf(TravelRequestCollection::class);
});

test('service handles name search parameters', function () {
    $travelRequests = TravelRequest::factory()->count(1)->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
    ]);

    $paginator = new LengthAwarePaginator(
        $travelRequests,
        1,
        15,
        1
    );

    $this->adminTravelRequestRepository
        ->shouldReceive('getAll')
        ->once()
        ->with(['name' => 'Paris'])
        ->andReturn($paginator);

    $result = $this->adminTravelRequestService->getAll(['name' => 'Paris']);

    expect($result)->toBeInstanceOf(TravelRequestCollection::class);
});
