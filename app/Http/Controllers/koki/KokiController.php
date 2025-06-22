<?php

namespace App\Http\Controllers\Koki;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Reservasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// START: Perubahan - Import NotificationController
use App\Http\Controllers\Customer\NotificationController;
// END: Perubahan

class KokiController extends Controller
{
    // ... fungsi index() dan getOrders() tidak berubah ...
    public function index()
    {
        return view('koki.dashboard', [
            'title' => 'Dashboard Koki',
        ]);
    }

    public function getOrders(Request $request)
    {
        try {
            $allOrders = Order::with(['menu', 'reservasi.meja'])
                               ->whereIn('status', ['pending', 'preparing', 'completed', 'cancelled'])
                               ->whereDate('created_at', Carbon::today())
                               ->orderBy('created_at', 'asc')
                               ->get();

            $newOrdersCount = $allOrders->where('status', 'pending')->count();
            $preparingOrdersCount = $allOrders->where('status', 'preparing')->count();
            $completedOrdersCount = $allOrders->where('status', 'completed')->count();

            $groupedOrders = $allOrders->groupBy('reservasi_id');
            $formattedReservations = [];

            foreach ($groupedOrders as $reservasiId => $ordersInReservation) {
                $firstOrder = $ordersInReservation->first();
                if (!$firstOrder->reservasi) continue;

                $reservationStatus = 'completed';
                foreach ($ordersInReservation as $order) {
                    if ($order->status === 'pending') {
                        $reservationStatus = 'pending';
                        break;
                    } elseif ($order->status === 'preparing') {
                        $reservationStatus = 'preparing';
                    }
                }

                if ($ordersInReservation->every(fn($order) => in_array($order->status, ['completed', 'cancelled']))) {
                    continue;
                }

                $items = [];
                foreach ($ordersInReservation as $order) {
                    if (in_array($order->status, ['pending', 'preparing'])) {
                        $items[] = [
                            'order_id' => $order->id,
                            'menu_name' => $order->menu->name ?? 'N/A',
                            'menu_image' => $order->menu->image_url ?? '',
                            'quantity' => $order->quantity,
                            'notes' => $order->notes,
                            'status' => $order->status,
                        ];
                    }
                }

                if (!empty($items)) {
                    $tableDisplay = 'N/A';
                    if ($firstOrder->reservasi->meja && $firstOrder->reservasi->meja->count() > 0) {
                        $tableNumbers = $firstOrder->reservasi->meja->map(function($meja) {
                            $display = $meja->nomor_meja ?? 'N/A';
                            if ($meja->area) $display .= ' (' . $meja->area . ')';
                            return $display;
                        })->toArray();
                        $tableDisplay = implode(', ', $tableNumbers);
                    }

                    $formattedReservations[] = [
                        'reservasi_id' => $firstOrder->reservasi->id,
                        'kode_reservasi' => $firstOrder->reservasi->kode_reservasi,
                        'table_display' => $tableDisplay,
                        'ordered_at' => $firstOrder->created_at,
                        'current_status' => $reservationStatus,
                        'status_badge_class' => $this->getStatusBadgeClass($reservationStatus),
                        'items' => $items,
                    ];
                }
            }

            usort($formattedReservations, fn($a, $b) => $a['ordered_at'] <=> $b['ordered_at']);

            return response()->json([
                'success' => true,
                'reservations' => $formattedReservations,
                'summary' => [
                    'new_orders' => $newOrdersCount,
                    'preparing_orders' => $preparingOrdersCount,
                    'completed_orders' => $completedOrdersCount,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching koki orders: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat pesanan dapur.'], 500);
        }
    }

    public function updateOrderStatus(Request $request, Reservasi $reservasi)
    {
        $request->validate([
            'status' => 'required|in:preparing,completed,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $newStatus = $request->status;
            $message = '';
            $statusChanged = false;

            // Pastikan kita memuat relasi user karena dibutuhkan untuk notifikasi
            $reservasi->load('user');

            if (!$reservasi->user) {
                DB::rollBack();
                Log::warning("Reservasi ID {$reservasi->id} tidak memiliki user terkait.");
                return response()->json(['success' => false, 'message' => 'Reservasi tidak memiliki data pelanggan.'], 404);
            }

            $ordersToUpdate = $reservasi->orders()->whereIn('status', ['pending', 'preparing'])->get();

            if ($ordersToUpdate->isEmpty()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Tidak ada item pesanan yang perlu diperbarui.'], 400);
            }

            foreach ($ordersToUpdate as $order) {
                $oldStatus = $order->status;

                // Terapkan perubahan status berdasarkan logika
                if ($oldStatus === 'pending' && $newStatus === 'preparing') {
                    $order->status = $newStatus;
                    $message = 'Pesanan sekarang sedang disiapkan.';
                    $statusChanged = true;
                } elseif ($oldStatus === 'preparing' && $newStatus === 'completed') {
                    $order->status = $newStatus;
                    $message = 'Pesanan telah selesai disiapkan.';
                    $statusChanged = true;
                } elseif ($newStatus === 'cancelled') { // Bisa membatalkan dari pending atau preparing
                    $order->status = $newStatus;
                    $message = 'Pesanan telah dibatalkan.';
                    $statusChanged = true;
                }
                
                if ($statusChanged) {
                    $order->save();
                    Log::info("Order ID {$order->id} status updated from {$oldStatus} to {$newStatus} for Reservasi ID {$reservasi->id}.");
                }
            }
            
            DB::commit();

            // START: Perubahan - Kirim notifikasi ke pelanggan setelah commit berhasil
            if ($statusChanged) {
                try {
                    NotificationController::createOrderStatusUpdateNotification($reservasi, $newStatus);
                } catch (\Exception $e) {
                    // Log error jika pengiriman notifikasi gagal, tapi jangan gagalkan respons ke koki
                    Log::error("Gagal mengirim notifikasi untuk Reservasi ID {$reservasi->id} setelah update status: " . $e->getMessage());
                }
            }
            // END: Perubahan

            return response()->json([
                'success' => true,
                'message' => $message,
                'reservasi_id' => $reservasi->id,
                'new_status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating order status for Reservasi ID {$reservasi->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status pesanan.'], 500);
        }
    }

    private function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'pending': return 'bg-danger';
            case 'preparing': return 'bg-warning';
            case 'completed': return 'bg-success';
            case 'cancelled': return 'bg-secondary';
            default: return 'bg-secondary';
        }
    }
}