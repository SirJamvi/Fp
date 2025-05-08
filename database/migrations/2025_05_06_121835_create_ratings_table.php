<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedTinyInteger('rating'); // nilai 1-5
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('pengguna')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
