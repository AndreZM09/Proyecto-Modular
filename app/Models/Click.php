<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    protected $table = 'clicks'; // Nombre de la tabla en la BD

    protected $fillable = ['id', 'email', 'created_at']; // Campos que se pueden asignar masivamente
}
