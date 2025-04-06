<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RandomAvatar extends Model
{
    protected $table = 'random_image';

    protected $fillable = [
        'path',
        'name',
        'priority'
    ];

}
