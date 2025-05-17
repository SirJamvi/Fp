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
            // Tambahkan kolom untuk menyimpan detail pembayaran
            $table->string('payment_method')->nullable()->after('total_bill'); // 'cash', 'qris', etc.
            $table->decimal('amount_paid', 10, 2)->nullable()->after('payment_method'); // Jumlah uang diterima (untuk cash)
            $table->decimal('change_given', 10, 2)->nullable()->after('amount_paid'); // Kembalian (untuk cash)

            // Opsional: Tambahkan index jika sering melakukan pencarian berdasarkan metode pembayaran
            // $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Hapus kolom saat rollback migrasi
            $table->dropColumn(['payment_method', 'amount_paid', 'change_given']);
        });
    }
};