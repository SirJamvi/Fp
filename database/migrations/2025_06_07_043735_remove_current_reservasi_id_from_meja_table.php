<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meja', function (Blueprint $table) {
            // Hapus kolom 'current_reservasi_id'
            $table->dropColumn('current_reservasi_id');
        });
    }

    public function down(): void
    {
        Schema::table('meja', function (Blueprint $table) {
            // Kode untuk mengembalikan kolom jika migrasi di-rollback
            $table->bigInteger('current_reservasi_id')->unsigned()->nullable()->after('status');
        });
    }
};
