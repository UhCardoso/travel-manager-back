<?php

use App\Enums\TravelRequestStatus;
use App\Mail\TravelRequestApproved;
use App\Mail\TravelRequestCancelled;
use App\Models\TravelRequest;
use App\Models\User;
use App\Observers\TravelRequestObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    Mail::fake();
});

test('observer sends email when admin approves travel request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    Auth::shouldReceive('user')->andReturn($admin);

    $observer = new TravelRequestObserver;

    $travelRequest->setRawAttributes(array_merge($travelRequest->getAttributes(), [
        'status' => TravelRequestStatus::APPROVED,
    ]));

    $travelRequest = Mockery::mock($travelRequest)->makePartial();
    $travelRequest->shouldReceive('wasChanged')->with('status')->andReturn(true);
    $travelRequest->shouldReceive('getOriginal')->with('status')->andReturn(TravelRequestStatus::PENDING->value);

    $observer->updated($travelRequest);

    Mail::assertSent(TravelRequestApproved::class, function ($mail) use ($travelRequest) {
        return $mail->travelRequest->id === $travelRequest->id;
    });
});

test('observer sends email when admin cancels travel request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    Auth::shouldReceive('user')->andReturn($admin);

    $observer = new TravelRequestObserver;

    $travelRequest->setRawAttributes(array_merge($travelRequest->getAttributes(), [
        'status' => TravelRequestStatus::CANCELLED,
    ]));

    $travelRequest = Mockery::mock($travelRequest)->makePartial();
    $travelRequest->shouldReceive('wasChanged')->with('status')->andReturn(true);
    $travelRequest->shouldReceive('getOriginal')->with('status')->andReturn(TravelRequestStatus::PENDING->value);

    $observer->updated($travelRequest);

    Mail::assertSent(TravelRequestCancelled::class, function ($mail) use ($travelRequest) {
        return $mail->travelRequest->id === $travelRequest->id;
    });
});

test('observer does not send email when user cancels own travel request', function () {
    $user = User::factory()->create(['role' => 'user']);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    Auth::shouldReceive('user')->andReturn($user);

    $observer = new TravelRequestObserver;

    $travelRequest->setRawAttributes(array_merge($travelRequest->getAttributes(), [
        'status' => TravelRequestStatus::CANCELLED,
    ]));

    $travelRequest = Mockery::mock($travelRequest)->makePartial();
    $travelRequest->shouldReceive('wasChanged')->with('status')->andReturn(true);
    $travelRequest->shouldReceive('getOriginal')->with('status')->andReturn(TravelRequestStatus::PENDING->value);

    $observer->updated($travelRequest);

    Mail::assertNotSent(TravelRequestCancelled::class);
    Mail::assertNotSent(TravelRequestApproved::class);
});

test('observer does not send email when status is not changed', function () {
    $user = User::factory()->create(['role' => 'user']);
    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    $observer = new TravelRequestObserver;

    $travelRequest = Mockery::mock($travelRequest)->makePartial();
    $travelRequest->shouldReceive('wasChanged')->with('status')->andReturn(false);

    $observer->updated($travelRequest);

    Mail::assertNotSent(TravelRequestApproved::class);
    Mail::assertNotSent(TravelRequestCancelled::class);
});

test('observer handles email sending errors gracefully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    $travelRequest = TravelRequest::factory()->create([
        'user_id' => $user->id,
        'status' => TravelRequestStatus::PENDING,
    ]);

    Auth::shouldReceive('user')->andReturn($admin);

    Mail::shouldReceive('to->send')
        ->andThrow(new \Exception('SMTP connection failed'));

    $observer = new TravelRequestObserver;

    $travelRequest = Mockery::mock($travelRequest)->makePartial();
    $travelRequest->shouldReceive('wasChanged')->with('status')->andReturn(true);
    $travelRequest->shouldReceive('getOriginal')->with('status')->andReturn(TravelRequestStatus::PENDING->value);

    $travelRequest->status = TravelRequestStatus::APPROVED;
    $observer->updated($travelRequest);

    expect($travelRequest->status)->toBe(TravelRequestStatus::APPROVED);
});
