<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Mengubah panjang kolom kehadiran_status menjadi VARCHAR(50)
            // Sesuaikan 50 jika Anda merasa butuh lebih atau kurang,
            // tapi 50 adalah ukuran yang aman untuk status seperti 'belum hadir', 'sudah hadir', 'dibatalkan', dll.
            $table->string('kehadiran_status', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Mengembalikan panjang kolom kehadiran_status ke ukuran sebelumnya
            // Perhatian: Ini mungkin menyebabkan truncation lagi jika ada data yang lebih panjang
            // dari 20 karakter setelah migrasi up. Sesuaikan ukuran ini jika Anda tahu ukuran aslinya.
            $table->string('kehadiran_status', 20)->change(); // Ganti 20 dengan ukuran sebelumnya jika Anda tahu
        });
    }
};