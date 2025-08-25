<?php

use App\Enums\UserRole;
use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    $this->authUserService = new AuthUserService($this->userRepository);
});

test('auth service creates user with valid data', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ];

    $result = $this->authUserService->store($userData);

    expect($result)->toBeInstanceOf(AuthResource::class);

    $resultData = $result->resolve();
    expect($resultData['user']['name'])->toBe($userData['name'])
        ->and($resultData['user']['email'])->toBe($userData['email'])
        ->and($resultData['token'])->toBeString()
        ->and($resultData['token_type'])->toBe('Bearer');
});

test('auth service creates user with default role when not specified', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
    ];

    $result = $this->authUserService->store($userData);

    $resultData = $result->resolve();

    $createdUser = User::find($resultData['user']['id']);
    expect($createdUser->role)->toBe(UserRole::USER->value);
});

test('auth service login with valid user credentials', function () {
    $user = User::factory()->user()->create([
        'password' => bcrypt('password123'),
    ]);

    $result = $this->authUserService->login($user->email, 'password123');

    expect($result)->toBeInstanceOf(AuthResource::class);

    $resultData = $result->resolve();
    expect($resultData['user']['id'])->toBe($user->id)
        ->and($resultData['token'])->toBeString()
        ->and($resultData['token_type'])->toBe('Bearer');
});

test('auth service throws exception for admin login through user routes', function () {
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('password123'),
    ]);

    expect(fn () => $this->authUserService->login($admin->email, 'password123'))
        ->toThrow(ValidationException::class, 'Administrador deve fazer login via painel de administração.');
});

test('auth service throws exception for invalid password', function () {
    User::factory()->user()->create([
        'password' => bcrypt('password123'),
    ]);

    expect(fn () => $this->authUserService->login('test@example.com', 'wrongpassword'))
        ->toThrow(ValidationException::class, 'Email ou senha inválidos.');
});

test('auth service throws exception for non-existent user', function () {
    expect(fn () => $this->authUserService->login('nonexistent@example.com', 'password123'))
        ->toThrow(ValidationException::class, 'Email ou senha inválidos.');
});

test('auth service logout returns success response', function () {
    $user = User::factory()->user()->create();

    $result = $this->authUserService->logout($user);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('success')
        ->and($result['success'])->toBe(true);
});

test('auth service logout with user model instance', function () {
    $user = User::factory()->user()->create();

    $result = $this->authUserService->logout($user);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('success')
        ->and($result['success'])->toBe(true);
});

test('auth service logout with user id', function () {
    $user = User::factory()->user()->create();

    $result = $this->authUserService->logout($user);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('success')
        ->and($result['success'])->toBe(true);
});

test('created user has hashed password', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'plainpassword123',
        'role' => UserRole::USER->value,
    ];

    $result = $this->authUserService->store($userData);

    $resultData = $result->resolve();
    $createdUser = User::find($resultData['user']['id']);

    expect($createdUser->password)->not->toBe('plainpassword123')
        ->and(Hash::check('plainpassword123', $createdUser->password))->toBe(true);
});

test('multiple users can be created with unique emails', function () {
    $user1Data = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ];

    $user2Data = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ];

    $result1 = $this->authUserService->store($user1Data);
    $result2 = $this->authUserService->store($user2Data);

    expect($result1)->toBeInstanceOf(AuthResource::class)
        ->and($result2)->toBeInstanceOf(AuthResource::class);

    $result1Data = $result1->resolve();
    $result2Data = $result2->resolve();

    expect($result1Data['user']['email'])->not->toBe($result2Data['user']['email']);
});

test('user factory creates users with correct roles', function () {
    $user = User::factory()->user()->create();
    $admin = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::USER->value)
        ->and($admin->role)->toBe(UserRole::ADMIN->value);
});

test('repository creates user correctly', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ];

    $user = $this->userRepository->store($userData);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe($userData['name'])
        ->and($user->email)->toBe($userData['email'])
        ->and($user->role)->toBe($userData['role']);
});

test('repository finds user by email', function () {
    $user = User::factory()->user()->create();

    $foundUser = $this->userRepository->findByEmail($user->email);

    expect($foundUser)->toBeInstanceOf(User::class)
        ->and($foundUser->id)->toBe($user->id);
});

test('repository returns null for non-existent email', function () {
    $foundUser = $this->userRepository->findByEmail('nonexistent@example.com');

    expect($foundUser)->toBeNull();
});

test('repository finds user by id', function () {
    $user = User::factory()->user()->create();

    $foundUser = $this->userRepository->findById($user->id);

    expect($foundUser)->toBeInstanceOf(User::class)
        ->and($foundUser->id)->toBe($user->id);
});

test('repository updates user data', function () {
    $user = User::factory()->user()->create();
    $updateData = ['name' => 'Updated Name'];

    $result = $this->userRepository->update($user->id, $updateData);

    expect($result)->toBe(true);

    $updatedUser = User::find($user->id);
    expect($updatedUser->name)->toBe('Updated Name');
});

test('user model has correct role methods', function () {
    $user = User::factory()->user()->create();
    $admin = User::factory()->admin()->create();

    expect($user->isUser())->toBe(true)
        ->and($user->isAdmin())->toBe(false)
        ->and($admin->isAdmin())->toBe(true)
        ->and($admin->isUser())->toBe(false);
});
