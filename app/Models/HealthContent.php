<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthContent extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image',
    ];
}
