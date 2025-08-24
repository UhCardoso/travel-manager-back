<?php

use App\Enums\TravelRequestStatus;
use App\Enums\UserRole;
use App\Mail\TravelRequestApproved;
use App\Mail\TravelRequestCancelled;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    Mail::fake();
});

test('admin user is created with correct role', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

    expect($admin->role)->toBe(UserRole::ADMIN->value);
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->hasRole('admin'))->toBeTrue();
});

test('admin approving travel request sends email to user', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $user = User::factory()->create(['role' => UserRole::USER->value]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
            'status' => TravelRequestStatus::APPROVED->value,
        ]);

    $response->assertStatus(200);

    Mail::assertSent(TravelRequestApproved::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    $this->assertDatabaseHas('travel_requests', [
        'id' => $travelRequest->id,
        'status' => TravelRequestStatus::APPROVED->value,
    ]);
});

test('admin cancelling travel request sends email to user', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $user = User::factory()->create(['role' => UserRole::USER->value]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
            'status' => TravelRequestStatus::CANCELLED->value,
        ]);

    $response->assertStatus(200);

    Mail::assertSent(TravelRequestCancelled::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    $this->assertDatabaseHas('travel_requests', [
        'id' => $travelRequest->id,
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);
});

test('user cancelling own travel request does not send email', function () {
    $user = User::factory()->create(['role' => UserRole::USER->value]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->patchJson("/api/user/travel-request/{$travelRequest->id}/cancel", [
            'status' => TravelRequestStatus::CANCELLED->value,
        ]);

    $response->assertStatus(200);

    Mail::assertNotSent(TravelRequestCancelled::class);
    Mail::assertNotSent(TravelRequestApproved::class);

    $this->assertDatabaseHas('travel_requests', [
        'id' => $travelRequest->id,
        'status' => TravelRequestStatus::CANCELLED->value,
    ]);
});

test('email contains correct travel request information', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $user = User::factory()->create(['role' => UserRole::USER->value]);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
        'name' => 'Viagem para São Paulo',
        'country' => 'Brasil',
        'town' => 'São Paulo',
        'state' => 'SP',
        'region' => 'Sudeste',
        'departure_date' => '2024-02-15',
        'return_date' => '2024-02-17',
    ]);

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/travel-request/{$travelRequest->id}/update", [
            'status' => TravelRequestStatus::APPROVED->value,
        ]);

    Mail::assertSent(TravelRequestApproved::class, function ($mail) use ($travelRequest, $user) {
        return $mail->hasTo($user->email) &&
               $mail->travelRequest->id === $travelRequest->id &&
               $mail->travelRequest->name === 'Viagem para São Paulo' &&
               $mail->travelRequest->country === 'Brasil';
    });
});
