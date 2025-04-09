<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicationLater extends Model
{
    use HasFactory;

    // Nama tabel yang terhubung
    protected $table = 'aplication_letter';
    

    // Kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'user_id',
        'current_role',
        'request_role',
        'full_name',
        'message',
        'origin',
        'status',
        'approved_at',
        'rejected_at',
    ];

    // Tipe data kolom yang akan dikonversi secara otomatis
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
}
