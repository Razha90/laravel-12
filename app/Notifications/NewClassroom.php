<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewClassroom extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $data = "";
    private $message = "";
    public function __construct($data)
    {
        $this->data = $data;
        $this->message = $data['message'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // return ['mail'];
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'title' => $this->data['title'],
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage( [
                'data' => [
                    'message' => $this->message,
                    'title' => $this->data['title'],
                ],
                'read_at' => null,
                'created_at' => $this->data['created_at'],
            ],
        );
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->data['user_id']);
    }

    
}
