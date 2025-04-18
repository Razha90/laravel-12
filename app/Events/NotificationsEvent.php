<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationsEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message = [];
    public $user_id = null;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $user_id)
    {
        try {
            $this->message = $message;
            $this->user_id = $user_id;
        } catch (\Throwable $th) {
            Log::error('NotificationsEvent Error: ' . $th->getMessage());
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notifications.' . $this->user_id);

    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notifications';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'data' => $this->message,
        ];
    }
}
