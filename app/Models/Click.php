<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    protected $table = 'clicks';

    protected $fillable = ['id', 'email', 'ip_address', 'created_at'];
}
