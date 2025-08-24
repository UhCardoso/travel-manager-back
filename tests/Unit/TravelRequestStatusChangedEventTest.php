<?php

use App\Events\TravelRequestStatusChanged;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

test('event is created with correct data for approval', function () {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $event = new TravelRequestStatusChanged($travelRequest, 'approve');

    expect($event->type)->toBe('approve');
    expect($event->message)->toBe("Seu pedido de ID {$travelRequest->id} foi aprovado");
    expect($event->travelRequest->id)->toBe($travelRequest->id);
});

test('event is created with correct data for cancellation', function () {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $event = new TravelRequestStatusChanged($travelRequest, 'cancelled');

    expect($event->type)->toBe('cancelled');
    expect($event->message)->toBe("Seu pedido de ID {$travelRequest->id} foi reprovado");
    expect($event->travelRequest->id)->toBe($travelRequest->id);
});

test('event broadcasts to correct channel', function () {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $event = new TravelRequestStatusChanged($travelRequest, 'approve');
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-user.{$user->id}");
});

test('event has correct broadcast data', function () {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'approved',
    ]);

    $event = new TravelRequestStatusChanged($travelRequest, 'approve');
    $broadcastData = $event->broadcastWith();

    expect($broadcastData)->toHaveKey('id');
    expect($broadcastData)->toHaveKey('type');
    expect($broadcastData)->toHaveKey('message');
    expect($broadcastData)->toHaveKey('status');
    expect($broadcastData)->toHaveKey('user_id');
    expect($broadcastData)->toHaveKey('timestamp');

    expect($broadcastData['id'])->toBe($travelRequest->id);
    expect($broadcastData['type'])->toBe('approve');
    expect($broadcastData['user_id'])->toBe($user->id);
    expect($broadcastData['status'])->toBe('approved');
});

test('event has correct broadcast name', function () {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $event = new TravelRequestStatusChanged($travelRequest, 'cancelled');

    expect($event->broadcastAs())->toBe('travel-request-status-changed');
});
