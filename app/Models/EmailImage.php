<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'size',
        'subject',
        'description'
    ];

    /**
     * Obtiene todos los emails asociados a esta imagen
     */
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Obtiene todos los clics asociados a esta imagen
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class, 'id_img');
    }
} 