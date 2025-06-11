<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- TAMBAHKAN IMPORT INI

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// ===================================================================
// === VVV TAMBAHKAN BLOK KODE INI UNTUK MENJADWALKAN COMMAND VVV ===
//
// Ini akan menjalankan command 'notifications:send-reminders' setiap menit.
// Command ini kemudian akan memicu Job yang akan mengirim notifikasi
// yang waktunya sudah tiba.
Schedule::command('notifications:send-reminders')->everyMinute();
// ===================================================================