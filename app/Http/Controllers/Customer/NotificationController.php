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

    public function markAsRead(Request $request, $notificationId)
    {
        $notification = CustomerNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.'
            ], 404);
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai telah dibaca.',
            'data'    => $notification
        ], 200);
    }

    public function markAllAsRead(Request $request)
    {
        $updatedCount = CustomerNotification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success'       => true,
            'message'       => 'Semua notifikasi berhasil ditandai telah dibaca.',
            'updated_count' => $updatedCount
        ], 200);
    }

    public static function createImmediateReservationNotification(Reservasi $reservasi)
    {
        try {
            Log::info('Creating immediate notification for reservation: ' . $reservasi->id);

            $notification = CustomerNotification::create([
                'user_id'       => $reservasi->user_id,
                'reservasi_id'  => $reservasi->id,
                'type'          => CustomerNotification::TYPE_RESERVATION_CREATED,
                'title'         => 'Reservasi Berhasil Dibuat',
                'message'       => "Reservasi Anda ({$reservasi->kode_reservasi}) berhasil dibuat. Silakan lakukan pembayaran.",
                'data'          => [
                    'kode_reservasi' => $reservasi->kode_reservasi,
                    'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                    'status'          => $reservasi->status,
                ],
                'is_sent'       => true,
                'sent_at'       => now(),
            ]);

            Log::info('Notification created with ID: ' . $notification->id);

            // Langsung buat pengingat juga
            self::createReservationReminders($reservasi);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Error creating immediate notification: ' . $e->getMessage());
        }
    }

    // Ganti seluruh method createReservationReminders Anda dengan ini:
    public static function createReservationReminders(Reservasi $reservasi)
    {
        try {
            Log::info('Creating reminders for reservation: ' . $reservasi->id);

            // PERBAIKAN: Gunakan 'waktu_kedatangan' yang benar
            $reservationDateTime = Carbon::parse($reservasi->waktu_kedatangan);

            if ($reservationDateTime->isPast()) {
                Log::info('Reservation is in the past, skipping reminders');
                return;
            }

            $userId = $reservasi->user_id;

            // Hapus reminder lama jika ada
            CustomerNotification::where('user_id', $userId)
                ->where('reservasi_id', $reservasi->id)
                ->whereIn('type', [
                    'reminder_12_hours',
                    'reminder_1_hour',
                    'reminder_5_minutes',
                ])
                ->delete();

            $notifications = [];

            // PERBAIKAN: Format tanggal dan waktu dari objek Carbon
            $formattedDate = $reservationDateTime->isoFormat('D MMMM YYYY');
            $formattedTime = $reservationDateTime->isoFormat('HH:mm');

            // Reminder 12 jam sebelumnya
            $rem12 = $reservationDateTime->copy()->subHours(12);
            if ($rem12->isFuture()) {
                $notifications[] = [
                    'user_id'       => $userId,
                    'reservasi_id'  => $reservasi->id,
                    'type'          => 'reminder_12_hours',
                    'title'         => 'Pengingat Reservasi (12 Jam)',
                    'message'       => "Hai! Reservasi Anda akan dimulai besok pada {$formattedDate} pukul {$formattedTime}.",
                    'data'          => json_encode(['kode_reservasi'=> $reservasi->kode_reservasi]),
                    'scheduled_at'  => $rem12,
                    'is_sent'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // Reminder 1 jam sebelumnya
            $rem1 = $reservationDateTime->copy()->subHour();
            if ($rem1->isFuture()) {
                $notifications[] = [
                    'user_id'       => $userId,
                    'reservasi_id'  => $reservasi->id,
                    'type'          => 'reminder_1_hour',
                    'title'         => 'Pengingat Reservasi (1 Jam)',
                    'message'       => "Reservasi Anda dimulai 1 jam lagi pada pukul {$formattedTime}.",
                    'data'          => json_encode(['kode_reservasi'=> $reservasi->kode_reservasi]),
                    'scheduled_at'  => $rem1,
                    'is_sent'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // Reminder 5 menit sebelumnya
            $rem5 = $reservationDateTime->copy()->subMinutes(5);
            if ($rem5->isFuture()) {
                $notifications[] = [
                    'user_id'       => $userId,
                    'reservasi_id'  => $reservasi->id,
                    'type'          => 'reminder_5_minutes',
                    'title'         => 'Pengingat Reservasi (5 Menit)',
                    'message'       => "5 menit lagi! Reservasi Anda akan dimulai.",
                    'data'          => json_encode(['kode_reservasi'=> $reservasi->kode_reservasi]),
                    'scheduled_at'  => $rem5,
                    'is_sent'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (! empty($notifications)) {
                CustomerNotification::insert($notifications);
                Log::info('Created ' . count($notifications) . ' reminders for reservation ' . $reservasi->id);
            }

        } catch (\Exception $e) {
            Log::error('Error creating reminders for reservation ' . $reservasi->id . ': ' . $e->getMessage());
        }
    }

    public static function createPaymentSuccessNotification(Reservasi $reservasi)
    {
        try {
            Log::info('Creating payment success notification for reservation: ' . $reservasi->id);

            $notification = CustomerNotification::create([
                'user_id'        => $reservasi->user_id,
                'reservasi_id'   => $reservasi->id,
                'type'           => CustomerNotification::TYPE_PAYMENT_SUCCESS,
                'title'          => 'Pembayaran Berhasil',
                'message'        => "Pembayaran untuk reservasi {$reservasi->kode_reservasi} telah berhasil.",
                'data'           => [
                    'kode_reservasi'=> $reservasi->kode_reservasi,
                    'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                ],
                'is_sent'        => true,
                'sent_at'        => now(),
            ]);

            Log::info('Payment notification created with ID: ' . $notification->id);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Error creating payment notification: ' . $e->getMessage());
        }
    }

    public static function createReservationConfirmedNotification(Reservasi $reservasi)
    {
        try {
            Log::info('Creating confirmation notification for reservation: ' . $reservasi->id);

            $notification = CustomerNotification::create([
                'user_id'       => $reservasi->user_id,
                'reservasi_id'  => $reservasi->id,
                'type'          => CustomerNotification::TYPE_RESERVATION_CONFIRMED,
                'title'         => 'Reservasi Dikonfirmasi',
                'message'       => "Reservasi Anda ({$reservasi->kode_reservasi}) telah dikonfirmasi.",
                'data'          => [
                    'kode_reservasi'=> $reservasi->kode_reservasi,
                ],
                'is_sent'       => true,
                'sent_at'       => now(),
            ]);

            Log::info('Confirmation notification created with ID: ' . $notification->id);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Error creating confirmation notification: ' . $e->getMessage());
        }
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

            return response()->json([
                'success'      => false,
                'message'      => 'Error fetching notifications',
                'data'         => [],
                'unread_count' => 0,
                'has_new'      => false,
            ], 500);
        }
    }

    public function getPendingNotifications()
    {
        $pending = CustomerNotification::pending()
            ->with([
                'pengguna:id,name,email',
                'reservasi:id,kode_reservasi'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $pending,
            'count'   => $pending->count(),
        ]);
    }

    public function sendPendingNotifications()
    {
        $pending = CustomerNotification::pending()->get();
        $sentCount = 0;

        foreach ($pending as $notification) {
            $notification->markAsSent();
            $sentCount++;
        }

        return response()->json([
            'success'   => true,
            'message'   => "{$sentCount} notifikasi berhasil dikirim.",
            'sent_count'=> $sentCount,
        ], 200);
    }

    public function destroy(Request $request, $notificationId)
    {
        $notification = CustomerNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus.'
        ], 200);
    }
}
