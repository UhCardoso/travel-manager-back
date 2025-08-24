<?php

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Eloquent\AdminTravelRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->repository = new AdminTravelRequestRepository(new TravelRequest);
    $this->admin = User::factory()->create(['role' => \App\Enums\UserRole::ADMIN->value]);
    $this->user = User::factory()->create(['role' => \App\Enums\UserRole::USER->value]);
});

test('repository gets all travel requests with pagination', function () {
    TravelRequest::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getAll([]);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->count())->toBe(3);
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

    $result = $this->repository->getAll([
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    expect($result->count())->toBe(1)
        ->and($result->first()->status->value)->toBe(TravelRequestStatus::PENDING->value);
});

test('repository gets all travel requests with user relationship', function () {
    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getAll([]);

    expect($result->first()->user)->toBeInstanceOf(User::class)
        ->and($result->first()->user->id)->toBe($this->user->id);
});

test('repository gets travel request details successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getDetails($travelRequest->id);

    expect($result)->toBeInstanceOf(TravelRequest::class)
        ->and($result->id)->toBe($travelRequest->id)
        ->and($result->user_id)->toBe($this->user->id);
});

test('repository returns null for non-existent travel request', function () {
    expect(fn () => $this->repository->getDetails(999))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('repository gets travel request details with user relationship', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getDetails($travelRequest->id);

    expect($result->user)->toBeInstanceOf(User::class)
        ->and($result->user->id)->toBe($this->user->id);
});

test('repository updates travel request status successfully', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $result = $this->repository->updateStatus($travelRequest->id, TravelRequestStatus::APPROVED->value);

    expect($result)->toBeInstanceOf(TravelRequest::class)
        ->and($result->status->value)->toBe(TravelRequestStatus::APPROVED->value);
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

    $result = $this->repository->getAll([
        'departure_date' => '2025-01-01',
    ]);

    expect($result->count())->toBe(2)
        ->and($result->first()->departure_date->format('Y-m-d'))->toBe('2025-01-01');
});

test('repository filters by return date range', function () {
    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'return_date' => '2025-01-07',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'return_date' => '2025-06-07',
    ]);

    $result = $this->repository->getAll([
        'return_date' => '2025-01-07',
    ]);

    expect($result->count())->toBe(1)
        ->and($result->first()->return_date->format('Y-m-d'))->toBe('2025-01-07');
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

    $result = $this->repository->getAll([
        'name' => 'Paris',
    ]);

    expect($result->count())->toBe(1)
        ->and($result->first()->name)->toBe('Viagem para Paris');
});

test('repository handles per_page parameter', function () {
    TravelRequest::factory()->count(10)->create([
        'user_id' => $this->user->id,
    ]);

    $result = $this->repository->getAll(['per_page' => 5]);

    expect($result->perPage())->toBe(5)
        ->and($result->count())->toBe(5);
});
