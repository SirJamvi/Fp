<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('meja', function (Blueprint $table) {
        $table->unsignedBigInteger('current_reservasi_id')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('meja', function (Blueprint $table) {
        $table->dropColumn('current_reservasi_id');
    });
}

};
