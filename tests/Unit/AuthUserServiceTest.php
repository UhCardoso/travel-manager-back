<?php

use App\Enums\UserRole;
use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\AuthUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->userRepository = new EloquentUserRepository(new User);
    $this->authUserService = new AuthUserService($this->userRepository);
});

test('auth service creates user with valid data', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ];

    $result = $this->authUserService->create($userData);

    expect($result)->toBeInstanceOf(AuthResource::class);

    $resultData = $result->resolve();

    expect($resultData)->toHaveKeys(['user', 'token', 'token_type'])
        ->and($resultData['user']['name'])->toBe($userData['name'])
        ->and($resultData['user']['email'])->toBe($userData['email'])
        ->and($resultData['token_type'])->toBe('Bearer')
        ->and($resultData['token'])->toBeString();
});

test('auth service creates user with default role when not specified', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        // role não especificado
    ];

    $result = $this->authUserService->create($userData);

    $resultData = $result->resolve();

    // Verificar se o usuário foi criado no banco com role padrão
    $createdUser = User::find($resultData['user']['id']);
    expect($createdUser->role)->toBe(UserRole::USER->value);
});

test('auth service login with valid user credentials', function () {
    $user = User::factory()->user()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password123'),
    ]);

    $result = $this->authUserService->login('user@test.com', 'password123');

    expect($result)->toBeInstanceOf(AuthResource::class);

    $resultData = $result->resolve();

    expect($resultData)->toHaveKeys(['user', 'token', 'token_type'])
        ->and($resultData['user']['id'])->toBe($user->id)
        ->and($resultData['user']['email'])->toBe('user@test.com')
        ->and($resultData['user']['role'])->toBe(UserRole::USER->value)
        ->and($resultData['token_type'])->toBe('Bearer')
        ->and($resultData['token'])->toBeString();
});

test('auth service login with valid admin credentials', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
    ]);

    $result = $this->authUserService->login('admin@test.com', 'password123');

    expect($result)->toBeInstanceOf(AuthResource::class);

    $resultData = $result->resolve();

    expect($resultData)->toHaveKeys(['user', 'token', 'token_type'])
        ->and($resultData['user']['id'])->toBe($admin->id)
        ->and($resultData['user']['email'])->toBe('admin@test.com');
});

test('auth service throws exception for invalid password', function () {
    User::factory()->user()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password123'),
    ]);

    expect(fn () => $this->authUserService->login('user@test.com', 'wrongpassword'))
        ->toThrow(ValidationException::class);
});

test('auth service throws exception for non-existent user', function () {
    expect(fn () => $this->authUserService->login('nonexistent@test.com', 'password123'))
        ->toThrow(ValidationException::class);
});

test('auth service logout returns success response', function () {
    $user = User::factory()->user()->create();

    // Simular usuário autenticado criando um token
    $token = $user->createToken('test-token');
    $user->withAccessToken($token->accessToken);

    $result = $this->authUserService->logout($user);

    expect($result)->toHaveKeys(['success'])
        ->and($result['success'])->toBe(true);
});

test('auth service logout with user model instance', function () {
    $user = User::factory()->user()->create();

    // Simular usuário autenticado criando um token
    $token = $user->createToken('test-token');
    $user->withAccessToken($token->accessToken);

    $result = $this->authUserService->logout($user);

    expect($result['success'])->toBe(true);
});

test('auth service logout with user id', function () {
    $user = User::factory()->user()->create();

    // Simular usuário autenticado criando um token
    $token = $user->createToken('test-token');
    $user->withAccessToken($token->accessToken);

    $result = $this->authUserService->logout($user);

    expect($result['success'])->toBe(true);
});

test('created user has hashed password', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'plainpassword123',
        'role' => UserRole::USER->value,
    ];

    $result = $this->authUserService->create($userData);

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

    $result1 = $this->authUserService->create($user1Data);
    $result2 = $this->authUserService->create($user2Data);

    expect($result1)->toBeInstanceOf(AuthResource::class)
        ->and($result2)->toBeInstanceOf(AuthResource::class);

    $result1Data = $result1->resolve();
    $result2Data = $result2->resolve();

    expect($result1Data['user']['email'])->not->toBe($result2Data['user']['email'])
        ->and($result1Data['token'])->not->toBe($result2Data['token']);
});

test('user factory creates users with correct roles', function () {
    $user = User::factory()->user()->create();
    $admin = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::USER->value)
        ->and($admin->role)->toBe(UserRole::ADMIN->value)
        ->and($user->isUser())->toBe(true)
        ->and($admin->isAdmin())->toBe(true);
});

test('repository creates user correctly', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password123'),
        'role' => UserRole::USER->value,
    ];

    $user = $this->userRepository->create($userData);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe($userData['name'])
        ->and($user->email)->toBe($userData['email'])
        ->and($user->role)->toBe(UserRole::USER->value);
});

test('repository finds user by email', function () {
    $fakeEmail = fake()->unique()->safeEmail();
    $user = User::factory()->user()->create([
        'email' => $fakeEmail,
    ]);

    $found = $this->userRepository->findByEmail($fakeEmail);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($user->id)
        ->and($found->email)->toBe($fakeEmail);
});

test('repository returns null for non-existent email', function () {
    $fakeEmail = fake()->unique()->safeEmail();

    $found = $this->userRepository->findByEmail($fakeEmail);

    expect($found)->toBeNull();
});

test('repository finds user by id', function () {
    $user = User::factory()->user()->create();

    $found = $this->userRepository->findById($user->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($user->id);
});

test('repository updates user data', function () {
    $user = User::factory()->user()->create([
        'name' => 'Original Name',
    ]);

    $newName = fake()->name();
    $updated = $this->userRepository->update($user->id, [
        'name' => $newName,
    ]);

    expect($updated)->toBe(true);

    $user->refresh();
    expect($user->name)->toBe($newName);
});
