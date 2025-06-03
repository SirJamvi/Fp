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
            // Ubah definisi kolom 'source' untuk menambahkan 'pre_order' ke daftar ENUM.
            // Pastikan Anda menyertakan SEMUA nilai ENUM yang sudah ada ('online', 'dine_in')
            // ditambah nilai baru ('pre_order').
            $table->enum('source', ['online', 'dine_in', 'pre_order'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Untuk mengembalikan, Anda menghapus 'pre_order' dari daftar ENUM.
            // Perhatian: Ini akan menyebabkan masalah jika ada data 'pre_order' di database.
            $table->enum('source', ['online', 'dine_in'])->change();
        });
    }
};