<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('meja', function (Blueprint $table) {
        $table->unsignedBigInteger('current_reservasi_id')->nullable()->after('status');

        // Jika ingin juga foreign key (opsional)
        // $table->foreign('current_reservasi_id')->references('id')->on('reservasi')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('meja', function (Blueprint $table) {
        // Jika pakai foreign key, drop dulu foreign key-nya:
        // $table->dropForeign(['current_reservasi_id']);
        $table->dropColumn('current_reservasi_id');
    });
}

};
