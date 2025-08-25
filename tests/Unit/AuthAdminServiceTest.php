<?php

use App\Enums\UserRole;
use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\AuthAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    $this->userRepository = new EloquentUserRepository(new User);
    $this->authAdminService = new AuthAdminService($this->userRepository);
});

test('user role enum has correct values', function () {
    expect(UserRole::ADMIN->value)->toBe('admin')
        ->and(UserRole::USER->value)->toBe('user');
});

test('user role enum forSelect method returns array', function () {
    $options = UserRole::forSelect();

    expect($options)->toBeArray()
        ->and($options)->toHaveKeys(['admin', 'user'])
        ->and($options['admin'])->toBe('Administrador')
        ->and($options['user'])->toBe('Usuário');
});

test('user role enum label method returns correct labels', function () {
    expect(UserRole::ADMIN->label())->toBe('Administrador')
        ->and(UserRole::USER->label())->toBe('Usuário');
});

test('user role enum values method returns all roles', function () {
    $values = UserRole::values();

    expect($values)->toBeArray()
        ->and($values)->toContain('admin')
        ->and($values)->toContain('user')
        ->and($values)->toHaveCount(2);
});

test('user factory creates admin users with faker data', function () {
    $admin = User::factory()->admin()->create([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt(fake()->password()),
    ]);

    expect($admin)->toBeInstanceOf(User::class)
        ->and($admin->role)->toBe(UserRole::ADMIN->value)
        ->and($admin->isAdmin())->toBe(true)
        ->and($admin->name)->toBeString()
        ->and($admin->email)->toBeString()
        ->and(str_contains($admin->email, '@'))->toBe(true);
});

test('user factory creates regular users with faker data', function () {
    $user = User::factory()->user()->create([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt(fake()->password()),
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->role)->toBe(UserRole::USER->value)
        ->and($user->isUser())->toBe(true)
        ->and($user->name)->toBeString()
        ->and($user->email)->toBeString();
});

test('repository creates user with faker data', function () {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt(fake()->password()),
        'role' => UserRole::ADMIN->value,
    ];

    $user = User::create($userData);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe($userData['name'])
        ->and($user->email)->toBe($userData['email'])
        ->and($user->role)->toBe(UserRole::ADMIN->value);
});

test('repository finds user by email with faker data', function () {
    $fakeEmail = fake()->unique()->safeEmail();
    $admin = User::factory()->admin()->create([
        'email' => $fakeEmail,
    ]);

    $found = $this->userRepository->findByEmail($fakeEmail);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($admin->id)
        ->and($found->email)->toBe($fakeEmail);
});

test('repository returns null for non-existent email', function () {
    $fakeEmail = fake()->unique()->safeEmail();

    $found = $this->userRepository->findByEmail($fakeEmail);

    expect($found)->toBeNull();
});

test('repository finds user by id', function () {
    $admin = User::factory()->admin()->create();

    $found = $this->userRepository->findById($admin->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($admin->id);
});

test('repository updates user data with faker data', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Original Name',
    ]);

    $newName = fake()->name();
    $updated = $this->userRepository->update($admin->id, [
        'name' => $newName,
    ]);

    expect($updated)->toBe(true);

    $admin->refresh();
    expect($admin->name)->toBe($newName);
});

test('repository deletes user', function () {
    $admin = User::factory()->admin()->create();
    $adminId = $admin->id;

    $deleted = $admin->delete();

    expect($deleted)->toBe(true);

    $found = User::find($adminId);
    expect($found)->toBeNull();
});

test('repository finds admin users only', function () {
    User::factory()->admin()->count(3)->create();
    User::factory()->user()->count(2)->create();

    $admins = User::where('role', UserRole::ADMIN->value)->get();

    expect($admins)->toHaveCount(3);

    foreach ($admins as $admin) {
        expect($admin->role)->toBe(UserRole::ADMIN->value);
    }
});

test('user model isAdmin method works correctly', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    expect($admin->isAdmin())->toBe(true)
        ->and($user->isAdmin())->toBe(false);
});

test('user model isUser method works correctly', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    expect($admin->isUser())->toBe(false)
        ->and($user->isUser())->toBe(true);
});

test('auth service login with valid admin credentials', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
    ]);

    $result = $this->authAdminService->login('admin@test.com', 'password123');

    expect($result)->toBeInstanceOf(AuthResource::class);

    $resultData = $result->resolve();

    expect($resultData)->toHaveKeys(['user', 'token', 'token_type'])
        ->and($resultData['user']['id'])->toBe($admin->id)
        ->and($resultData['user']['email'])->toBe('admin@test.com')
        ->and($resultData['user']['role'])->toBe(UserRole::ADMIN->value)
        ->and($resultData['token_type'])->toBe('Bearer')
        ->and($resultData['token'])->toBeString();
});

test('auth service throws exception for non-admin user', function () {
    User::factory()->user()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password123'),
    ]);

    expect(fn () => $this->authAdminService->login('user@test.com', 'password123'))
        ->toThrow(ValidationException::class, 'Usuário não é administrador.');
});

test('auth service throws exception for invalid password', function () {
    User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
    ]);

    expect(fn () => $this->authAdminService->login('admin@test.com', 'wrongpassword'))
        ->toThrow(ValidationException::class);
});

test('auth service throws exception for non-existent user', function () {
    expect(fn () => $this->authAdminService->login('nonexistent@test.com', 'password123'))
        ->toThrow(ValidationException::class);
});

test('auth service logout returns success response', function () {
    $admin = User::factory()->admin()->create();

    $token = $admin->createToken('test-token');
    $admin->withAccessToken($token->accessToken);

    $result = $this->authAdminService->logout($admin);

    expect($result)->toHaveKeys(['success'])
        ->and($result['success'])->toBe(true);
});

test('multiple users can be created with unique faker data', function () {
    $users = User::factory()->count(5)->create();

    expect($users)->toHaveCount(5);

    $emails = $users->pluck('email')->toArray();
    expect($emails)->toHaveCount(5);

    expect(array_unique($emails))->toHaveCount(5);

    foreach ($users as $user) {
        expect($user)->toBeInstanceOf(User::class)
            ->and($user->name)->toBeString()
            ->and($user->email)->toBeString()
            ->and($user->role)->toBeString();
    }
});
