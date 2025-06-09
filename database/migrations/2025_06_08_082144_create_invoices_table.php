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
       Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('reservasi_id');
        $table->string('invoice_number')->unique();
        $table->decimal('subtotal', 12, 2);
        $table->decimal('service_fee', 12, 2);
        $table->decimal('total_amount', 12, 2);
        $table->decimal('amount_paid', 12, 2)->default(0);
        $table->decimal('remaining_amount', 12, 2);
        $table->string('payment_method')->nullable();
        $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
        $table->text('qr_code')->nullable();
        $table->timestamp('generated_at')->nullable();
        $table->timestamps();
        
        $table->foreign('reservasi_id')->references('id')->on('reservasi')->onDelete('cascade');
        $table->index(['reservasi_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
