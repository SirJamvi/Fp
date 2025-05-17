<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('meja_id')->constrained('meja')->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->dateTime('waktu_kedatangan');
            $table->integer('jumlah_tamu');
            $table->enum('status', ['dipesan', 'selesai', 'dibatalkan']);
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
