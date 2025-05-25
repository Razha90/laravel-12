<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;
    
    protected $table = 'comment';

    protected $fillable = [
        'id',
        'content_id',
        'user_id',
        'comment',
        'isDeleted',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
