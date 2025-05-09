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
    Schema::create('users', function (Blueprint $table) {
        $table->id(); // Kolom ID, biasanya digunakan sebagai primary key
        $table->string('name'); // Nama pengguna
        $table->string('email')->unique(); // Alamat email, harus unik
        $table->timestamp('email_verified_at')->nullable(); // Waktu verifikasi email (jika digunakan)
        $table->string('password'); // Kata sandi yang terenkripsi
        $table->rememberToken(); // Token untuk mengingat sesi pengguna (untuk "remember me" di login)
        $table->timestamps(); // Kolom created_at dan updated_at
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
