<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Notifications\AplicationLaterNotifaction;
use App\Notifications\UserMaiRegisterationlNotification;
use App\Notifications\UserNotification;

new #[Layout('components.layouts.app-page')] class extends Component {
    public function sendEmail()
    {
        $user = auth()->user();
        $user->notify(new UserMaiRegisterationlNotification());
    }

    public function sendNotification()
    {
        $data = [
            'title' => 'New Notification',
            'message' => 'This is a test notification.',
            'user_id' => auth()->user()->id,
        ];
        $user = auth()->user();
        $user->notify(new AplicationLaterNotifaction($data));
    }

    public function notificatoinSender()
    {
        $user = auth()->user();
        $data = [
            'title' => 'New Notification',
            'message' => 'This is a test notification.',
            'user_id' => auth()->user()->id,
            'created_at' => now(), 
        ];
        $user->notify(new UserNotification($data));
    }
}; ?>

<div>
    <button wire:click="sendEmail" class="rounded bg-blue-500 px-4 py-2 text-white">
        Send Email
    </button>

    <button wire:click="notificatoinSender" class="bg-blue-500 hover:bg-red-500">Send Notification</button>

</div>
