<?php

namespace App\Http\Controllers\Koki;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Reservasi;
use App\Models\Menu;
use App\Models\Meja;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KokiController extends Controller
{
    public function index()
    {
        return view('koki.dashboard', [
            'title' => 'Dashboard Koki',
        ]);
    }

    public function getOrders(Request $request)
    {
        try {
            // Eager load dengan relasi many-to-many yang benar
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
                
                // Check if reservasi exists
                if (!$firstOrder->reservasi) {
                    continue;
                }

                // Determine reservation status
                $reservationStatus = 'completed';
                foreach ($ordersInReservation as $order) {
                    if ($order->status === 'pending') {
                        $reservationStatus = 'pending';
                        break;
                    } elseif ($order->status === 'preparing') {
                        $reservationStatus = 'preparing';
                    }
                }

                // Skip completed/cancelled reservations
                if ($ordersInReservation->every(function($order) {
                    return in_array($order->status, ['completed', 'cancelled']);
                })) {
                    continue;
                }

                $items = [];
                foreach ($ordersInReservation as $order) {
                    if (in_array($order->status, ['pending', 'preparing'])) {
                        $items[] = [
                            'order_id' => $order->id,
                            'menu_name' => $order->menu->name ?? 'Menu Tidak Ditemukan',
                            'menu_image' => $order->menu->image_url ?? asset('assets/img/default-food.png'),
                            'quantity' => $order->quantity,
                            'notes' => $order->notes,
                            'status' => $order->status,
                        ];
                    }
                }

                if (!empty($items)) {
                    // Handle many-to-many relationship for meja
                    $mejaCollection = $firstOrder->reservasi->meja; // This returns a collection
                    
                    // Get table display from collection
                    $tableDisplay = 'N/A';
                    if ($mejaCollection && $mejaCollection->count() > 0) {
                        // If multiple tables, combine them
                        $tableNumbers = $mejaCollection->map(function($meja) {
                            $display = $meja->nomor_meja ?? 'N/A';
                            if ($meja->area) {
                                $display .= ' (' . $meja->area . ')';
                            }
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

            usort($formattedReservations, function($a, $b) {
                return $a['ordered_at'] <=> $b['ordered_at'];
            });

            return response()->json([
                'success' => true,
                'reservations' => $formattedReservations,
                'summary' => [
                    'new_orders' => $newOrdersCount,
                    'preparing_orders' => $preparingOrdersCount,
                    'completed_orders' => $completedOrdersCount,
                    'low_stock_items' => 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching koki orders: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Gagal memuat pesanan dapur: ' . $e->getMessage()], 500);
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

            $ordersToUpdate = $reservasi->orders()->whereIn('status', ['pending', 'preparing'])->get();

            if ($ordersToUpdate->isEmpty()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Tidak ada item pesanan yang perlu diperbarui dalam reservasi ini.'], 400);
            }

            foreach ($ordersToUpdate as $order) {
                $oldStatus = $order->status;

                if ($oldStatus === 'pending' && $newStatus === 'preparing') {
                    $order->status = $newStatus;
                    $message = 'Pesanan berhasil diubah menjadi Sedang Diproses.';
                } elseif ($oldStatus === 'preparing' && $newStatus === 'completed') {
                    $order->status = $newStatus;
                    $message = 'Pesanan berhasil diubah menjadi Selesai.';
                } elseif ($newStatus === 'cancelled') {
                    $order->status = $newStatus;
                    $message = 'Pesanan berhasil dibatalkan.';
                } else {
                    continue;
                }
                $order->save();
                Log::info("Order ID {$order->id} status updated from {$oldStatus} to {$newStatus} for Reservasi ID {$reservasi->id}.");
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'reservasi_id' => $reservasi->id,
                'new_status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating order status for Reservasi ID {$reservasi->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status pesanan: ' . $e->getMessage()], 500);
        }
    }

    private function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'pending':
                return 'bg-danger';
            case 'preparing':
                return 'bg-warning';
            case 'completed':
                return 'bg-success';
            case 'served':
                return 'bg-primary';
            case 'cancelled':
                return 'bg-secondary';
            default:
                return 'bg-secondary';
        }
    }

    public function daftarPesanan()
    {
    }

    public function stokBahan()
    {
    }
}