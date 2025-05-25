<?php

namespace App\Models;

use App\Events\Chatting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Chat extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;
    protected $table = 'chat';
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
        'conversations_id'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversations_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            try {
                $chat = [
                    'id' => $model->id,
                    'sender_id' => $model->sender_id,
                    'receiver_id' => $model->receiver_id,
                    'message' => $model->message,
                    'read_at' => $model->read_at,
                    'conversations_id' => $model->conversations_id,
                    'created_at' => $model->created_at,
                    'updated_at' => $model->updated_at
                ];

                $conversation = Conversation::find($model->conversations_id);
                $conversation->last_message = $model->message;
                $conversation->save();
                broadcast(new Chatting($chat));
                // event(new Chatting($chat));

            } catch (\Throwable $th) {
                Log::error('Error sending notification: ' . $th->getMessage());
            }
        });
    }
}
