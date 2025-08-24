<?php

namespace App\Observers;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;

class TravelRequestObserver
{
    /**
     * Handle the TravelRequest "updated" event.
     */
    public function updated(TravelRequest $travelRequest): void
    {
        // Check if the status column was changed
        if ($travelRequest->wasChanged('status')) {
            $oldStatus = $travelRequest->getOriginal('status');
            $newStatus = $travelRequest->status;

            // Handle status change to cancelled
            if ($newStatus === TravelRequestStatus::CANCELLED) {
                $this->handleStatusCancelled($travelRequest, $oldStatus);
            }

            // Handle status change to approved
            if ($newStatus === TravelRequestStatus::APPROVED) {
                $this->handleStatusApproved($travelRequest, $oldStatus);
            }
        }
    }

    /**
     * Handle the TravelRequest "created" event.
     */
    public function created(TravelRequest $travelRequest): void
    {
        // TODO: Send email notification when travel request is created
        // This functionality will be implemented later

        // Log the creation for debugging purposes
        \Log::info('Travel request created', [
            'travel_request_id' => $travelRequest->id,
            'user_id' => $travelRequest->user_id,
            'status' => $travelRequest->status->value,
            'created_at' => now(),
        ]);
    }

    /**
     * Handle the TravelRequest "deleted" event.
     */
    public function deleted(TravelRequest $travelRequest): void
    {
        // TODO: Send email notification when travel request is deleted
        // This functionality will be implemented later

        // Log the deletion for debugging purposes
        \Log::info('Travel request deleted', [
            'travel_request_id' => $travelRequest->id,
            'user_id' => $travelRequest->user_id,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Handle when status changes to cancelled
     */
    private function handleStatusCancelled(TravelRequest $travelRequest, $oldStatus): void
    {
        // TODO: Send email notification when travel request is cancelled
        // This functionality will be implemented later

        // Log the status change for debugging purposes
        \Log::info('Travel request cancelled', [
            'travel_request_id' => $travelRequest->id,
            'user_id' => $travelRequest->user_id,
            'old_status' => $oldStatus,
            'new_status' => $travelRequest->status->value,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Handle when status changes to approved
     */
    private function handleStatusApproved(TravelRequest $travelRequest, $oldStatus): void
    {
        // TODO: Send email notification when travel request is approved
        // This functionality will be implemented later

        // Log the status change for debugging purposes
        \Log::info('Travel request approved', [
            'travel_request_id' => $travelRequest->id,
            'user_id' => $travelRequest->user_id,
            'old_status' => $oldStatus,
            'new_status' => $travelRequest->status->value,
            'approved_at' => now(),
        ]);
    }
}
