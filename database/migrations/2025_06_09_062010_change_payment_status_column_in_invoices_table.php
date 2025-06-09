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
        Schema::table('invoices', function (Blueprint $table) {
            // Mengubah kolom payment_status menjadi string dengan panjang 20
            $table->string('payment_status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Kode ini untuk membatalkan perubahan jika diperlukan
            // Anda bisa biarkan atau sesuaikan dengan tipe data sebelumnya
        });
    }
};