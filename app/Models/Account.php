<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención de nombres de Laravel)
    protected $table = 'accounts';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'email',
        'pass',
        'pass_encrip',
    ];

    // Campos ocultos en las respuestas JSON (opcional)
    protected $hidden = [
        'pass', // Ocultamos la contraseña en texto plano
        'pass_encrip', // Ocultamos la contraseña encriptada
    ];
}