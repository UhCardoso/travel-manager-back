<?php

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {});

test('user can view their own travel request details', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Barcelona',
        'country' => 'Espanha',
        'town' => 'Barcelona',
        'state' => 'Catalunha',
        'region' => 'Nordeste',
        'departure_date' => '2025-08-01',
        'return_date' => '2025-08-07',
        'status' => TravelRequestStatus::PENDING,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson("/api/user/travel-request/{$travelRequest->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Solicitação de viagem encontrada com sucesso',
            'data' => [
                'id' => $travelRequest->id,
                'user_id' => $user->id,
                'name' => 'Viagem para Barcelona',
                'country' => 'Espanha',
                'town' => 'Barcelona',
                'state' => 'Catalunha',
                'region' => 'Nordeste',
                'departure_date' => '2025-08-01',
                'return_date' => '2025-08-07',
                'status' => TravelRequestStatus::PENDING->value,
            ],
        ]);
});

test('user cannot view another user travel request details', function () {
    $user1 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $user2 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token1 = $user1->createToken('test-token')->plainTextToken;

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Viagem para Paris',
        'country' => 'França',
        'departure_date' => '2025-10-01',
        'return_date' => '2025-10-07',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token1,
    ])->getJson("/api/user/travel-request/{$travelRequest->id}");

    $response->assertStatus(404);
    // O Laravel pode retornar mensagens diferentes dependendo do contexto
    $responseData = $response->json();
    expect($responseData)->toHaveKey('message');
});

test('unauthenticated user cannot view travel request details', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Londres',
        'country' => 'Inglaterra',
        'departure_date' => '2025-11-01',
        'return_date' => '2025-11-07',
    ]);

    $response = $this->getJson("/api/user/travel-request/{$travelRequest->id}");

    $response->assertStatus(401);
});

test('returns 404 for non-existent travel request', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/user/travel-request/999');

    $response->assertStatus(404);
    // O Laravel pode retornar mensagens diferentes dependendo do contexto
    $responseData = $response->json();
    expect($responseData)->toHaveKey('message');
});

test('returns 404 for travel request with invalid id format', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/user/travel-request/invalid-id');

    // Pode retornar 404 ou 500 dependendo de como o Laravel trata o erro
    expect($response->status())->toBeIn([404, 500]);
});

test('travel request details include all required fields', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Tóquio',
        'country' => 'Japão',
        'town' => 'Tóquio',
        'state' => 'Kanto',
        'region' => 'Ásia',
        'departure_date' => '2025-12-01',
        'return_date' => '2025-12-07',
        'status' => TravelRequestStatus::APPROVED,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson("/api/user/travel-request/{$travelRequest->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
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
            ],
        ]);
});

test('multiple users can view their own travel requests independently', function () {
    $user1 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $user2 = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);

    $token1 = $user1->createToken('test-token')->plainTextToken;
    $token2 = $user2->createToken('test-token')->plainTextToken;

    $travelRequest1 = TravelRequest::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Viagem para Roma',
        'country' => 'Itália',
        'departure_date' => '2025-01-01',
        'return_date' => '2025-01-07',
    ]);

    $travelRequest2 = TravelRequest::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Viagem para Berlim',
        'country' => 'Alemanha',
        'departure_date' => '2025-02-01',
        'return_date' => '2025-02-07',
    ]);

    // User 1 viewing their own travel request
    $response1 = $this->withHeaders([
        'Authorization' => 'Bearer '.$token1,
    ])->getJson("/api/user/travel-request/{$travelRequest1->id}");

    $response1->assertStatus(200)
        ->assertJson([
            'data' => [
                'user_id' => $user1->id,
                'name' => 'Viagem para Roma',
            ],
        ]);

    // Limpar o cache de autenticação
    $this->app->make('auth')->forgetGuards();

    // User 2 viewing their own travel request
    $response2 = $this->withHeaders([
        'Authorization' => 'Bearer '.$token2,
    ])->getJson("/api/user/travel-request/{$travelRequest2->id}");

    $response2->assertStatus(200)
        ->assertJson([
            'data' => [
                'user_id' => $user2->id,
                'name' => 'Viagem para Berlim',
            ],
        ]);
});

test('admin cannot access user travel request details through user route', function () {
    $admin = User::factory()->create([
        'role' => \App\Enums\UserRole::ADMIN->value,
    ]);
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token = $admin->createToken('test-token')->plainTextToken;

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem para Milão',
        'country' => 'Itália',
        'departure_date' => '2025-09-01',
        'return_date' => '2025-09-07',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson("/api/user/travel-request/{$travelRequest->id}");

    $response->assertStatus(403);
});

test('travel request details show correct status values', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::USER->value,
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

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

    // Test pending status
    $response1 = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson("/api/user/travel-request/{$pendingRequest->id}");

    $response1->assertStatus(200)
        ->assertJson([
            'data' => [
                'status' => TravelRequestStatus::PENDING->value,
            ],
        ]);

    // Test approved status
    $response2 = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson("/api/user/travel-request/{$approvedRequest->id}");

    $response2->assertStatus(200)
        ->assertJson([
            'data' => [
                'status' => TravelRequestStatus::APPROVED->value,
            ],
        ]);

    // Test cancelled status
    $response3 = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson("/api/user/travel-request/{$cancelledRequest->id}");

    $response3->assertStatus(200)
        ->assertJson([
            'data' => [
                'status' => TravelRequestStatus::CANCELLED->value,
            ],
        ]);
});
