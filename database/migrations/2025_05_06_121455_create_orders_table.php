<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')->constrained('reservasi')->onDelete('cascade'); // Kunci asing ke tabel reservasi
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('pengguna')->onDelete('set null'); // Bisa user pelanggan atau pelayan
            $table->integer('quantity');
            $table->decimal('price_at_order', 10, 2); // Harga menu saat dipesan
            $table->decimal('total_price', 10, 2);    // Total harga untuk item ini
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // Contoh: pending, confirmed, preparing, ready, served, paid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};