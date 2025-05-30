<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use HasFactory;

    protected $table = 'clicks';
    
    /**
     * Indicar a Eloquent que no use los timestamps automáticos
     */
    public $timestamps = false;

    protected $fillable = [
        'email', 
        'ip_address',
        'municipio',
        'id_img',
        'id_person',
        'email_sent_at',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'email_sent_at' => 'datetime'
    ];

    /**
     * Obtiene la imagen asociada al clic
     */
    public function emailImage(): BelongsTo
    {
        return $this->belongsTo(EmailImage::class, 'id_img');
    }
}