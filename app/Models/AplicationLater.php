<?php

namespace App\Models;

use App\Events\AplicationNotification;
use App\Notifications\AplicationLaterNotifaction;
use App\Notifications\UserNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class AplicationLater extends Model
{
    use HasFactory;
    protected $table = 'aplication_letter';
    protected $fillable = ['user_id', 'current_role', 'request_role', 'full_name', 'message', 'origin', 'status', 'approved_at', 'rejected_at'];
    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relasi dengan model User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            $model->load('user');
            try {
                $payload = [
                    'approved_at' => $model->approved_at,
                    'created_at' => $model->created_at,
                    'current_role' => $model->current_role,
                    'full_name' => $model->full_name,
                    'id' => $model->id,
                    'message' => $model->message,
                    'origin' => $model->origin,
                    'rejected_at' => $model->rejected_at,
                    'request_role' => $model->request_role,
                    'status' => $model->status,
                    'updated_at' => $model->updated_at,
                    'user_id' => $model->user_id,
                ];

                auth()->user()->notify(new AplicationLaterNotifaction($payload));

                $notification = [
                    'message' => $model->message,
                    'title' => __('notifikasi.aplication_letter'),
                    'created_at' => $model->created_at,
                    'read_at' => null,
                    'user_id' => $model->user_id,
                ];

                auth()->user()->notify(new UserNotification($notification));
            } catch (\Throwable $th) {
                Log::error('Error sending notification: ' . $th->getMessage());
            }
        });

        static::updated(function ($model) {
            $model->load('user');
            try {
                $payload = [
                    'approved_at' => $model->approved_at,
                    'created_at' => $model->created_at,
                    'current_role' => $model->current_role,
                    'full_name' => $model->full_name,
                    'id' => $model->id,
                    'message' => $model->message,
                    'origin' => $model->origin,
                    'rejected_at' => $model->rejected_at,
                    'request_role' => $model->request_role,
                    'status' => $model->status,
                    'updated_at' => $model->updated_at,
                    'user_id' => $model->user_id,
                ];

                auth()->user()->notify(new AplicationLaterNotifaction($payload));
                if ($model->status == 'approved') {
                    $notification = [
                        'message' => $model->message,
                        'title' => __('notifikasi.aplication_letter_approved'),
                        'created_at' => $model->created_at,
                        'read_at' => null,
                        'user_id' => $model->user_id,
                    ];
                } elseif ($model->status == 'rejected') {
                    $notification = [
                        'message' => $model->message,
                        'title' => __('notifikasi.aplication_letter_rejected'),
                        'created_at' => $model->created_at,
                        'read_at' => null,
                        'user_id' => $model->user_id,
                    ];
                }

                auth()->user()->notify(new UserNotification($notification));
            } catch (\Throwable $th) {
                Log::error('Error sending notification: ' . $th->getMessage());
            }
        });

        static::deleted(function ($model) {
            $model->load('user');
            try {
                $payload = [
                    'id' => $model->id,
                    'user_id' => $model->user_id,
                ];

                auth()->user()->notify(new AplicationLaterNotifaction($payload));
            } catch (\Throwable $th) {
                Log::error('Error sending notification: ' . $th->getMessage());
            }
        });
    }
}
