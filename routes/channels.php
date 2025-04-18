<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('notifications', function () {
//     Log::info('Seseorang Masuk' );
//     return true; // Mengizinkan siapa saja untuk mendengarkan
// });

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('aplications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
