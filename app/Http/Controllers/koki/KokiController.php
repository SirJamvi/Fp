<?php

namespace App\Http\Controllers\Koki;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Reservasi;
use App\Models\Menu;
use App\Models\Meja; // Pastikan model Meja diimport jika belum
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

    /**
     * API untuk mengambil daftar pesanan dan summary untuk koki, dikelompokkan per reservasi.
     * Mengembalikan data dalam format JSON.
     */
    public function getOrders(Request $request)
    {
        try {
            // Eager load relasi 'menu' (untuk gambar dan nama) dan 'reservasi.meja'
            // Pastikan relasi 'meja' di dalam 'reservasi' sudah benar dan kolom 'area' ada di tabel 'meja'
            $allOrders = Order::with(['menu', 'reservasi.meja'])
                               // Mengambil status 'pending', 'preparing', 'completed', dan 'cancelled'
                               // agar bisa dihitung untuk summary cards di frontend
                               ->whereIn('status', ['pending', 'preparing', 'completed', 'cancelled'])
                               ->whereDate('created_at', Carbon::today()) // Hanya pesanan yang dibuat hari ini
                               ->orderBy('created_at', 'asc')
                               ->get();

            // Hitung jumlah pesanan berdasarkan status untuk card summary
            $newOrdersCount = $allOrders->where('status', 'pending')->count();
            $preparingOrdersCount = $allOrders->where('status', 'preparing')->count();
            $completedOrdersCount = $allOrders->where('status', 'completed')->count(); // Hitungan pesanan selesai hari ini

            // Kelompokkan orders berdasarkan reservasi_id
            $groupedOrders = $allOrders->groupBy('reservasi_id');

            $formattedReservations = [];

            foreach ($groupedOrders as $reservasiId => $ordersInReservation) {
                $firstOrder = $ordersInReservation->first(); // Ambil order pertama untuk detail reservasi
                
                // Jika reservasi atau meja tidak ditemukan (misalnya data tidak konsisten), lewati
                if (!$firstOrder->reservasi || !$firstOrder->reservasi->meja) {
                    continue;
                }

                // Tentukan status reservasi secara keseluruhan untuk tombol aksi
                $reservationStatus = 'completed'; // Default, akan di-override
                foreach ($ordersInReservation as $order) {
                    if ($order->status === 'pending') {
                        $reservationStatus = 'pending';
                        break; // Cukup satu pending, status reservasi adalah pending
                    } elseif ($order->status === 'preparing') {
                        $reservationStatus = 'preparing'; // Set ke preparing, tapi lanjutkan cek jika ada pending
                    }
                }

                // Jika semua order di reservasi ini sudah 'completed' atau 'cancelled',
                // maka reservasi ini tidak perlu ditampilkan di daftar aktif koki.
                if ($ordersInReservation->every(function($order) {
                    return in_array($order->status, ['completed', 'cancelled']);
                })) {
                    continue;
                }


                $items = [];
                foreach ($ordersInReservation as $order) {
                    // Hanya tampilkan item yang belum 'completed' atau 'cancelled' untuk koki
                    if (in_array($order->status, ['pending', 'preparing'])) {
                        $items[] = [
                            'order_id' => $order->id, // ID order item individual
                            'menu_name' => $order->menu->name ?? 'Menu Tidak Ditemukan',
                            'menu_image' => $order->menu->image_url ?? asset('assets/img/default-food.png'), // Menggunakan image_url
                            'quantity' => $order->quantity,
                            'notes' => $order->notes,
                            'status' => $order->status, // Status individu item
                        ];
                    }
                }

                // Pastikan ada item yang aktif sebelum ditambahkan ke daftar reservasi
                if (!empty($items)) {
                    // Logika untuk menggabungkan nomor meja dan area
                    $tableNumber = $firstOrder->reservasi->meja->nomor_meja ?? 'N/A';
                    $tableArea = $firstOrder->reservasi->meja->area ?? ''; // Ambil area meja
                    
                    $tableDisplay = $tableNumber;
                    if ($tableArea) {
                        $tableDisplay .= ' (' . $tableArea . ')'; // Gabungkan jika area ada
                    }

                    $formattedReservations[] = [
                        'reservasi_id' => $firstOrder->reservasi->id,
                        'kode_reservasi' => $firstOrder->reservasi->kode_reservasi,
                        'table_display' => $tableDisplay, // Mengirim string gabungan ke frontend
                        'ordered_at' => $firstOrder->created_at, // Waktu pesan dari order pertama
                        'current_status' => $reservationStatus, // Status agregat untuk reservasi
                        'status_badge_class' => $this->getStatusBadgeClass($reservationStatus),
                        'items' => $items, // Daftar item menu dalam reservasi ini
                    ];
                }
            }

            // Urutkan reservasi berdasarkan waktu pesan
            usort($formattedReservations, function($a, $b) {
                return $a['ordered_at'] <=> $b['ordered_at'];
            });


            return response()->json([
                'success' => true,
                'reservations' => $formattedReservations, // Kirim data yang sudah dikelompokkan
                'summary' => [
                    'new_orders' => $newOrdersCount,
                    'preparing_orders' => $preparingOrdersCount,
                    'completed_orders' => $completedOrdersCount,
                    'low_stock_items' => 0, // Anda perlu implementasi ini jika ingin fitur stok
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching koki orders: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Gagal memuat pesanan dapur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API untuk memperbarui status semua item pesanan dalam satu reservasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservasi  $reservasi  Model Reservasi yang akan diupdate (Route Model Binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderStatus(Request $request, Reservasi $reservasi)
    {
        // Validasi status: 'ready' diganti menjadi 'completed' agar konsisten dengan frontend
        $request->validate([
            'status' => 'required|in:preparing,completed,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $newStatus = $request->status;
            $message = '';

            // Perbarui status semua Order yang terkait dengan Reservasi ini
            // Hanya update order yang statusnya belum 'completed' atau 'cancelled'
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
                } elseif ($newStatus === 'cancelled') { // Koki bisa membatalkan seluruh reservasi
                    $order->status = $newStatus;
                    $message = 'Pesanan berhasil dibatalkan.';
                } else {
                    // Jika ada transisi status yang tidak valid untuk item tertentu,
                    // bisa diabaikan atau log warning. Untuk simplicity, kita abaikan.
                    continue;
                }
                $order->save();
                Log::info("Order ID {$order->id} status updated from {$oldStatus} to {$newStatus} for Reservasi ID {$reservasi->id}.");
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'reservasi_id' => $reservasi->id, // Kirim ID reservasi saja
                'new_status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating order status for Reservasi ID {$reservasi->id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status pesanan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper function to get Bootstrap badge class based on status.
     */
    private function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'pending':
                return 'bg-danger'; // Merah untuk baru
            case 'preparing':
                return 'bg-warning'; // Kuning untuk sedang diproses
            case 'completed': // 'ready' diganti 'completed'
                return 'bg-success'; // Hijau untuk selesai
            case 'served':
                return 'bg-primary'; // Biru untuk sudah disajikan (opsional)
            case 'cancelled':
                return 'bg-secondary'; // Abu-abu untuk dibatalkan
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