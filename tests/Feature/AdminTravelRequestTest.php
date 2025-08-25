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
    $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $this->user = User::factory()->create(['role' => UserRole::USER->value]);
    $this->regularUser = User::factory()->create(['role' => UserRole::USER->value]);
});

// GET /api/admin/travel-request/all
test('admin can get all travel requests', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    TravelRequest::factory()->count(2)->create([
        'user_id' => $this->regularUser->id,
    ]);

    $response = $this->getJson('/api/admin/travel-request/all');

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
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                    ],
                ],
                'links',
                'meta',
            ],
        ]);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(5);
});

test('admin can filter travel requests by status', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response = $this->getJson('/api/admin/travel-request/all?status=pending');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(1)
        ->and($responseData['data']['data'][0]['status'])->toBe(TravelRequestStatus::PENDING->value);
});

test('admin can filter travel requests by departure date', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-01-01',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'departure_date' => '2025-06-01',
    ]);

    $response = $this->getJson('/api/admin/travel-request/all?departure_date=2025-01-01');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(2)
        ->and($responseData['data']['data'][0]['departure_date'])->toBe('2025-01-01');
});

test('admin can filter travel requests by return date', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'return_date' => '2025-01-07',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'return_date' => '2025-06-07',
    ]);

    $response = $this->getJson('/api/admin/travel-request/all?return_date=2025-01-07');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(1)
        ->and($responseData['data']['data'][0]['return_date'])->toBe('2025-01-07');
});

test('admin can filter travel requests by name', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
    ]);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Londres',
    ]);

    $response = $this->getJson('/api/admin/travel-request/all?name=Paris');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(1)
        ->and($responseData['data']['data'][0]['name'])->toBe('Viagem para Paris');
});

test('admin can set custom per_page parameter', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->count(10)->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/admin/travel-request/all?per_page=5');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'])->toHaveCount(5)
        ->and($responseData['data']['meta']['per_page'])->toBe(5);
});

test('admin can see user information in travel requests', function () {
    Sanctum::actingAs($this->admin);

    TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Viagem para Paris',
    ]);

    $response = $this->getJson('/api/admin/travel-request/all');

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['data'][0]['user'])->toHaveKey('id')
        ->and($responseData['data']['data'][0]['user'])->toHaveKey('name')
        ->and($responseData['data']['data'][0]['user'])->toHaveKey('email');
});

test('admin cannot access without authentication', function () {
    $response = $this->getJson('/api/admin/travel-request/all');

    $response->assertStatus(401);
});

test('regular user cannot access admin routes', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/admin/travel-request/all');

    $response->assertStatus(403);
});

// GET /api/admin/travel-request/{id}/details
test('admin can view any travel request details', function () {
    Sanctum::actingAs($this->admin);

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

    $response = $this->getJson("/api/admin/travel-request/{$travelRequest->id}/details");

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

test('admin can see user information in travel request details', function () {
    Sanctum::actingAs($this->admin);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/admin/travel-request/{$travelRequest->id}/details");

    $response->assertStatus(200);

    $responseData = $response->json();
    expect($responseData['data']['user'])->toHaveKey('id')
        ->and($responseData['data']['user'])->toHaveKey('name')
        ->and($responseData['data']['user'])->toHaveKey('email');
});

test('admin cannot view non-existent travel request details', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->getJson('/api/admin/travel-request/999/details');

    $response->assertStatus(404);
});

test('admin cannot view travel request details without authentication', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/admin/travel-request/{$travelRequest->id}/details");

    $response->assertStatus(401);
});

test('regular user cannot view travel request details through admin route', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->regularUser->id,
    ]);

    $response = $this->getJson("/api/admin/travel-request/{$travelRequest->id}/details");

    $response->assertStatus(403);
});

// PATCH /api/admin/travel-request/{id}/update
test('admin can approve travel request', function () {
    Sanctum::actingAs($this->admin);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Status da solicitação de viagem atualizado com sucesso',
            'data' => [
                'id' => $travelRequest->id,
                'status' => TravelRequestStatus::APPROVED->value,
            ],
        ]);

    $this->assertDatabaseHas('travel_requests', [
        'id' => $travelRequest->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);
});

test('admin can cancel travel request', function () {
    Sanctum::actingAs($this->admin);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Status da solicitação de viagem atualizado com sucesso',
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

test('admin cannot change status from approved to cancelled', function () {
    Sanctum::actingAs($this->admin);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);

    $response->assertStatus(500)
        ->assertJsonPath('message', 'Solicitação de viagem já aprovada e não pode ser cancelada');
});

test('admin cannot update with invalid status', function () {
    Sanctum::actingAs($this->admin);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('admin cannot update non-existent travel request', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->patchJson('/api/admin/travel-request/999/update', [
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response->assertStatus(404);
});

test('admin cannot update travel request without authentication', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response->assertStatus(401);
});

test('regular user cannot update travel request through admin route', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->regularUser->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::APPROVED->value,
    ]);

    $response->assertStatus(403);
});

test('admin cannot update without status parameter', function () {
    Sanctum::actingAs($this->admin);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
        'status' => TravelRequestStatus::PENDING->value,
    ]);

    $response = $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

// Middleware and Authorization Tests
test('unauthenticated user gets 401 for all admin protected routes', function () {
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->getJson('/api/admin/travel-request/all')->assertStatus(401);
    $this->getJson("/api/admin/travel-request/{$travelRequest->id}/details")->assertStatus(401);
    $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::APPROVED->value,
    ])->assertStatus(401);
});

test('regular user gets 403 for all admin routes', function () {
    Sanctum::actingAs($this->user);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $this->regularUser->id,
    ]);

    $this->getJson('/api/admin/travel-request/all')->assertStatus(403);
    $this->getJson("/api/admin/travel-request/{$travelRequest->id}/details")->assertStatus(403);
    $this->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
        'status' => TravelRequestStatus::APPROVED->value,
    ])->assertStatus(403);
});
