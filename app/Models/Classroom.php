<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['title', 'description', 'image'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->hasMany(Content::class);
    }
}
