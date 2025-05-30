<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToClicksTable extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
}
