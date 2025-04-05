<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    protected $table = 'clicks';

    protected $fillable = [
        'email', 
        'ip_address',
        'municipio',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];
}