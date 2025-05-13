<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('user_id'); // Pastikan mengacu ke tabel 'pengguna'
            $table->unsignedBigInteger('reservasi_id'); // relasi ke tabel reservasis
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);

            $table->enum('status_makanan', ['pending', 'proses', 'complete', 'cancelled'])->default('pending');

            $table->timestamps();
        
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade'); // Ubah dari 'users' ke 'pengguna'
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
