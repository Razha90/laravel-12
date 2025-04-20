<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AplicationLaterNotifaction extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        if (!empty($this->data['status'])) {
            return new BroadcastMessage([
                'data' => [
                    'approved_at' => $this->data['approved_at'],
                    'created_at' => $this->data['created_at'],
                    'current_role' => $this->data['current_role'],
                    'full_name' => $this->data['full_name'],
                    'id' => $this->data['id'],
                    'message' => $this->data['message'],
                    'origin' => $this->data['origin'],
                    'rejected_at' => $this->data['rejected_at'],
                    'request_role' => $this->data['request_role'],
                    'status' => $this->data['status'],
                    'updated_at' => $this->data['updated_at'],
                    'user_id' => $this->data['user_id'],
                ],
            ]);
        } else {
            return new BroadcastMessage([
                'data' => [
                    'id' => $this->data['id'],
                    'user_id' => $this->data['user_id'],
                ],
            ]);
        }
    }

    public function broadcastOn()
    {
        return new PrivateChannel('aplications.' . $this->data['user_id']);
    }
}
