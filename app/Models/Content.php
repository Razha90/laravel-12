<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';
    protected $fillable = ['title', 'description','type','content','visibility','release','classroom_id'];
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function images()
    {
        return $this->hasMany(ImagesContent::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
