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
        Schema::create('customer_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reservasi_id')->nullable()->constrained('reservasis')->onDelete('cascade');
            $table->string('type'); // reminder_12_hours, reminder_1_hour, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // data tambahan dalam format JSON
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_at')->nullable(); // kapan notifikasi dijadwalkan untuk dikirim
            $table->timestamp('sent_at')->nullable(); // kapan notifikasi benar-benar dikirim
            $table->boolean('is_sent')->default(false);
            $table->timestamps();

            // Index untuk performa
            $table->index(['user_id', 'read_at']);
            $table->index(['scheduled_at', 'is_sent']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_notifications');
    }
};