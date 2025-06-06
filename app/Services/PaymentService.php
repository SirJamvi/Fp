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
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

   public function processPayment(Request $request, $reservasi_id)
{
    DB::beginTransaction();

    try {
        $reservasi = Reservasi::with('meja', 'orders.menu')->findOrFail($reservasi_id);
        $totalBill  = $reservasi->total_bill;
        $changeGiven = 0;
        $snapToken   = null;

        // Pastikan status awalnya bukan "paid" ataupun "selesai"
        if ($reservasi->payment_status === 'paid' || $reservasi->status === 'selesai') {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Pesanan ini sudah lunas.'
            ];
        }

        // ─────── Pembayaran Tunai ───────
        if ($request->payment_method === 'tunai') {
            // Di JS kita mengirim payload.uang_diterima, bukan amount_paid
            $uangDiterima = $request->input('uang_diterima');  

            if (is_null($uangDiterima) || $uangDiterima < $totalBill) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Jumlah uang tunai yang dibayarkan kurang dari total tagihan.'
                ];
            }

            $changeGiven = $uangDiterima - $totalBill;
            $reservasi->payment_status           = 'paid';
            $reservasi->payment_method           = 'tunai';
            $reservasi->amount_paid              = $uangDiterima;    // simpan ke kolom yang sesuai
            $reservasi->change_given             = $changeGiven;
            $reservasi->sisa_tagihan_reservasi   = 0;
            $reservasi->waktu_selesai            = now();
            $reservasi->status                   = 'selesai';
            $reservasi->save();
        }
        // ──────────────────────────────────

        // ─────── Pembayaran QRIS ───────
        elseif ($request->payment_method === 'qris') {
            Config::$serverKey    = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            $item_details = [];
            foreach ($reservasi->orders as $order) {
                if ($order->menu) {
                    $item_details[] = [
                        'id'       => $order->menu->id,
                        'price'    => (int) $order->price_at_order,
                        'quantity' => (int) $order->quantity,
                        'name'     => $order->menu->name,
                    ];
                }
            }
            if ($reservasi->service_charge > 0) {
                $item_details[] = [
                    'id'       => 'service_charge',
                    'price'    => (int) $reservasi->service_charge,
                    'quantity' => 1,
                    'name'     => 'Biaya Layanan',
                ];
            }
            if ($reservasi->tax > 0) {
                $item_details[] = [
                    'id'       => 'tax',
                    'price'    => (int) $reservasi->tax,
                    'quantity' => 1,
                    'name'     => 'Pajak (PPN)',
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
                    'finish' => route('pelayan.order.summary', $reservasi->id),
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Midtrans Snap Token generation failed: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()
                ];
            }

            $reservasi->payment_method = 'qris';
            $reservasi->status         = 'selesai';
            $reservasi->save();
        }
        // ──────────────────────────────────

        DB::commit();

        $response = [
            'success'      => true,
            'change'       => $changeGiven,
            // Hanya redirect otomatis jika “tunai”
            'redirect_url' => ($request->payment_method === 'tunai')
                                ? route('pelayan.order.summary', $reservasi->id)
                                : null,
        ];

        if ($request->payment_method === 'qris' && $snapToken) {
            $response['snap_token'] = $snapToken;
        }

        return $response;
    }
    catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error processing payment: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
        ];
    }
}


    public function bayarSisa($id)
    {
        $reservasi = Reservasi::with('orders')->findOrFail($id);

        if ($reservasi->status === 'paid') {
            return [
                'success' => false,
                'redirect' => route('pelayan.reservasi'),
                'message' => 'Reservasi sudah dibayar lunas.',
            ];
        }

        $totalTagihan = $reservasi->orders->sum('total_price');
        $totalDibayar = $totalTagihan - ($reservasi->sisa_tagihan_reservasi ?? $totalTagihan);
        $sisa = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

        return [
            'success' => true,
            'reservasi' => $reservasi,
            'totalTagihan' => $totalTagihan,
            'totalDibayar' => $totalDibayar,
            'sisa' => $sisa,
        ];
    }

   public function bayarSisaPost(Request $request, $id)
{
    $reservasi = Reservasi::with('orders', 'pengguna')->findOrFail($id);

    $request->validate([
        'jumlah_dibayar' => 'required|numeric|min:1',
        'metode'         => 'required|string|in:tunai,qris',
    ]);

    $totalTagihan = $reservasi->orders->sum('total_price');
    $sisa         = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

    if ($request->jumlah_dibayar > $sisa) {
        return ['success' => false, 'message' => 'Jumlah dibayar melebihi sisa tagihan.'];
    }

    // ─────── Pembayaran Tunai ───────
    if ($request->metode === 'tunai') {
        try {
            DB::beginTransaction();

            $reservasi->sisa_tagihan_reservasi = $sisa - $request->jumlah_dibayar;
            $reservasi->payment_method        = 'tunai';

            if ($reservasi->sisa_tagihan_reservasi <= 0) {
                $reservasi->status                 = 'paid';
                $reservasi->sisa_tagihan_reservasi = 0;
            }

            $reservasi->save();
            DB::commit();

            return ['success' => true, 'message' => 'Pembayaran sisa berhasil.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Gagal menyimpan pembayaran: ' . $e->getMessage()];
        }
    }
    // ──────────────────────────────────


    // ─────── Pembayaran QRIS / VA lewat Snap ───────
    if ($request->metode === 'qris') {
        DB::beginTransaction();

        // Hitung nominal yang ingin dibayar
        $grossAmount = (int) round($request->jumlah_dibayar);

        // Siapkan item_details (optional, Anda bisa kirimkan detail menu juga jika mau)
        $item_details = [[
            'id'       => 'partial-payment-'. $reservasi->kode_reservasi,
            'price'    => $grossAmount,
            'quantity' => 1,
            'name'     => 'Pembayaran Sebagian Reservasi #' . $reservasi->kode_reservasi
        ]];

        // Siapkan customer_details jika perlu
        $customer_details = [
            'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
            'email'      => $reservasi->pengguna->email ?? 'customer@restaurant.com',
            'phone'      => $reservasi->pengguna->phone ?? '081234567890',
        ];

        // Transaksi Snap: 
        // - Tanpa kita tentukan payment_type, Snap UI otomatis menampilkan QRIS, VA, E-Wallet, dll.
        $transaction_details = [
            'order_id'     => $reservasi->kode_reservasi . '-PART-' . time(),
            'gross_amount' => $grossAmount,
        ];

        $params = [
            'transaction_details' => $transaction_details,
            'item_details'        => $item_details,
            'customer_details'    => $customer_details,
            // Jika Anda ingin mem‐filter supaya hanya muncul QRIS dan VA:
            // 'enable_payments' => ['qris','bca_va','bni_va'],
            // atau biarkan kosong agar semua metode Snap tersedia.
            'callbacks' => [
                'finish' => route('pelayan.reservasi.bayarSisa.callback', $reservasi->id),
            ],
        ];

        try {
            // Generate Snap token
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Snap Token failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()
            ];
        }

        // Set payment_method jadi 'qris' (atau biarkan untuk ditentukan lagi via callback)
        $reservasi->payment_method       = 'qris'; 
        $reservasi->payment_token        = $snapToken;       // simpan Snap token agar nanti bisa render Snap UI
        $reservasi->payment_amount       = $grossAmount;     // jumlah yang dibayar lewat Snap
        $reservasi->payment_status       = 'pending';        // belum settled
        $reservasi->sisa_tagihan_reservasi = $sisa - $grossAmount;
        if ($reservasi->sisa_tagihan_reservasi < 0) {
            $reservasi->sisa_tagihan_reservasi = 0;
        }
        $reservasi->save();

        DB::commit();

        return [
            'success'    => true,
            'snap_token' => $snapToken,
            'redirect'   => route('pelayan.reservasi.bayarSisa.qris', $reservasi->id),
        ];
    }

    return ['success' => false, 'message' => 'Metode pembayaran tidak dikenali.'];
}


        public function showQrisPayment($id)
    {
        $reservasi = Reservasi::findOrFail($id);

        return view('pelayan.qris-payment', [
            'snapToken'      => $reservasi->payment_token,
            'reservasi'      => $reservasi,
            'jumlah_dibayar' => $reservasi->payment_amount,
        ]);
    }


    public function handleQrisCallback(Request $request)
{
    // 1. Ambil payload
    $serverKey = config('services.midtrans.server_key');

    $orderId = $request->order_id;
    $statusCode = $request->status_code;
    $grossAmount = $request->gross_amount;
    $signatureKey = $request->signature_key;

    // 2. Verifikasi Signature
    $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    if ($expectedSignature !== $signatureKey) {
        return response()->json(['status' => 'invalid_signature'], 403);
    }

    // 3. Cari reservasi berdasarkan order_id (kode_reservasi)
    $reservasi = Reservasi::where('kode_reservasi', $orderId)->first();

    if (!$reservasi) {
        return response()->json(['status' => 'reservasi_not_found'], 404);
    }

    // 4. Update status jika settlement
    if ($request->transaction_status === 'settlement') {
        try {
            DB::beginTransaction();

            $reservasi->sisa_tagihan_reservasi -= $reservasi->payment_amount;

            if ($reservasi->sisa_tagihan_reservasi <= 0) {
                $reservasi->sisa_tagihan_reservasi = 0;
                $reservasi->status = 'paid';
            }

            $reservasi->payment_status = 'paid';
            $reservasi->save();

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    return response()->json(['status' => $request->transaction_status]);
}

}
