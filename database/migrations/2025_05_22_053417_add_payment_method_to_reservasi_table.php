<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToReservasiTable extends Migration
{
    public function up()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            if (!Schema::hasColumn('reservasi', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
}