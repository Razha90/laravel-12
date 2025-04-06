<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = ['title', 'description', 'image', 'password', 'code', 'status', 'delete'];
    
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
}
