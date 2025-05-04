<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/[timestamp]_create_meja_table.php
// database/migrations/[timestamp]_create_meja_table.php
return new class extends Migration {
    public function up(): void
    {
        Schema::create('meja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_meja')->unique();
            $table->string('area');
            $table->integer('kapasitas');
            $table->enum('status', ['tersedia', 'terisi', 'dipesan', 'nonaktif'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meja');
    }
};