<?php

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Eloquent\UserTravelRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->repository = new UserTravelRequestRepository(new TravelRequest);
    $this->user = User::factory()->create();
});

test('repository stores travel request successfully', function () {
    $travelRequestData = [
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'town' => 'Paris',
        'state' => 'Île-de-France',
        'region' => 'Europa',
        'departure_date' => '2025-01-01',
        'return_date' => '2025-01-07',
        'status' => TravelRequestStatus::PENDING->value,
    ];

    $travelRequest = $this->repository->store($travelRequestData);

    expect($travelRequest)->toBeInstanceOf(TravelRequest::class)
        ->and($travelRequest->name)->toBe('Viagem para Paris')
        ->and($travelRequest->user_id)->toBe($this->user->id)
        ->and($travelRequest->status->value)->toBe(TravelRequestStatus::PENDING->value);
});

test('repository gets all travel requests with pagination', function () {
    TravelRequest::factory()->count(5)->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getAll($this->user->id, []);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->count())->toBe(5)
        ->and($result->first()->user_id)->toBe($this->user->id);
});

test('repository gets all travel requests with filters', function () {
    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $result = $this->repository->getAll($this->user->id, [
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    expect($result->count())->toBe(1)
        ->and($result->first()->status->value)->toBe(TravelRequestStatus::PENDING->value);
});

test('repository gets travel request details successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getDetails($this->user->id, $travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequest::class)
        ->and($result->id)->toBe($travelRequest->id)
        ->and($result->user_id)->toBe($this->user->id);
});

test('repository returns null for non-existent travel request', function () {
    expect(fn () => $this->repository->getDetails($this->user->id, 999))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('repository returns null for travel request of another user', function () {
    $otherUser = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    expect(fn () => $this->repository->getDetails($this->user->id, $travelRequest->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('repository cancels travel request successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $result = $this->repository->cancel($this->user->id, $travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequest::class)
        ->and($result->status->value)->toBe(TravelRequestStatus::CANCELLED->value);
});

test('repository throws exception when cancelling approved travel request', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    expect(fn () => $this->repository->cancel($this->user->id, $travelRequest->id))
        ->toThrow(\InvalidArgumentException::class, 'Não é possível cancelar uma solicitação aprovada.');
});

test('repository throws exception when cancelling another user travel request', function () {
    $otherUser = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $otherUser->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    expect(fn () => $this->repository->cancel($this->user->id, $travelRequest->id))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('repository filters by departure date range', function () {
    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-01-01',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-06-01',
    ]);

    $result = $this->repository->getAll($this->user->id, [
        'departure_date' => '2025-01-01',
    ]);

    expect($result->count())->toBe(2)
        ->and($result->first()->departure_date->format('Y-m-d'))->toBe('2025-01-01');
});

test('repository filters by name search', function () {
    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Londres',
    ]);

    $result = $this->repository->getAll($this->user->id, [
        'name' => 'Paris',
    ]);

    expect($result->count())->toBe(1)
        ->and($result->first()->name)->toBe('Viagem para Paris');
});
