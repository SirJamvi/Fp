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
        Schema::table('menus', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('price'); // Misal: 5,2 untuk 100.00%
            $table->decimal('discounted_price', 10, 2)->nullable()->after('discount_percentage'); // Harga setelah diskon
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'discounted_price']);
        });
    }
};