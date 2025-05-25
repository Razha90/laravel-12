<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Chatting implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $chat;
    public function __construct($chat)
    {
        Log::info('Chatting event created with chat data: ' . 'receiver' . $chat['receiver_id'] . ' me' . auth()->user()->id);
        $this->chat = $chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */


    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chat['receiver_id']),
            new PrivateChannel('chat.' . $this->chat['sender_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chatting';
    }

    public function broadcastWith(): array
    {
        return [
            'data' => $this->chat,
        ];
    }


}
