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
            $table->foreignId('created_by_pelayan_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('total_bill', 10, 2)->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            $table->dropForeign(['created_by_pelayan_id']);
            $table->dropColumn(['created_by_pelayan_id', 'total_bill']);
        });
    }
};
