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
        Schema::table('clicks', function (Blueprint $table) {
            // Añadir nuevos campos
            $table->foreignId('id_img')->nullable()->after('id')->comment('ID de la imagen enviada (email_images)');
            $table->foreignId('id_person')->nullable()->after('id_img')->comment('ID de la persona a quien se envió');
            $table->timestamp('email_sent_at')->nullable()->after('municipio')->comment('Cuando se envió el correo');
            $table->timestamp('clicked_at')->nullable()->after('ip_address');
            $table->timestamp('email_opened_at')->nullable()->after('ip_address');
            
            // Agregar índices para mejorar el rendimiento
            $table->index('id_img');
            $table->index('id_person');
            $table->index('email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            // Eliminar los campos añadidos
            $table->dropIndex(['id_img']);
            $table->dropIndex(['id_person']);
            $table->dropIndex(['email_sent_at']);
            
            $table->dropColumn('id_img');
            $table->dropColumn('id_person');
            $table->dropColumn('email_sent_at');
            $table->dropColumn('clicked_at');
            $table->dropColumn('email_opened_at');
        });
    }
};
