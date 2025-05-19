<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('reservasi', function (Blueprint $table) {
        $table->json('combined_tables')->nullable()->after('meja_id');
    });
}

public function down()
{
    Schema::table('reservasi', function (Blueprint $table) {
        $table->dropColumn('combined_tables');
    });
}

};
