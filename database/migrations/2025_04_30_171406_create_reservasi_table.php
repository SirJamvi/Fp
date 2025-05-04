<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/[timestamp]_create_reservasi_table.php
return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna');
            $table->foreignId('meja_id')->constrained('meja');
            $table->dateTime('waktu_kedatangan');
            $table->integer('jumlah_tamu');
            $table->enum('status', ['dipesan', 'hadir', 'batal', 'selesai'])->default('dipesan');
            $table->string('kode_reservasi')->unique();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
