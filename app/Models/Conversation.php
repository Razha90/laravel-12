<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Conversation extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'conversations';

    protected $fillable = [
        'user_one',
        'user_two',
        'last_message',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }
    public function chats()
    {
        return $this->hasMany(Chat::class, 'conversations_id');
    }

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::updated(function ($model) {
    //         try {
    //             $chat = new Chat();
    //             $chat->sender_id = $model->user_one;

    //         } catch (\Throwable $th) {
    //             Log::error('Error sending notification: ' . $th->getMessage());
    //         }
    //     });
    // }
}
