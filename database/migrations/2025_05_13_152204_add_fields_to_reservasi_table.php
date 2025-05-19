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
            if (!Schema::hasColumn('reservasi', 'nama_pelanggan')) {
                $table->string('nama_pelanggan')->nullable()->after('meja_id'); // Tambahkan kolom ini
            }
            if (!Schema::hasColumn('reservasi', 'created_by_pelayan_id')) {
                $table->foreignId('created_by_pelayan_id')->nullable()->after('catatan_khusus')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('reservasi', 'total_bill')) {
                $table->decimal('total_bill', 10, 2)->nullable()->after('created_by_pelayan_id');
            }
            // Pastikan kolom user_id nullable jika pelanggan bisa walk-in tanpa akun
            if (Schema::hasColumn('reservasi', 'user_id')) {
                 $table->foreignId('user_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Hati-hati dengan dropForeign jika ada data
            if (Schema::hasColumn('reservasi', 'created_by_pelayan_id')) {
                // Cek dulu apakah foreign key constraint ada sebelum drop
                // Cara ceknya bisa lebih kompleks, untuk simpelnya kita coba try-catch atau cek manual di DB
                // Untuk keamanan, Anda mungkin ingin menghapus ini dari method down atau pastikan constraint ada
                // $table->dropForeign(['created_by_pelayan_id']); // Baris ini mungkin error jika constraint tidak ada
            }
            $table->dropColumn(['nama_pelanggan', 'created_by_pelayan_id', 'total_bill']);
             // Jika Anda mengubah user_id menjadi nullable, kembalikan jika perlu
            // if (Schema::hasColumn('reservasi', 'user_id')) {
            //     $table->foreignId('user_id')->nullable(false)->change();
            // }
        });
    }
};