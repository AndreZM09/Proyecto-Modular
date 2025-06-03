<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('email_images', function (Blueprint $table) {
            if (!Schema::hasColumn('email_images', 'message')) {
                $table->text('message')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('email_images', 'priority')) {
                $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal')->after('message');
            }
        });
    }

    public function down()
    {
        Schema::table('email_images', function (Blueprint $table) {
            $table->dropColumn(['message', 'priority']);
        });
    }
}; 