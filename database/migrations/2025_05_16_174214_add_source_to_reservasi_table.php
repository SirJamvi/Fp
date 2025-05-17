<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade if you need to modify enum

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Tambahkan kolom 'source' setelah kolom 'status'
            // Nilai bisa 'online' atau 'dine_in'
            // Default 'dine_in' untuk pesanan yang dibuat pelayan
            // Default 'online' untuk reservasi dari aplikasi/online (jika Anda mengimplementasikannya)
            // Pastikan kolom 'status' ada di tabel reservasi Anda
            $table->enum('source', ['online', 'dine_in'])->default('dine_in')->after('status');
        });

        // Opsional: Jika sudah ada data reservasi lama yang seharusnya 'online',
        // Anda bisa menjalankan query untuk mengupdatenya.
        // Contoh: DB::table('reservasi')->whereNull('source')->update(['source' => 'online']);
        // Atau jika reservasi lama dibuat oleh user_id (pelanggan) dan staff_id null:
        // DB::table('reservasi')->whereNotNull('user_id')->whereNull('staff_id')->update(['source' => 'online']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Hapus kolom 'source' saat rollback
            // Check if column exists before dropping (optional but safer)
            if (Schema::hasColumn('reservasi', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
