<?php

namespace App\Events;

use App\Models\TravelRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelRequestStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $travelRequest;

    public $type;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(TravelRequest $travelRequest, string $type)
    {
        $this->travelRequest = $travelRequest;
        $this->type = $type;

        $this->message = $type === 'approve'
            ? "Seu pedido de ID {$travelRequest->id} foi aprovado"
            : "Seu pedido de ID {$travelRequest->id} foi reprovado";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->travelRequest->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->travelRequest->id,
            'type' => $this->type,
            'message' => $this->message,
            'status' => $this->travelRequest->status->value,
            'user_id' => $this->travelRequest->user_id,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'travel-request-status-changed';
    }
}
