<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); // ID único de la cuenta
            $table->string('email')->unique(); // Correo electrónico (único)
            $table->string('pass'); // Contraseña en texto plano (solo para pruebas, no usar en producción)
            $table->string('pass_encrip'); // Contraseña encriptada
            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};