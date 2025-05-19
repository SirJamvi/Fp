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
            // Tambahkan kolom 'kehadiran_status' setelah 'jumlah_tamu'
            // Nilai bisa 'hadir', 'tidak_hadir', 'belum_dikonfirmasi'
            // Default 'hadir' untuk dine-in order, 'belum_dikonfirmasi' untuk reservasi online
            $table->enum('kehadiran_status', ['hadir', 'tidak_hadir', 'belum_dikonfirmasi'])->default('belum_dikonfirmasi')->after('jumlah_tamu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Hapus kolom 'kehadiran_status' saat rollback
            $table->dropColumn('kehadiran_status');
        });
    }
};
