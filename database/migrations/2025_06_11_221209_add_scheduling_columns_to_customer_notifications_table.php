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
        Schema::table('customer_notifications', function (Blueprint $table) {
            // Tambahkan kolom-kolom yang dibutuhkan oleh logic reminder
            $table->timestamp('scheduled_at')->nullable()->after('data');
            $table->boolean('is_sent')->default(false)->after('scheduled_at');
            $table->timestamp('sent_at')->nullable()->after('is_sent');
            
            // Tambahkan juga kolom reservasi_id jika belum ada, karena kode Anda menggunakannya
            $table->foreignId('reservasi_id')->nullable()->constrained('reservasi')->onDelete('cascade')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_notifications', function (Blueprint $table) {
            $table->dropForeign(['reservasi_id']);
            $table->dropColumn(['reservasi_id', 'scheduled_at', 'is_sent', 'sent_at']);
        });
    }
};