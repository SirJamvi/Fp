<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerNotification;
use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching notifications for user: ' . $request->user()->id);

            $notifications = CustomerNotification::where('user_id', $request->user()->id)
                ->with([
                    'reservasi:id,kode_reservasi,waktu_kedatangan,status',
                    'pengguna:id,nama,email'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $unreadCount = CustomerNotification::where('user_id', $request->user()->id)
                ->unread()
                ->count();

            Log::info('Notifications found: ' . $notifications->count());
            Log::info('Unread count: ' . $unreadCount);

            return response()->json([
                'success'      => true,
                'message'      => 'Daftar notifikasi berhasil diambil.',
                'data'         => $notifications,
                'unread_count' => $unreadCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications: ' . $e->getMessage(),
                'data'    => [
                    'data'         => [],
                    'current_page' => 1,
                    'last_page'    => 1,
                    'per_page'     => 20,
                    'total'        => 0
                ],
                'unread_count' => 0,
            ], 500);
        }
    }

    // START: Perubahan - Fungsi baru untuk membuat notifikasi status pesanan dari Koki
    public static function createOrderStatusUpdateNotification(Reservasi $reservasi, string $newStatus)
    {
        try {
            $notificationType = null;
            $title = '';
            $message = '';

            switch ($newStatus) {
                case 'preparing':
                    $notificationType = CustomerNotification::TYPE_ORDER_PREPARING;
                    $title = 'Pesanan Sedang Disiapkan';
                    $message = "Koki kami mulai menyiapkan pesanan Anda untuk reservasi {$reservasi->kode_reservasi}.";
                    break;
                case 'completed':
                    $notificationType = CustomerNotification::TYPE_ORDER_COMPLETED;
                    $title = 'Pesanan Selesai Dimasak';
                    $message = "Semua pesanan Anda untuk reservasi {$reservasi->kode_reservasi} telah selesai dimasak.";
                    break;
                case 'cancelled':
                    $notificationType = CustomerNotification::TYPE_ORDER_CANCELLED_KOKI;
                    $title = 'Pesanan Dibatalkan Dapur';
                    $message = "Mohon maaf, terjadi masalah pada pesanan Anda ({$reservasi->kode_reservasi}) dan harus dibatalkan oleh dapur.";
                    break;
                default:
                    // Jangan buat notifikasi jika status tidak dikenali
                    return null;
            }

            Log::info("Creating order status notification for reservation: {$reservasi->id}, status: {$newStatus}");

            $notification = CustomerNotification::create([
                'user_id'       => $reservasi->user_id,
                'reservasi_id'  => $reservasi->id,
                'type'          => $notificationType,
                'title'         => $title,
                'message'       => $message,
                'data'          => [
                    'kode_reservasi' => $reservasi->kode_reservasi,
                    'new_status'     => $newStatus,
                ],
                'is_sent'       => true,
                'sent_at'       => now(),
            ]);

            Log::info("Order status notification created with ID: {$notification->id}");
            return $notification;

        } catch (\Exception $e) {
            Log::error("Error creating order status notification: " . $e->getMessage());
            return null;
        }
    }
    // END: Perubahan

    // Fungsi lain seperti markAsRead, getLatestNotifications, dll. tetap sama
    // ... (kode yang ada sebelumnya tidak perlu diubah) ...
    
    public function markAsRead(Request $request, $notificationId)
    {
        $notification = CustomerNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil ditandai telah dibaca.', 'data' => $notification], 200);
    }

    public function markAllAsRead(Request $request)
    {
        $updatedCount = CustomerNotification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Semua notifikasi berhasil ditandai telah dibaca.', 'updated_count' => $updatedCount], 200);
    }
    
    public function getLatestNotifications(Request $request)
    {
        try {
            $lastNotificationId = $request->get('last_id', 0);

            $notifications = CustomerNotification::where('user_id', $request->user()->id)
                ->where('id', '>', $lastNotificationId)
                ->with([
                    'reservasi:id,kode_reservasi,waktu_kedatangan,status',
                    'pengguna:id,nama,email'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            $unreadCount = CustomerNotification::where('user_id', $request->user()->id)
                ->unread()
                ->count();

            return response()->json([
                'success'      => true,
                'message'      => 'Notifikasi terbaru berhasil diambil.',
                'data'         => $notifications,
                'unread_count' => $unreadCount,
                'has_new'      => $notifications->count() > 0,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching latest notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching notifications', 'data' => [], 'unread_count' => 0, 'has_new' => false], 500);
        }
    }

    public function destroy(Request $request, $notificationId)
    {
        $notification = CustomerNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dihapus.'], 200);
    }
}