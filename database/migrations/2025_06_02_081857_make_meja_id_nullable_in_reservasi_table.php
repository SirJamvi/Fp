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
            // Mengubah kolom meja_id menjadi nullable
            // Penting: pastikan tipe data sudah sesuai dengan definisi awal (unsignedBigInteger)
            $table->unsignedBigInteger('meja_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Mengembalikan kolom meja_id menjadi NOT NULL
            // CATATAN: Ini akan gagal jika ada baris dengan meja_id NULL di database saat rollback.
            $table->unsignedBigInteger('meja_id')->nullable(false)->change();
        });
    }
};