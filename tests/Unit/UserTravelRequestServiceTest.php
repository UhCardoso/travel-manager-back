<?php

use App\Enums\TravelRequestStatus;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTravelRequestRepositoryInterface;
use App\Services\UserTravelRequestService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->userTravelRequestRepository = $this->app->make(UserTravelRequestRepositoryInterface::class);
    $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    $this->userTravelRequestService = new UserTravelRequestService(
        $this->userTravelRequestRepository,
        $this->userRepository
    );
});

test('travel request service stores travel request correctly', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelData = [
        'user_id' => $user->id,
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'town' => 'Paris',
        'state' => 'Île-de-France',
        'region' => 'Europa',
        'departure_date' => '2024-06-15',
        'return_date' => '2024-06-22',
    ];

    $result = $this->userTravelRequestService->store($travelData);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);

    $resultData = $result->resolve();
    expect($resultData['user_id'])->toBe($user->id)
        ->and($resultData['name'])->toBe('Viagem para Paris')
        ->and($resultData['country'])->toBe('França')
        ->and($resultData['status'])->toBe(TravelRequestStatus::PENDING->value);
});

test('travel request service creates travel request with minimal data', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelData = [
        'user_id' => $user->id,
        'name' => 'Viagem para Londres',
        'country' => 'Reino Unido',
        'departure_date' => '2024-07-01',
        'return_date' => '2024-07-08',
    ];

    $result = $this->userTravelRequestService->store($travelData);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);

    $resultData = $result->resolve();
    expect($resultData['user_id'])->toBe($user->id)
        ->and($resultData['name'])->toBe('Viagem para Londres')
        ->and($resultData['country'])->toBe('Reino Unido')
        ->and($resultData['town'])->toBeNull()
        ->and($resultData['state'])->toBeNull()
        ->and($resultData['region'])->toBeNull();
});

test('travel request service sets default status to pending', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelData = [
        'user_id' => $user->id,
        'name' => 'Viagem para Tóquio',
        'country' => 'Japão',
        'departure_date' => '2024-08-01',
        'return_date' => '2024-08-15',
    ];

    $result = $this->userTravelRequestService->store($travelData);

    $resultData = $result->resolve();
    expect($resultData['status'])->toBe(TravelRequestStatus::PENDING->value);

    // Verificar no banco de dados
    $this->assertDatabaseHas('travel_requests', [
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);
});

test('travel request service returns travel request details for valid user', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Barcelona',
        'country' => 'Espanha',
        'status' => TravelRequestStatus::PENDING,
    ]);

    $result = $this->userTravelRequestService->getDetails($user->id, $travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequestResource::class);

    $resultData = $result->resolve();
    expect($resultData['id'])->toBe($travelRequest->id)
        ->and($resultData['user_id'])->toBe($user->id)
        ->and($resultData['name'])->toBe('Viagem para Barcelona')
        ->and($resultData['country'])->toBe('Espanha')
        ->and($resultData['status'])->toBe(TravelRequestStatus::PENDING->value);
});

test('travel request service throws exception for non-existent travel request', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    expect(fn () => $this->userTravelRequestService->getDetails($user->id, 999))
        ->toThrow(ModelNotFoundException::class);
});

test('travel request service returns correct resource structure', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Roma',
        'country' => 'Itália',
        'town' => 'Roma',
        'state' => 'Lazio',
        'region' => 'Centro',
        'departure_date' => '2024-09-01',
        'return_date' => '2024-09-07',
        'status' => TravelRequestStatus::APPROVED,
    ]);

    $result = $this->userTravelRequestService->getDetails($user->id, $travelRequest->id);

    $resultData = $result->resolve();
    expect($resultData)->toHaveKeys([
        'id',
        'user_id',
        'name',
        'country',
        'town',
        'state',
        'region',
        'departure_date',
        'return_date',
        'status',
        'created_at',
        'updated_at',
    ]);
});

test('travel request service handles repository errors gracefully', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    // Mock do repositório para simular erro
    $mockRepository = Mockery::mock(UserTravelRequestRepositoryInterface::class);
    $mockRepository->shouldReceive('getDetails')
        ->once()
        ->andReturn(null);

    $service = new UserTravelRequestService($mockRepository, $this->userRepository);

    expect(fn () => $service->getDetails($user->id, 999))
        ->toThrow(NotFoundHttpException::class);
});

test('travel request service getDetails validates user ownership correctly', function () {
    $user1 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $user2 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Viagem para Berlim',
        'country' => 'Alemanha',
    ]);

    // User1 tentando acessar travel request do User2
    expect(fn () => $this->userTravelRequestService->getDetails($user1->id, $travelRequest->id))
        ->toThrow(ModelNotFoundException::class);
});

test('travel request service getDetails handles different travel request statuses', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $pendingRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    $approvedRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::APPROVED,
    ]);

    $cancelledRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::CANCELLED,
    ]);

    $pendingResult = $this->userTravelRequestService->getDetails($user->id, $pendingRequest->id);
    $approvedResult = $this->userTravelRequestService->getDetails($user->id, $approvedRequest->id);
    $cancelledResult = $this->userTravelRequestService->getDetails($user->id, $cancelledRequest->id);

    expect($pendingResult->resolve()['status'])->toBe(TravelRequestStatus::PENDING->value)
        ->and($approvedResult->resolve()['status'])->toBe(TravelRequestStatus::APPROVED->value)
        ->and($cancelledResult->resolve()['status'])->toBe(TravelRequestStatus::CANCELLED->value);
});

test('travel request service getDetails returns complete travel request data', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Amsterdã',
        'country' => 'Holanda',
        'town' => 'Amsterdã',
        'state' => 'Holanda do Norte',
        'region' => 'Europa',
        'departure_date' => '2024-10-01',
        'return_date' => '2024-10-08',
        'status' => TravelRequestStatus::PENDING,
    ]);

    $result = $this->userTravelRequestService->getDetails($user->id, $travelRequest->id);

    $resultData = $result->resolve();
    expect($resultData['id'])->toBe($travelRequest->id)
        ->and($resultData['user_id'])->toBe($user->id)
        ->and($resultData['name'])->toBe('Viagem para Amsterdã')
        ->and($resultData['country'])->toBe('Holanda')
        ->and($resultData['town'])->toBe('Amsterdã')
        ->and($resultData['state'])->toBe('Holanda do Norte')
        ->and($resultData['region'])->toBe('Europa')
        ->and($resultData['departure_date'])->toBe('2024-10-01')
        ->and($resultData['return_date'])->toBe('2024-10-08')
        ->and($resultData['status'])->toBe(TravelRequestStatus::PENDING->value);
});

test('travel request repository getDetails returns correct travel request', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Viena',
        'country' => 'Áustria',
    ]);

    $result = $this->userTravelRequestRepository->getDetails($user->id, $travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequest::class)
        ->and($result->id)->toBe($travelRequest->id)
        ->and($result->user_id)->toBe($user->id)
        ->and($result->name)->toBe('Viagem para Viena')
        ->and($result->country)->toBe('Áustria');
});

test('travel request repository getDetails throws exception for non-existent travel request', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    expect(fn () => $this->userTravelRequestRepository->getDetails($user->id, 999))
        ->toThrow(ModelNotFoundException::class);
});

test('travel request repository getDetails throws exception for non-existent travel request when user does not own it', function () {
    $user1 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $user2 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Viagem para Dublin',
        'country' => 'Irlanda',
    ]);

    // User1 tentando acessar travel request do User2
    expect(fn () => $this->userTravelRequestRepository->getDetails($user1->id, $travelRequest->id))
        ->toThrow(ModelNotFoundException::class);
});
