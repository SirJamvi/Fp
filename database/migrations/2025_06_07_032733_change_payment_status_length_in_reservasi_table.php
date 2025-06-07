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
            // Ubah panjang kolom 'payment_status' menjadi 20 karakter
            // Anda juga bisa mendefinisikan nilai default jika perlu
            $table->string('payment_status', 20)->nullable()->change();
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
            // Jika perlu di-rollback, kembalikan ke definisi sebelumnya
            // Sesuaikan panjangnya dengan definisi lama Anda
            $table->string('payment_status', 5)->nullable()->change();
        });
    }
};