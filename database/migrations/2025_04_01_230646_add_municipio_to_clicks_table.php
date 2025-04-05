<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMunicipioToClicksTable extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->string('municipio')->after('ip_address')->default('Desconocido');
        });
    }

    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn('municipio');
        });
    }
}