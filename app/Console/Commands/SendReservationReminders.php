<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendReservationReminderJob; // Import Job Anda

class SendReservationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Signature ini akan digunakan untuk memanggil command dari scheduler
    protected $signature = 'notifications:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to send all pending reservation reminders.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching job to send reservation reminders...');

        // Memicu (dispatch) Job untuk dieksekusi di background
        SendReservationReminderJob::dispatch();

        $this->info('Job dispatched successfully!');
    }
}