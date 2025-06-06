<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMejaReservasiTable extends Migration
{
    public function up()
    {
        Schema::create('meja_reservasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservasi_id');
            $table->unsignedBigInteger('meja_id');
            $table->timestamps();

            // FK ke tabel reservasi
            $table->foreign('reservasi_id')
                  ->references('id')
                  ->on('reservasi')
                  ->onDelete('cascade');

            // FK ke tabel meja
            $table->foreign('meja_id')
                  ->references('id')
                  ->on('meja')
                  ->onDelete('cascade');

            // Pastikan kombinasi reservasi_id + meja_id unik
            $table->unique(['reservasi_id', 'meja_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('meja_reservasi');
    }
}
