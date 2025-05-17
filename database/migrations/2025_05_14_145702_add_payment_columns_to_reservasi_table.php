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
            // Only add waktu_selesai here, as payment details are added by an earlier migration
            // You can add it after 'change_given' (which is added by the 103600 migration)
            // Or simply after 'catatan' or 'updated_at' if order doesn't strictly matter
            // Let's place it after 'change_given' for logical flow if that column exists
            // Make sure 'change_given' column exists based on migration 2025_05_14_103600
             if (Schema::hasColumn('reservasi', 'change_given')) {
                $table->dateTime('waktu_selesai')->nullable()->after('change_given');
             } else {
                 // Fallback: Add after 'updated_at' if 'change_given' doesn't exist for some reason
                 $table->dateTime('waktu_selesai')->nullable()->after('updated_at');
             }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Only drop waktu_selesai
            if (Schema::hasColumn('reservasi', 'waktu_selesai')) {
                $table->dropColumn('waktu_selesai');
            }
        });
    }
};
