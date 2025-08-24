<?php

use App\Enums\TravelRequestStatus;
use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTravelRequestRepositoryInterface;
use App\Services\UserTravelRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->userTravelRequestRepository = Mockery::mock(UserTravelRequestRepositoryInterface::class);
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->userTravelRequestService = new UserTravelRequestService(
        $this->userTravelRequestRepository,
        $this->userRepository
    );
    $this->user = User::factory()->create();
});

afterEach(function () {
    Mockery::close();
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

    $this->userTravelRequestRepository
        ->shouldReceive('getAll')
        ->once()
        ->with($this->user->id, [])
        ->andReturn($paginator);

    $result = $this->userTravelRequestService->getAll($this->user->id, []);

    expect($result)->toBeInstanceOf(TravelRequestCollection::class);
});

test('service gets travel request details successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->userTravelRequestRepository
        ->shouldReceive('getDetails')
        ->once()
        ->with($this->user->id, $travelRequest->id)
        ->andReturn($travelRequest);

    $result = $this->userTravelRequestService->getDetails($this->user->id, $travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);
});

test('service throws exception when travel request not found', function () {
    $this->userTravelRequestRepository
        ->shouldReceive('getDetails')
        ->once()
        ->with($this->user->id, 999)
        ->andReturn(null);

    expect(fn () => $this->userTravelRequestService->getDetails($this->user->id, 999))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

test('service cancels travel request successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    $this->userTravelRequestRepository
        ->shouldReceive('cancel')
        ->once()
        ->with($this->user->id, $travelRequest->id)
        ->andReturn($travelRequest);

    $result = $this->userTravelRequestService->cancel($this->user->id, $travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);
});

test('service throws exception when cancelling approved travel request', function () {
    $this->userTravelRequestRepository
        ->shouldReceive('cancel')
        ->once()
        ->with($this->user->id, 999)
        ->andThrow(new \InvalidArgumentException('Não é possível cancelar uma solicitação aprovada.'));

    expect(fn () => $this->userTravelRequestService->cancel($this->user->id, 999))
        ->toThrow(\InvalidArgumentException::class, 'Não é possível cancelar uma solicitação aprovada.');
});

test('service throws exception when cancelling another user travel request', function () {
    $this->userTravelRequestRepository
        ->shouldReceive('cancel')
        ->once()
        ->with($this->user->id, 999)
        ->andThrow(new \InvalidArgumentException('Você só pode cancelar suas próprias solicitações.'));

    expect(fn () => $this->userTravelRequestService->cancel($this->user->id, 999))
        ->toThrow(\InvalidArgumentException::class, 'Você só pode cancelar suas próprias solicitações.');
});

test('service stores new travel request', function () {
    $travelRequestData = [
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'departure_date' => '2025-01-01',
        'return_date' => '2025-01-07',
        'status' => TravelRequestStatus::PENDING->value,
    ];

    $travelRequest = TravelRequest::factory()->create($travelRequestData);

    $this->userTravelRequestRepository
        ->shouldReceive('store')
        ->once()
        ->with($travelRequestData)
        ->andReturn($travelRequest);

    $result = $this->userTravelRequestService->store($travelRequestData);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);
});
