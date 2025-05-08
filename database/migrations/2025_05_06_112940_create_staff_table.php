<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nama'); // Nama staf
            $table->string('jabatan'); // Jabatan staf, contoh: pelayan, kasir
            $table->float('rating')->default(0); // Penilaian staf (default 0)
            $table->timestamps(); // Created_at dan Updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff');
    }
}

