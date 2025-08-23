<?php

use App\Enums\UserRole;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {});

test('admin can login with valid credentials', function () {
    $admin = User::factory()->create([
        'email' => 'admin@admin.com',
        'password' => bcrypt('admin123'),
        'role' => UserRole::ADMIN->value,
    ]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@admin.com',
        'password' => 'admin123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login realizado com sucesso')
        ->assertJsonPath('data.user.email', 'admin@admin.com')
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

test('admin cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'admin@admin.com',
        'password' => bcrypt('admin123'),
        'role' => UserRole::ADMIN->value,
    ]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@admin.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Email ou senha incorretos.');
});

test('regular user cannot login as admin', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login realizado com sucesso');
});

test('admin cannot login with missing credentials', function () {
    $response = $this->postJson('/api/admin/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('admin cannot login with invalid email format', function () {
    $response = $this->postJson('/api/admin/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('admin cannot login with short password', function () {
    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@admin.com',
        'password' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('authenticated admin can logout', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('unauthenticated user cannot access protected admin routes', function () {
    $response = $this->postJson('/api/admin/logout');

    $response->assertStatus(401);
});

test('regular user cannot access admin routes', function () {
    $user = User::factory()->user()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/admin/logout');

    $response->assertStatus(403);
});

test('admin can access protected routes', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('admin login returns proper user data structure', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Admin User',
        'email' => 'admin@admin.com',
        'password' => bcrypt('admin123'),
    ]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@admin.com',
        'password' => 'admin123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.user.name', 'Admin User')
        ->assertJsonPath('data.user.email', 'admin@admin.com');
});

test('admin login creates valid sanctum token', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@admin.com',
        'password' => bcrypt('admin123'),
    ]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@admin.com',
        'password' => 'admin123',
    ]);

    $token = $response->json('data.token');

    expect($token)->toBeString()
        ->and(strlen($token))->toBeGreaterThanOrEqual(50);
});

test('multiple admin users can login independently', function () {
    $admin1 = User::factory()->admin()->create([
        'email' => 'admin1@admin.com',
        'password' => bcrypt('password1'),
    ]);

    $admin2 = User::factory()->admin()->create([
        'email' => 'admin2@admin.com',
        'password' => bcrypt('password2'),
    ]);

    $response1 = $this->postJson('/api/admin/login', [
        'email' => 'admin1@admin.com',
        'password' => 'password1',
    ]);

    $response2 = $this->postJson('/api/admin/login', [
        'email' => 'admin2@admin.com',
        'password' => 'password2',
    ]);

    $response1->assertStatus(200)->assertJsonPath('success', true);
    $response2->assertStatus(200)->assertJsonPath('success', true);

    expect($response1->json('data.token'))->not->toBe($response2->json('data.token'));
});
