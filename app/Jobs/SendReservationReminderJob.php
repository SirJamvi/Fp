<?php

namespace App\Jobs;

use App\Models\CustomerNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReservationReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Ambil semua notifikasi yang sudah waktunya dikirim
            $pendingNotifications = CustomerNotification::pending()
                ->with(['user', 'reservasi'])
                ->get();

            $sentCount = 0;

            foreach ($pendingNotifications as $notification) {
                // Di sini Anda bisa menambahkan logic untuk:
                // 1. Mengirim push notification ke mobile app
                // 2. Mengirim email notification
                // 3. Mengirim SMS notification
                // 4. Atau integrasi dengan service notification lainnya

                // Contoh log untuk debugging
                Log::info('Sending notification', [
                    'user_id' => $notification->user_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'scheduled_at' => $notification->scheduled_at
                ]);

                // Mark notification sebagai sudah dikirim
                $notification->markAsSent();
                $sentCount++;
            }

            if ($sentCount > 0) {
                Log::info("Successfully sent {$sentCount} reservation reminder notifications");
            }

        } catch (\Exception $e) {
            Log::error('Error sending reservation reminder notifications: ' . $e->getMessage());
            throw $e;
        }
    }
}