<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailImage extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'size'
    ];
} 