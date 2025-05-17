<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagesContent extends Model
{
    protected $table = 'images_content';
    protected $fillable = ['name', 'path', 'content_id', 'classroom_id'];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}