<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderService
{
    /**
     * Tampilkan dashboard (daftar menu & meja).
     */
    public function showDashboard()
    {
        try {
            $menusByCategory = Menu::where('is_available', true)
                ->orderBy('category')
                ->get()
                ->groupBy('category');

            $categories = Menu::select('category')->distinct()->pluck('category');
            $mejas = Meja::whereIn('status', ['tersedia', 'terisi'])
                ->orderBy('nomor_meja')
                ->get();

            return view('pelayan.dashboard', compact('menusByCategory', 'categories', 'mejas'));
        } catch (\Exception $e) {
            Log::error("Error loading dashboard: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat halaman.');
        }
    }

    /**
     * Buat reservasi + order items baru sekaligus.
     * Logika ini mengadaptasi kode storeOrder lama, namun sekarang
     * memprioritaskan 'discounted_price' jika tersedia, fallback ke 'price'.
     */
    public function createOrder(Request $request)
    {
        // (Validasi request sudah dilakukan di StoreOrderRequest sebelumnya)
        DB::beginTransaction();

        try {
            // 1) Ambil data meja utama
            $mejaUtama = Meja::findOrFail($request->meja_id);
            $pelayan   = Auth::user();
            $jumlahTamu = $request->jumlah_tamu;

            // 2) Pastikan meja utama berstatus tersedia
            if ($mejaUtama->status !== 'tersedia') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Meja nomor {$mejaUtama->nomor_meja} sedang tidak tersedia."
                ], 400);
            }

            // 3) Tentukan kombinasi meja jika kapasitas meja utama < jumlah tamu
            $combinedTables = [$mejaUtama->id];
            $totalCapacity = $mejaUtama->kapasitas;

            if ($totalCapacity < $jumlahTamu) {
                $mejaTambahanList = Meja::where('status', 'tersedia')
                    ->where('id', '!=', $mejaUtama->id)
                    ->where('area', $mejaUtama->area)
                    ->orderBy('kapasitas', 'asc')
                    ->get();

                foreach ($mejaTambahanList as $mejaTambahan) {
                    $combinedTables[] = $mejaTambahan->id;
                    $totalCapacity += $mejaTambahan->kapasitas;

                    if ($totalCapacity >= $jumlahTamu) {
                        break;
                    }
                }

                if ($totalCapacity < $jumlahTamu) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada kombinasi meja yang tersedia untuk menampung jumlah tamu.'
                    ], 400);
                }
            }

            // 4) Generate kode reservasi unik
            $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
            }

            // 5) Hitung subtotal berdasarkan discounted_price (jika ada), fallback ke price
            $subtotal = 0;
            $orderItemsData = [];
            foreach ($request->items as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);

                if (!$menu->is_available) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Menu '{$menu->name}' tidak tersedia saat ini."
                    ], 400);
                }

                // Gunakan discounted_price jika tidak null, jika null gunakan price
                $unitPrice = $menu->discounted_price !== null
                    ? $menu->discounted_price
                    : $menu->price;

                $itemSubtotal = $unitPrice * $itemData['quantity'];
                $subtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'menu_id'        => $itemData['menu_id'],
                    'quantity'       => $itemData['quantity'],
                    'price_at_order' => $unitPrice,
                    'total_price'    => $itemSubtotal,
                    'notes'          => $itemData['notes'] ?? null,
                ];
            }

            // 6) Hitung service charge dan tax (jika ada)
            $serviceCharge = 0; // Tambahkan logika jika perlu
            $tax = 0;           // Tambahkan logika jika perlu
            $finalTotalBill = $subtotal + $serviceCharge + $tax;

            // 7) Simpan data reservasi
            $reservasi = Reservasi::create([
                'kode_reservasi'   => $kodeReservasi,
                'meja_id'          => $mejaUtama->id,
                'combined_tables'  => json_encode($combinedTables),
                'user_id'          => null, // Jika tidak ada relasi user
                'staff_id'         => $pelayan->id,
                'nama_pelanggan'   => $request->nama_pelanggan ?? 'Walk-in Customer',
                'jumlah_tamu'      => $jumlahTamu,
                'waktu_kedatangan' => now(),
                'status'           => 'active_order',
                'source'           => 'dine_in',
                'kehadiran_status' => 'hadir',
                'total_bill'       => $finalTotalBill,
                'subtotal'         => $subtotal,
                'service_charge'   => $serviceCharge,
                'tax'              => $tax,
            ]);

            // 8) Simpan data order item
            foreach ($orderItemsData as $itemData) {
                Order::create([
                    'reservasi_id'   => $reservasi->id,
                    'menu_id'        => $itemData['menu_id'],
                    'user_id'        => $pelayan->id,
                    'quantity'       => $itemData['quantity'],
                    'price_at_order' => $itemData['price_at_order'],
                    'total_price'    => $itemData['total_price'],
                    'notes'          => $itemData['notes'],
                    'status'         => 'pending',
                ]);
            }

            // 9) Update status semua meja gabungan menjadi 'terisi'
            foreach ($combinedTables as $mejaId) {
                $meja = Meja::find($mejaId);
                if ($meja && $meja->status == 'tersedia') {
                    $meja->status = 'terisi';
                    $meja->current_reservasi_id = $reservasi->id;
                    $meja->save();
                }
            }

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'reservasi_id'    => $reservasi->id,
                'total_bill'      => $reservasi->total_bill,
                'kode_reservasi'  => $reservasi->kode_reservasi,
                'combined_tables' => $combinedTables,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                'Error creating order during AJAX store: '
                . $e->getMessage()
                . ' Stack trace: '
                . $e->getTraceAsString()
            );
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan ringkasan pesanan (order summary).
     */
    public function summary($id)
{
    try {
        $reservasi = Reservasi::with(['meja', 'orders.menu', 'staffYangMembuat'])
            ->findOrFail($id);

        // Format data untuk view
        $orderSummary = [
            'kode_reservasi' => $reservasi->kode_reservasi,
            'reservasi_id' => $reservasi->id,
            'nomor_meja' => $reservasi->meja->nomor_meja ?? 'N/A',
            'area_meja' => $reservasi->meja->area ?? 'N/A',
            'nama_pelanggan' => $reservasi->nama_pelanggan,
            'nama_pelayan' => $reservasi->staffYangMembuat->name ?? 'N/A',
            'waktu_pesan' => $reservasi->waktu_kedatangan,
            'total_keseluruhan' => $reservasi->total_bill,
            'items' => [],
            'combined_tables' => []
        ];

        // Format items pesanan
        foreach ($reservasi->orders as $order) {
            $orderSummary['items'][] = [
                'nama_menu' => $order->menu->name ?? 'N/A',
                'quantity' => $order->quantity,
                'harga_satuan' => $order->price_at_order,
                'subtotal' => $order->total_price,
                'catatan' => $order->notes
            ];
        }

        // Format meja gabungan
        if ($reservasi->combined_tables) {
            $ids = is_string($reservasi->combined_tables)
                ? json_decode($reservasi->combined_tables, true)
                : $reservasi->combined_tables;

            if (is_array($ids)) {
                $combinedTables = Meja::whereIn('id', $ids)
                    ->orderBy('nomor_meja')
                    ->get()
                    ->toArray();
                
                $orderSummary['combined_tables'] = $combinedTables;
            }
        }

        return view('pelayan.summary', [
            'orderSummary' => $orderSummary,
            'reservasi' => $reservasi,
            'title' => 'Ringkasan Pesanan'
        ]);

    } catch (\Exception $e) {
        Log::error("Error show summary: " . $e->getMessage());
        return redirect()->route('pelayan.dashboard')
            ->with('error', 'Gagal menampilkan ringkasan pesanan.');
    }
}

    /**
     * Tambahkan item ke order yang sudah berjalan.
     */
    public function addItems(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::findOrFail($id);
            $pelayan   = Auth::user();

            if (!in_array($reservasi->status, ['active_order', 'pending_payment'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak bisa menambahkan item ke reservasi dengan status {$reservasi->status}."
                ], 400);
            }

            $newSubtotal = 0;
            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $unitPrice = $menu->discounted_price ?? $menu->price;
                $lineTotal = $unitPrice * $item['quantity'];
                $newSubtotal += $lineTotal;

                Order::create([
                    'reservasi_id'   => $reservasi->id,
                    'menu_id'        => $menu->id,
                    'user_id'        => $pelayan->id,
                    'quantity'       => $item['quantity'],
                    'price_at_order' => $unitPrice,
                    'total_price'    => $lineTotal,
                    'notes'          => $item['notes'] ?? null,
                    'status'         => 'pending',
                ]);
            }

            $reservasi->subtotal += $newSubtotal;
            $reservasi->service_charge = (int)($reservasi->subtotal * 0.10);
            $reservasi->tax = (int)(($reservasi->subtotal + $reservasi->service_charge) * 0.11);
            $reservasi->total_bill = $reservasi->subtotal
                + $reservasi->service_charge
                + $reservasi->tax;
            $reservasi->save();

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Item berhasil ditambahkan ke pesanan.',
                'total_bill' => $reservasi->total_bill,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal tambah item: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item: ' . $e->getMessage()
            ], 500);
        }
    }
}
