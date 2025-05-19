<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

return new class extends Migration
{
    public function up(): void
    {
        // Tentukan SEMUA nilai ENUM yang Anda inginkan, termasuk yang lama dan yang baru
        $newEnumValues = "'dipesan', 'selesai', 'dibatalkan', 'pending_arrival', 'confirmed', 'active_order', 'pending_payment', 'paid'"; // Sesuaikan dengan kebutuhan Anda
        // Pastikan default value adalah salah satu dari ENUM baru jika Anda mengubahnya
        // Jika default sebelumnya adalah 'dipesan', dan 'dipesan' masih ada, itu aman.
        DB::statement("ALTER TABLE reservasi MODIFY COLUMN status ENUM({$newEnumValues}) DEFAULT 'dipesan'"); // Atau DEFAULT NULL jika Anda mengizinkan NULL dan tidak ada default
    }

    public function down(): void
    {
        // Kembalikan ke definisi ENUM lama jika melakukan rollback
        $oldEnumValues = "'dipesan', 'selesai', 'dibatalkan'";
        // DB::statement("ALTER TABLE reservasi MODIFY COLUMN status ENUM({$oldEnumValues}) DEFAULT 'dipesan'");
        // Berhati-hatilah dengan rollback jika ada data yang sudah menggunakan nilai ENUM baru.
        // Mungkin lebih aman untuk tidak mendefinisikan rollback yang terlalu drastis atau pastikan datanya aman.
        // Untuk saat ini, kita bisa biarkan down() kosong atau hanya membalikkan jika yakin.
    }
};