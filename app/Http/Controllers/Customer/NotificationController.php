<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerNotification; // Kita akan buat model ini jika belum ada

class NotificationController extends Controller
{
    /**
     * Get a list of notifications for the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Jika Anda menggunakan sistem notifikasi bawaan Laravel (Database Notifications),
        // Anda bisa menggunakan: $notifications = $request->user()->notifications()->paginate(10);
        // Pastikan model Pengguna Anda menggunakan trait Notifiable.

        // Jika Anda menggunakan model custom CustomerNotification:
        $notifications = CustomerNotification::where('user_id', $request->user()->id)
                                             ->orderBy('created_at', 'desc')
                                             ->paginate(10);

        return response()->json([
            'message' => 'Daftar notifikasi berhasil diambil.',
            'notifications' => $notifications,
        ], 200);
    }

    /**
     * Mark a specific notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $notificationId)
    {
        // Menggunakan Custom Notification Model
        $notification = CustomerNotification::where('id', $notificationId)
                                            ->where('user_id', $request->user()->id)
                                            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if (!$notification->read_at) { // Pastikan notifikasi belum dibaca
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json(['message' => 'Notifikasi berhasil ditandai telah dibaca.', 'notification' => $notification], 200);
    }

    /**
     * Mark all notifications for the authenticated customer as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        // Menggunakan Custom Notification Model
        CustomerNotification::where('user_id', $request->user()->id)
                            ->whereNull('read_at')
                            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi berhasil ditandai telah dibaca.'], 200);
    }
}