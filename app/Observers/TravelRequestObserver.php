<?php

namespace App\Observers;

use App\Enums\TravelRequestStatus;
use App\Mail\TravelRequestApproved;
use App\Mail\TravelRequestCancelled;
use App\Models\TravelRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TravelRequestObserver
{
    /**
     * Handle the TravelRequest "updated" event.
     */
    public function updated(TravelRequest $travelRequest): void
    {
        if ($travelRequest->wasChanged('status')) {
            $oldStatus = $travelRequest->getOriginal('status');
            $newStatus = $travelRequest->status;

            $currentUser = Auth::user();

            if ($currentUser && $currentUser->id !== $travelRequest->user_id) {
                if ($newStatus === TravelRequestStatus::CANCELLED) {
                    $this->handleStatusCancelled($travelRequest, $oldStatus);
                }

                if ($newStatus === TravelRequestStatus::APPROVED) {
                    $this->handleStatusApproved($travelRequest, $oldStatus);
                }
            }
        }
    }

    /**
     * Handle when status changes to cancelled
     */
    private function handleStatusCancelled(TravelRequest $travelRequest, $oldStatus): void
    {
        try {
            Mail::to($travelRequest->user->email)->send(new TravelRequestCancelled($travelRequest));

        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de cancelamento', [
                'travel_request_id' => $travelRequest->id,
                'user_id' => $travelRequest->user_id,
                'user_email' => $travelRequest->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle when status changes to approved
     */
    private function handleStatusApproved(TravelRequest $travelRequest, $oldStatus): void
    {
        try {
            Mail::to($travelRequest->user->email)->send(new TravelRequestApproved($travelRequest));

        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de aprovação', [
                'travel_request_id' => $travelRequest->id,
                'user_id' => $travelRequest->user_id,
                'user_email' => $travelRequest->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
