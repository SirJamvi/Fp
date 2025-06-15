<?php

namespace App\Services;

use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Untuk pembayaran awal (tunai atau QRIS penuh).
     */
    public function processPayment(Request $request, $reservasi_id)
    {
        DB::beginTransaction();

        try {
            $reservasi = Reservasi::with('orders.menu')->findOrFail($reservasi_id);

            // Jika sudah lunas
            if (in_array($reservasi->status, ['paid', 'selesai'])) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Pesanan ini sudah lunas.'];
            }

            $totalBill = $reservasi->total_bill;
            $change    = 0;
            $snapToken = null;

            // 1) Tunai
            if ($request->payment_method === 'tunai') {
                $uang = $request->input('uang_diterima');
                if (is_null($uang) || $uang < $totalBill) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Jumlah tunai kurang dari total tagihan.'];
                }

                $change = $uang - $totalBill;
                $reservasi->payment_status         = 'paid';
                $reservasi->payment_method         = 'tunai';
                $reservasi->amount_paid            = $uang;
                $reservasi->change_given           = $change;
                $reservasi->sisa_tagihan_reservasi = 0;
                $reservasi->waktu_selesai          = now();
                $reservasi->status                 = 'selesai';
                $reservasi->save();
            }
            // 2) QRIS penuh
            elseif ($request->payment_method === 'qris') {
                // Build item_details + transaction_details...
                $item_details = [];
                foreach ($reservasi->orders as $order) {
                    $item_details[] = [
                        'id'       => $order->menu->id,
                        'price'    => (int) $order->price_at_order,
                        'quantity' => (int) $order->quantity,
                        'name'     => $order->menu->name,
                    ];
                }

                $transaction_details = [
                    'order_id'     => $reservasi->kode_reservasi . '-' . time(),
                    'gross_amount' => (int) $totalBill,
                ];

                $params = [
                    'transaction_details' => $transaction_details,
                    'item_details'        => $item_details,
                    'customer_details'    => [
                        'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
                    ],
                    'callbacks' => [
                        'finish' => false, // jangan pakai callback Midtrans
                    ],
                    'finish_redirect_url' => route('pelayan.order.summary', $reservasi->id),
                ];



                try {
                    $snapToken = Snap::getSnapToken($params);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Midtrans SnapToken Error: '.$e->getMessage());
                    return ['success' => false, 'message' => 'Gagal generate Snap token.'];
                }

                // Simpan token & pending status saja
                $reservasi->payment_method         = 'qris';
                $reservasi->payment_token          = $snapToken;
                $reservasi->payment_status         = 'selesai';
                $reservasi->status                 = 'selesai';
                // status dan sisa tetap menunggu callback JS
                $reservasi->payment_amount         = $totalBill;
                $reservasi->sisa_tagihan_reservasi = 0;
                $reservasi->save();
            }

            DB::commit();

            $response = ['success' => true, 'change' => $change];
            if ($snapToken) {
                $response['snap_token']  = $snapToken;
                $response['redirect_url'] = route('pelayan.reservasi.bayarSisa.qris', $reservasi->id);
            } else {
                $response['redirect_url'] = route('pelayan.order.summary', $reservasi->id);
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processPayment: '.$e->getMessage());
            return ['success' => false, 'message' => 'Gagal memproses pembayaran.'];
        }
    }

    /**
     * Tampilkan form bayar sisa (tunai + opsi QRIS/VA).
     */
    public function bayarSisa($id)
{
    $reservasi = Reservasi::with('orders')->findOrFail($id);
    $total     = $reservasi->orders->sum('total_price');
    $pajakPersen = 10;
    $pajakNominal = $total * ($pajakPersen / 100);
    $totalSetelahPajak = $total + $pajakNominal;

    $dibayar = $totalSetelahPajak - ($reservasi->sisa_tagihan_reservasi ?? $totalSetelahPajak);
    $sisa    = $reservasi->sisa_tagihan_reservasi ?? $totalSetelahPajak;

    return [
        'success'           => true,
        'reservasi'         => $reservasi,
        'totalTagihan'      => $total,
        'pajakNominal'      => $pajakNominal,
        'pajakPersen'       => $pajakPersen,
        'totalSetelahPajak' => $totalSetelahPajak,
        'totalDibayar'      => $dibayar,
        'sisa'              => $sisa,
    ];
}


    /**
     * Proses form bayar sisa; tunai langsung update, QRIS generate Snap token.
     */
    public function bayarSisaPost(Request $request, $id)
{
    $reservasi = Reservasi::with('orders','pengguna')->findOrFail($id);
    $request->validate([
        'jumlah_dibayar' => 'required|numeric|min:1',
        'metode'         => 'required|in:tunai,qris',
    ]);

    $total = $reservasi->orders->sum('total_price');
    $pajak = $total * 0.10;
    $totalSetelahPajak = $total + $pajak;

    $sisa  = $reservasi->sisa_tagihan_reservasi ?? $totalSetelahPajak;
    $bayar = (int) round($request->jumlah_dibayar);

    if ($bayar > $sisa) {
        return ['success' => false, 'message' => 'Pembayaran melebihi sisa tagihan.'];
    }

    // Tunai
    if ($request->metode === 'tunai') {
        DB::beginTransaction();
        try {
            $reservasi->sisa_tagihan_reservasi = $sisa - $bayar;
            $reservasi->payment_method         = 'tunai';
            if ($reservasi->sisa_tagihan_reservasi <= 0) {
                $reservasi->sisa_tagihan_reservasi = 0;
                $reservasi->status                 = 'paid';
                $reservasi->payment_status         = 'paid';
                $reservasi->waktu_selesai          = now();
            }
            $reservasi->save();
            DB::commit();
            return ['success' => true, 'message' => 'Pembayaran sisa berhasil.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Gagal simpan pembayaran: '.$e->getMessage()];
        }
    }

    // QRIS (partial)
    if ($request->metode === 'qris') {
        DB::beginTransaction();
        try {
            $grossAmount = $bayar;
            $item_details = [[
                'id'       => 'part-'.$reservasi->kode_reservasi,
                'price'    => $grossAmount,
                'quantity' => 1,
                'name'     => 'Pembayaran Sisa #' . $reservasi->kode_reservasi
            ]];
            $customer = [
                'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
                'email'      => $reservasi->pengguna->email ?? null,
                'phone'      => $reservasi->pengguna->phone ?? null,
            ];
            $transaction_details = [
                'order_id'     => $reservasi->kode_reservasi.'-PART-'.time(),
                'gross_amount' => $grossAmount,
            ];

            $params = [
                'transaction_details' => $transaction_details,
                'item_details'        => $item_details,
                'customer_details'    => $customer,
                'callbacks'           => ['finish' => false],
                'finish_redirect_url' => route('pelayan.order.summary', $reservasi->id),
            ];

            $snapToken = Snap::getSnapToken($params);

            $reservasi->payment_method         = 'qris';
            $reservasi->payment_token          = $snapToken;
            $reservasi->payment_status         = 'pending';
            $reservasi->payment_amount         = $grossAmount;
            $reservasi->sisa_tagihan_reservasi = max(0, $sisa - $grossAmount);
            $reservasi->save();

            DB::commit();
            return [
                'success'    => true,
                'snap_token' => $snapToken,
                'redirect'   => route('pelayan.reservasi.bayarSisa.qris', $reservasi->id),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Gagal generate Snap: '.$e->getMessage()];
        }
    }

    return ['success' => false, 'message' => 'Metode tidak dikenali.'];
}


    /**
     * Dipanggil via AJAX oleh JS Snap onSuccess untuk menandai lunas.
     */
    public function settlePayment(Request $request, $id)
    {
        // Hanya AJAX
        if (! $request->ajax()) {
            abort(403);
        }
        $reservasi = Reservasi::findOrFail($id);
        if (! in_array($reservasi->status, ['paid','selesai'])) {
            $reservasi->payment_status         = 'paid';
            $reservasi->sisa_tagihan_reservasi = 0;
            $reservasi->status                 = 'paid';
            $reservasi->waktu_selesai          = now();
            $reservasi->save();
        }
        return response()->json(['success' => true]);
    }

    public function showQrisPayment($id)
{
    $reservasi = Reservasi::findOrFail($id);

    if (! $reservasi->payment_token || $reservasi->payment_status !== 'pending') {
        throw new \Exception('Tidak ada transaksi QRIS yang sedang berjalan.');
    }

    return [
        'snap_token'     => $reservasi->payment_token,
        'reservasi'      => $reservasi,
        'jumlah_dibayar' => $reservasi->payment_amount,
    ];
}

}
