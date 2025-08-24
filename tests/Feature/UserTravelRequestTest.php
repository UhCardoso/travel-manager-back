<?php

use App\Enums\TravelRequestStatus;
use App\Enums\UserRole;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => UserRole::USER->value]);
    $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
});

// POST /api/user/travel-request/create
test('user can create travel request with valid data', function () {
    Sanctum::actingAs($this->user);

    $travelRequestData = [
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'town' => 'Paris',
        'state' => 'Île-de-France',
        'region' => 'Europa',
        'departure_date' => '2025-01-01',
        'return_date' => '2025-01-07',
    ];

    $response = $this->postJson('/api/user/travel-request/create', $travelRequestData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Solicitação de viagem criada com sucesso',
            'data' => [
                'name' => 'Viagem para Paris',
                'country' => 'França',
                'status' => TravelRequestStatus::PENDING->value,
            ],
        ]);

    $this->assertDatabaseHas('travel_requests', [
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
        'status' => TravelRequestStatus::PENDING->value,
    ]);
});

test('user cannot create travel request without authentication', function () {
    $travelRequestData = [
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'departure_date' => '2025-01-01',
        'return_date' => '2025-01-07',
    ];

    $response = $this->postJson('/api/user/travel-request/create', $travelRequestData);

    $response->assertStatus(401);
});

test('user cannot create travel request with invalid data', function () {
    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/user/travel-request/create', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'country', 'departure_date', 'return_date']);
});

test('user cannot create travel request with return date before departure date', function () {
    Sanctum::actingAs($this->user);

    $travelRequestData = [
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'departure_date' => '2025-01-07',
        'return_date' => '2025-01-01',
    ];

    $response = $this->postJson('/api/user/travel-request/create', $travelRequestData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['return_date']);
});

// GET /api/user/travel-request/all
test('user can get all their travel requests', function () {
    Sanctum::actingAs($this->user);

    TravelRequest::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/user/travel-request/all');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'country',
                        'status',
                        'departure_date',
                        'return_date',
                    ],
                ],
                'links',
                'meta',
            ],
        ]);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(3);
});

test('user can filter travel requests by status', function () {
    Sanctum::actingAs($this->user);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response = $this->getJson('/api/user/travel-request/all?status=pending');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(1)
        ->and($responseData['data']['data'][0]['status'])->toBe(TravelRequestStatus::PENDING->value);
});

test('user can filter travel requests by departure date', function () {
    Sanctum::actingAs($this->user);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-01-01',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-06-01',
    ]);

    $response = $this->getJson('/api/user/travel-request/all?departure_date=2025-01-01');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(2)
        ->and($responseData['data']['data'][0]['departure_date'])->toBe('2025-01-01');
});

test('user can filter travel requests by name', function () {
    Sanctum::actingAs($this->user);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Londres',
    ]);

    $response = $this->getJson('/api/user/travel-request/all?name=Paris');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(1)
        ->and($responseData['data']['data'][0]['name'])->toBe('Viagem para Paris');
});

test('user cannot access another user travel requests', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create(['role' => UserRole::USER->value]);
    TravelRequest::factory()->count(3)->create([
        'user_id' => $otherUser->id,
    ]);

    $response = $this->getJson('/api/user/travel-request/all');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(0);
});

test('user cannot access travel requests without authentication', function () {
    $response = $this->getJson('/api/user/travel-request/all');

    $response->assertStatus(401);
});

// GET /api/user/travel-request/{id}/details
test('user can view their own travel request details', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Barcelona',
        'country' => 'Espanha',
        'town' => 'Barcelona',
        'state' => 'Catalunha',
        'region' => 'Europa',
        'departure_date' => '2025-08-01',
        'return_date' => '2025-08-07',
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->getJson("/api/user/travel-request/{$travelRequest->id}/details");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Solicitação de viagem encontrada com sucesso',
            'data' => [
                'id' => $travelRequest->id,
                'user_id' => $this->user->id,
                'name' => 'Viagem para Barcelona',
                'country' => 'Espanha',
                'town' => 'Barcelona',
                'state' => 'Catalunha',
                'region' => 'Europa',
                'departure_date' => '2025-08-01',
                'return_date' => '2025-08-07',
                'status' => TravelRequestStatus::PENDING->value,
            ],
        ]);
});

test('user cannot view another user travel request details', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create(['role' => UserRole::USER->value]);
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $response = $this->getJson("/api/user/travel-request/{$travelRequest->id}/details");

    $response->assertStatus(404);
});

test('user cannot view non-existent travel request details', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/user/travel-request/999/details');

    $response->assertStatus(404);
});

test('user cannot view travel request details without authentication', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/user/travel-request/{$travelRequest->id}/details");

    $response->assertStatus(401);
});

// PATCH /api/user/travel-request/{id}/cancel
test('user can cancel their own pending travel request', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Solicitação de viagem cancelada com sucesso',
            'data' => [
                'id' => $travelRequest->id,
                'status' => TravelRequestStatus::CANCELLED->value,
            ],
        ]);

    $this->assertDatabaseHas('travel_requests', [
        'id' => $travelRequest->id,
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);
});

test('user cannot cancel approved travel request', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response = $this->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(500);
});

test('user cannot cancel another user travel request', function () {
    Sanctum::actingAs($this->user);

    $otherUser = User::factory()->create(['role' => UserRole::USER->value]);
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $otherUser->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(404);
});

test('user cannot cancel with invalid status', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('user cannot cancel non-existent travel request', function () {
    Sanctum::actingAs($this->user);

    $response = $this->patchJson('/api/user/travel-request/999/cancel', [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(404);
});

test('user cannot cancel travel request without authentication', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(401);
});

// Middleware and Authorization Tests
test('admin cannot access user travel request routes', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->getJson('/api/user/travel-request/all');

    $response->assertStatus(403);
});

test('unauthenticated user gets 401 for all protected routes', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->getJson('/api/user/travel-request/all')->assertStatus(401);
    $this->getJson("/api/user/travel-request/{$travelRequest->id}/details")->assertStatus(401);
    $this->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ])->assertStatus(401);
    $this->postJson('/api/user/travel-request/create', [])->assertStatus(401);
});
