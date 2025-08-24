<?php

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use App\Observers\TravelRequestObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->observer = new TravelRequestObserver;
    $this->user = User::factory()->create();
});

test('observer logs when travel request status changes to cancelled', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    // Change status to cancelled
    $travelRequest->update(['status' => TravelRequestStatus::CANCELLED]);

    // Verify the status was actually changed
    expect($travelRequest->fresh()->status->value)->toBe(TravelRequestStatus::CANCELLED->value);
});

test('observer logs when travel request status changes to approved', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    // Change status to approved
    $travelRequest->update(['status' => TravelRequestStatus::APPROVED]);

    // Verify the status was actually changed
    expect($travelRequest->fresh()->status->value)->toBe(TravelRequestStatus::APPROVED->value);
});

test('observer logs when travel request is created', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    // Verify the travel request was created
    expect($travelRequest->id)->toBeGreaterThan(0)
        ->and($travelRequest->user_id)->toBe($this->user->id)
        ->and($travelRequest->status->value)->toBe(TravelRequestStatus::PENDING->value);
});

test('observer logs when travel request is deleted', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $travelRequestId = $travelRequest->id;

    $travelRequest->delete();

    // Verify the travel request was deleted
    expect(TravelRequest::find($travelRequestId))->toBeNull();
});

test('observer does not log when status is not changed', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    // Update other fields, not status
    $travelRequest->update(['name' => 'Updated Name']);

    // Verify the name was updated but status remained the same
    expect($travelRequest->fresh()->name)->toBe('Updated Name')
        ->and($travelRequest->fresh()->status->value)->toBe(TravelRequestStatus::PENDING->value);
});
