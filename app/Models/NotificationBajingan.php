<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationBajingan extends Model
{
    protected $table = 'notifications'; // Pastikan nama tabelnya benar

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
    // protected $table = 'notifications';
    // protected $fillable = [
    //     'title',
    //     'body',
    //     'read',
    //     'user_id',
    // ];
    // protected $casts = [
    //     'read' => 'boolean',
    // ];
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
