<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomMember extends Model
{
    protected $table = 'classroom_members';
    protected $fillable = [
        'classroom_id',
        'user_id',
        'role',
        'status',
        'action',
        'message',
        'joined_at',
        'approved_at',
        'rejected_at',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    // Relasi ke User (Setiap member terkait dengan satu user)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
