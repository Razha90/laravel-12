<?php

namespace App\Models;

use App\Notifications\NewClassroom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Classroom extends Model
{
    protected $fillable = ['user_id','title', 'position' ,'description', 'image', 'password', 'code', 'status', 'ask_join', 'is_password', 'password'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->hasMany(Content::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ClassroomMember::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            $model->load('user');
            try {
                $notification = [
                    'classroom_id' => $model->id,
                    'created_at' => $model->created_at,
                    'user_id' => $model->user_id,
                    'message' => __('mail.new_classroom.message', ['link' => route('classroom-learn', $model->id)]),
                    'title' => __('mail.new_classroom.title'),
                ];
                auth()->user()->notify(new NewClassroom($notification));
            } catch (\Throwable $th) {
                Log::error('Error sending notification: ' . $th->getMessage());
            }
        });
    }
}
