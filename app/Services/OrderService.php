<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Reservasi;
use App\Models\Meja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderService
{
    public function storeOrder($request)
    {
        DB::beginTransaction();

        try {
            // 1. Ambil meja utama dan validasi status
            $mejaUtama  = Meja::findOrFail($request->meja_id);
            $pelayan    = Auth::user();
            $jumlahTamu = (int) $request->jumlah_tamu;

            if ($mejaUtama->status !== 'tersedia') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Meja nomor {$mejaUtama->nomor_meja} sedang tidak tersedia."
                ];
            }

            // 2. Inisialisasi gabungan meja dan total kapasitas
            $combinedTables = [$mejaUtama->id];
            $totalCapacity  = (int) $mejaUtama->kapasitas;

            // 3. Jika kapasitas meja utama belum cukup, cari kombinasi meja tambahan
            if ($totalCapacity < $jumlahTamu) {
                // Hitung sisa kursi yang dibutuhkan setelah meja utama
                $needed = $jumlahTamu - $totalCapacity;

                // Ambil semua kandidat meja tambahan (status 'tersedia', area sama, bukan meja utama)
                $mejaTambahanList = Meja::where('status', 'tersedia')
                    ->where('id', '!=', $mejaUtama->id)
                    ->where('area', $mejaUtama->area)
                    ->get(['id', 'kapasitas']);

                // Ubah menjadi array sederhana: [ ['id'=>.., 'kapasitas'=>..], ... ]
                $candidates = $mejaTambahanList->map(function($m) {
                    return [
                        'id'        => $m->id,
                        'kapasitas' => (int) $m->kapasitas,
                    ];
                })->toArray();

                // Persiapkan variabel global untuk mencari subset terbaik
                $bestSum    = PHP_INT_MAX;
                $bestSubset = [];

                /**
                 * Backtracking (DFS) untuk menemukan kombinasi meja tambahan
                 * yang total kapasitasnya >= $needed dan seminimal mungkin.
                 *
                 * @param int   $idx
                 * @param int   $currentSum
                 * @param array $currentSubset
                 */
                $dfs = function($idx, $currentSum, $currentSubset)
                    use (&$dfs, $needed, &$bestSum, &$bestSubset, $candidates)
                {
                    // Jika sudah mencukupi kebutuhan
                    if ($currentSum >= $needed) {
                        // Simpan jika lebih efisien (smaller sum)
                        if ($currentSum < $bestSum) {
                            $bestSum    = $currentSum;
                            $bestSubset = $currentSubset;
                        }
                        // Langsung return karena tidak perlu menambahkan lebih banyak
                        return;
                    }

                    // Jika sudah melewati batas kandidat, stop
                    if ($idx >= count($candidates)) {
                        return;
                    }

                    // Pruning: jika currentSum sudah ≥ bestSum, tidak usah lanjut
                    if ($currentSum >= $bestSum) {
                        return;
                    }

                    // 1) Pilih kandidat idx
                    $m = $candidates[$idx];
                    $subsetWith = $currentSubset;
                    $subsetWith[] = $m['id'];
                    $dfs($idx + 1, $currentSum + $m['kapasitas'], $subsetWith);

                    // 2) Lewati kandidat idx
                    $dfs($idx + 1, $currentSum, $currentSubset);
                };

                // Jalankan DFS mulai dari idx=0, sum=0, subset=[]
                $dfs(0, 0, []);

                // Jika bestSum tetap PHP_INT_MAX, tidak ada kombinasi yang cocok
                if ($bestSum === PHP_INT_MAX) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Tidak ada kombinasi meja yang tersedia untuk menampung jumlah tamu.'
                    ];
                }

                // Tambahkan meja tambahan dari bestSubset ke combinedTables
                foreach ($bestSubset as $mejaId) {
                    $combinedTables[] = $mejaId;
                    $totalCapacity   += Meja::find($mejaId)->kapasitas;
                }
            }

            // 4. Generate kode reservasi unik
            $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
            }

            // 5. Proses order items (hitung subtotal dan kumpulkan data)
            $subtotal       = 0;
            $orderItemsData = [];
            foreach ($request->items as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);
                if (!$menu->is_available) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Menu '{$menu->name}' tidak tersedia saat ini."
                    ];
                }

                $unitPrice    = $menu->discounted_price ?? $menu->price;
                $itemSubtotal = $unitPrice * $itemData['quantity'];
                $subtotal    += $itemSubtotal;

                $orderItemsData[] = [
                    'menu_id'        => $itemData['menu_id'],
                    'quantity'       => $itemData['quantity'],
                    'price_at_order' => $unitPrice,
                    'total_price'    => $itemSubtotal,
                    'notes'          => $itemData['notes'] ?? null,
                ];
            }

            // 6. Hitung total bill (serviceCharge & tax masih 0 sementara)
            $serviceCharge   = 0;
            $tax             = 0;
            $finalTotalBill  = $subtotal + $serviceCharge + $tax;

            // 7. Simpan data reservasi
            $reservasi = Reservasi::create([
                'kode_reservasi'   => $kodeReservasi,
                'meja_id'          => $mejaUtama->id,
                'combined_tables'  => json_encode($combinedTables),
                'user_id'          => null,
                'staff_id'         => $pelayan->id,
                'nama_pelanggan'   => $request->nama_pelanggan ?? 'Walk-in Customer',
                'jumlah_tamu'      => $jumlahTamu,
                'waktu_kedatangan' => now(),    
                'status'           => 'pending_payment',
                'source'           => 'dine_in',
                'kehadiran_status' => 'hadir',
                'total_bill'       => $finalTotalBill,
                'subtotal'         => $subtotal,
                'service_charge'   => $serviceCharge,
                'tax'              => $tax,
            ]);

            // 8. Simpan detail order (order items)
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

            // 9. Update status semua meja (utama + tambahan) menjadi 'terisi'
            foreach ($combinedTables as $mejaId) {
                $meja = Meja::find($mejaId);
                if ($meja && $meja->status === 'tersedia') {
                    $meja->status             = 'terisi';
                    $meja->current_reservasi_id = $reservasi->id;
                    $meja->save();
                }
            }

            DB::commit();

            // 10. Kembalikan respon sukses
            return [
                'success'         => true,
                'message'         => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'reservasi_id'    => $reservasi->id,
                'total_bill'      => $reservasi->total_bill,
                'kode_reservasi'  => $reservasi->kode_reservasi,
                'combined_tables' => $combinedTables,
            ];
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage(),
            ];
        }
    }

    public function addItemsToOrder($request, $reservasi)
    {
        DB::beginTransaction();
        try {
            $pelayan = Auth::user();
            if (!in_array($reservasi->status, ['active_order', 'pending_payment'])) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Tidak bisa menambahkan item ke reservasi dengan status ' . $reservasi->status,
                ];
            }

            $newItemsSubtotal = 0;

            foreach ($request->items as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);
                if (!$menu->is_available) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Menu '{$menu->name}' tidak tersedia saat ini.",
                    ];
                }
                $itemSubtotal      = $menu->price * $itemData['quantity'];
                $newItemsSubtotal += $itemSubtotal;

                Order::create([
                    'reservasi_id'   => $reservasi->id,
                    'menu_id'        => $itemData['menu_id'],
                    'user_id'        => $pelayan->id,
                    'quantity'       => $itemData['quantity'],
                    'price_at_order' => $menu->price,
                    'total_price'    => $itemSubtotal,
                    'notes'          => $itemData['notes'] ?? null,
                    'status'         => 'pending',
                ]);
            }

            $currentSubtotal      = $reservasi->subtotal ?? $reservasi->orders->sum('total_price');
            $currentServiceCharge = $reservasi->service_charge ?? 0;
            $currentTax           = $reservasi->tax ?? 0;

            $updatedSubtotal = $currentSubtotal + $newItemsSubtotal;

            $serviceChargeRate = 0.10; // 10%
            $taxRate           = 0.11; // 11%

            $updatedServiceCharge = (int) ($updatedSubtotal * $serviceChargeRate);
            $totalAfterService    = $updatedSubtotal + $updatedServiceCharge;
            $updatedTax           = (int) ($totalAfterService * $taxRate);

            $updatedTotalBill = $updatedSubtotal + $updatedServiceCharge + $updatedTax;

            $reservasi->total_bill      = $updatedTotalBill;
            $reservasi->subtotal        = $updatedSubtotal;
            $reservasi->service_charge  = $updatedServiceCharge;
            $reservasi->tax             = $updatedTax;
            $reservasi->save();

            DB::commit();

            return [
                'success'               => true,
                'message'               => 'Item berhasil ditambahkan ke pesanan.',
                'reservasi_id'          => $reservasi->id,
                'total_bill'            => $reservasi->total_bill,
                'kode_reservasi'        => $reservasi->kode_reservasi,
                'updated_subtotal'      => $reservasi->subtotal,
                'updated_service_charge'=> $reservasi->service_charge,
                'updated_tax'           => $reservasi->tax,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding items to order: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menambahkan item ke pesanan: ' . $e->getMessage(),
            ];
        }
    }
}
