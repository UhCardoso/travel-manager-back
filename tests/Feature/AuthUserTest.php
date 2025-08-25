<?php

use App\Enums\UserRole;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {});

test('user can register with valid data', function () {
    $userData = [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/user/register', $userData);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Usuário criado com sucesso')
        ->assertJsonPath('data.user.name', 'João Silva')
        ->assertJsonPath('data.user.email', 'joao@example.com')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ],
        ]);

    expect($response->json('data.token'))->not->toBeEmpty();
});

test('user cannot register with invalid email format', function () {
    $userData = [
        'name' => 'João Silva',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/user/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot register with short password', function () {
    $userData = [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ];

    $response = $this->postJson('/api/user/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user cannot register with password confirmation mismatch', function () {
    $userData = [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'password' => 'password123',
        'password_confirmation' => 'differentpassword',
    ];

    $response = $this->postJson('/api/user/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user cannot register with duplicate email', function () {
    User::factory()->create([
        'email' => 'joao@example.com',
    ]);

    $userData = [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/user/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ]);

    $response = $this->postJson('/api/user/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login realizado com sucesso')
        ->assertJsonPath('data.user.email', 'user@example.com')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ],
        ]);

    expect($response->json('data.token'))->not->toBeEmpty();
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ]);

    $response = $this->postJson('/api/user/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Email ou senha inválidos.');
});

test('user cannot login with non-existent email', function () {
    $response = $this->postJson('/api/user/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Email ou senha inválidos.');
});

test('user cannot login with missing credentials', function () {
    $response = $this->postJson('/api/user/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('user cannot login with invalid email format', function () {
    $response = $this->postJson('/api/user/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot login with short password', function () {
    $response = $this->postJson('/api/user/login', [
        'email' => 'user@example.com',
        'password' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('admin cannot login through user routes', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/user/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Administrador deve fazer login via painel de administração.');
});

test('authenticated user can logout', function () {
    $user = User::factory()->user()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/user/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logout realizado com sucesso');
});

test('unauthenticated user cannot access protected user routes', function () {
    $response = $this->postJson('/api/user/logout');

    $response->assertStatus(401);
});

test('admin cannot access protected user routes', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/user/logout');

    $response->assertStatus(403);
});

test('user can access protected routes', function () {
    $user = User::factory()->user()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/user/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logout realizado com sucesso');
});

test('user registration creates user with default role', function () {
    $userData = [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/user/register', $userData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'maria@example.com',
        'role' => UserRole::USER->value,
    ]);
});

test('multiple users can register independently', function () {
    $user1Data = [
        'name' => 'User 1',
        'email' => 'user1@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $user2Data = [
        'name' => 'User 2',
        'email' => 'user2@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response1 = $this->postJson('/api/user/register', $user1Data);
    $response2 = $this->postJson('/api/user/register', $user2Data);

    $response1->assertStatus(201);
    $response2->assertStatus(201);

    expect($response1->json('data.token'))->not->toBe($response2->json('data.token'));
});
