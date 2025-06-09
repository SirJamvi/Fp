<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Tambahkan kolom ini untuk menyimpan jumlah yang dibayar
            // Gunakan decimal untuk presisi angka di belakang koma, atau integer jika tidak perlu
            $table->decimal('payment_amount', 15, 2)->nullable()->after('payment_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            $table->dropColumn('payment_amount');
        });
    }
};