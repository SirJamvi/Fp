<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Tambah kolom yang hilang jika belum ada
            if (!Schema::hasColumn('ratings', 'rating')) {
                $table->decimal('rating', 2, 1)->nullable()->after('app_rating');
            }
            
            if (!Schema::hasColumn('ratings', 'komentar')) {
                $table->text('komentar')->nullable()->after('comment');
            }

            // Rename kolom agar konsisten dengan model
            if (Schema::hasColumn('ratings', 'food_rating')) {
                $table->renameColumn('food_rating', 'rating_makanan');
            }
            
            if (Schema::hasColumn('ratings', 'service_rating')) {
                $table->renameColumn('service_rating', 'rating_pelayanan');
            }
            
            if (Schema::hasColumn('ratings', 'app_rating')) {
                $table->renameColumn('app_rating', 'rating_aplikasi');
            }

            // Tambah foreign key constraint untuk reservasi_id jika belum ada
            if (!Schema::hasColumn('ratings', 'reservasi_id')) {
                $table->unsignedBigInteger('reservasi_id')->after('reservation_id');
                $table->foreign('reservasi_id')->references('id')->on('reservasi')->onDelete('cascade');
            }
        });

        // Drop kolom lama jika ada
        Schema::table('ratings', function (Blueprint $table) {
            if (Schema::hasColumn('ratings', 'reservation_id')) {
                $table->dropColumn('reservation_id');
            }
            if (Schema::hasColumn('ratings', 'comment')) {
                $table->dropColumn('comment');
            }
        });
    }

    public function down()
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('rating_makanan', 'food_rating');
            $table->renameColumn('rating_pelayanan', 'service_rating'); 
            $table->renameColumn('rating_aplikasi', 'app_rating');
            
            $table->dropForeign(['reservasi_id']);
            $table->dropColumn(['rating', 'komentar', 'reservasi_id']);
            
            $table->unsignedBigInteger('reservation_id')->after('id');
            $table->text('comment')->nullable();
        });
    }
};