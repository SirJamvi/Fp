<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\MidtransHelper;
use Illuminate\Http\Request;

class PaymentService
{
    /**
     * Proses pembayaran (tunai atau QRIS) untuk reservasi.
     */
    public function process(Request $request, $reservasi_id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'amount_paid'    => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::with('meja', 'orders.menu')->findOrFail($reservasi_id);

            if ($reservasi->status === 'paid') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ini sudah lunas.'
                ], 400);
            }

            $totalBill = $reservasi->total_bill;
            $changeGiven = 0;
            $snapToken = null;

            if ($request->payment_method === 'cash') {
                // Validasi pembayaran tunai
                if (is_null($request->amount_paid) || $request->amount_paid < $totalBill) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Jumlah tunai kurang dari total tagihan.'
                    ], 422);
                }

                $amountPaid = $request->amount_paid;
                $changeGiven = $amountPaid - $totalBill;

                // Update reservation
                $reservasi->payment_method = 'cash';
                $reservasi->amount_paid   = $amountPaid;
                $reservasi->change_given  = $changeGiven;
                $reservasi->status        = 'paid';
                $reservasi->waktu_selesai = now();
                $reservasi->save();

                // Catatan: jangan ubah status order ke 'served' di sini
            }
            else if ($request->payment_method === 'qris') {
                // --- QRIS via Midtrans ---
                MidtransHelper::configure();

                // Siapkan item_details untuk Midtrans
                $itemDetails = [];
                foreach ($reservasi->orders as $order) {
                    if ($order->menu) {
                        $itemDetails[] = [
                            'id'       => $order->menu->id,
                            'price'    => (int)$order->price_at_order,
                            'quantity' => (int)$order->quantity,
                            'name'     => $order->menu->name,
                        ];
                    }
                }
                if ($reservasi->service_charge > 0) {
                    $itemDetails[] = [
                        'id'       => 'service_charge',
                        'price'    => (int)$reservasi->service_charge,
                        'quantity' => 1,
                        'name'     => 'Biaya Layanan'
                    ];
                }
                if ($reservasi->tax > 0) {
                    $itemDetails[] = [
                        'id'       => 'tax',
                        'price'    => (int)$reservasi->tax,
                        'quantity' => 1,
                        'name'     => 'Pajak (PPN)'
                    ];
                }

                // Customer details (opsional)
                $customerDetails = [
                    'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
                    // bisa tambah last_name, email, phone jika ada
                ];

                // Transaction details
                $transactionDetails = [
                    'order_id'     => $reservasi->kode_reservasi . '-' . time(),
                    'gross_amount' => (int)$totalBill,
                ];

                $params = [
                    'transaction_details' => $transactionDetails,
                    'item_details'        => $itemDetails,
                    'customer_details'    => $customerDetails,
                    'callbacks'           => [
                        'finish' => route('pelayan.order.summary', $reservasi->id),
                    ],
                ];

                try {
                    $snapToken = MidtransHelper::getSnapToken($params);
                    Log::info("Snap Token untuk Reservasi {$reservasi->id}: " . $snapToken);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Gagal generate Snap Token: " . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()
                    ], 500);
                }

                // Simpan status reservasi menjadi 'pending_payment'
                $reservasi->payment_method = 'qris';
                $reservasi->status = 'pending_payment';
                $reservasi->save();
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Pesanan berhasil diproses. Lanjutkan ke pembayaran.',
                'change'  => $changeGiven,
                'redirect_url' => ($request->payment_method === 'cash')
                    ? route('pelayan.order.summary', $reservasi->id)
                    : null,
            ];
            if ($request->payment_method === 'qris' && $snapToken) {
                $response['snap_token'] = $snapToken;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing payment: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan form bayar sisa (partial payment).
     */
    public function showPartialPayment($id)
    {
        $reservasi = Reservasi::with('orders')->findOrFail($id);
        if ($reservasi->status === 'paid') {
            return redirect()->route('pelayan.reservasi')
                ->with('info', 'Reservasi sudah lunas.');
        }

        $totalTagihan = $reservasi->orders->sum('total_price');
        $totalDibayar = $totalTagihan - ($reservasi->sisa_tagihan_reservasi ?? $totalTagihan);
        $sisa         = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

        return view('pelayan.bayar-sisa', compact('reservasi', 'totalTagihan', 'totalDibayar', 'sisa'));
    }

    /**
     * Proses bayar sisa (partial payment).
     */
    public function handlePartialPayment(Request $request, $id)
    {
        $reservasi = Reservasi::with('orders')->findOrFail($id);

        $request->validate([
            'jumlah_dibayar' => 'required|numeric|min:1',
            'metode'         => 'required|string|in:tunai,qris',
        ]);

        $totalTagihan = $reservasi->orders->sum('total_price');
        $sisa         = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

        if ($request->jumlah_dibayar > $sisa) {
            return back()->with('error', 'Jumlah dibayar melebihi sisa tagihan.');
        }

        if ($request->metode === 'tunai') {
            DB::beginTransaction();
            try {
                $reservasi->sisa_tagihan_reservasi = $sisa - $request->jumlah_dibayar;
                $reservasi->payment_method         = $request->metode;

                if ($reservasi->sisa_tagihan_reservasi <= 0) {
                    $reservasi->status = 'paid';
                    $reservasi->sisa_tagihan_reservasi = 0;
                }

                $reservasi->save();
                DB::commit();

                return redirect()->route('pelayan.reservasi')
                    ->with('success', 'Pembayaran sisa berhasil disimpan.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
            }
        }
        // Jika metode QRIS
        elseif ($request->metode === 'qris') {
            DB::beginTransaction();
            try {
                $grossAmount = (int) round($request->jumlah_dibayar);
                if ($grossAmount < 1) {
                    throw new \Exception('Minimal pembayaran QRIS adalah Rp 1');
                }

                MidtransHelper::configure();

                $transactionDetails = [
                    'order_id'     => $reservasi->kode_reservasi . '-PART-' . time(),
                    'gross_amount' => $grossAmount
                ];
                $itemDetails = [
                    [
                        'id'       => 'partial-payment',
                        'price'    => $grossAmount,
                        'quantity' => 1,
                        'name'     => 'Pembayaran Sebagian Reservasi #' . $reservasi->kode_reservasi
                    ]
                ];
                $customerDetails = [
                    'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
                    'email'      => $reservasi->pengguna->email ?? 'customer@restaurant.com',
                    'phone'      => $reservasi->pengguna->phone ?? '081234567890'
                ];
                $params = [
                    'transaction_details' => $transactionDetails,
                    'item_details'        => $itemDetails,
                    'customer_details'    => $customerDetails,
                    'payment_type'        => 'qris',
                    'callbacks'           => [
                        'finish' => route('pelayan.reservasi.bayarSisa.callback', $reservasi->id),
                    ],
                    'expiry' => [
                        'unit'     => 'hour',
                        'duration' => 24
                    ]
                ];

                $snapToken = MidtransHelper::getSnapToken($params);

                $reservasi->payment_method   = 'qris';
                $reservasi->payment_token    = $snapToken;
                $reservasi->payment_amount   = $grossAmount;
                $reservasi->payment_status   = 'pending';
                $reservasi->save();

                DB::commit();

                return redirect()->route('pelayan.reservasi.bayarSisa.qris', $reservasi->id);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Midtrans QRIS partial payment failed: " . $e->getMessage());
                return back()->with('error', 'Gagal memproses pembayaran QRIS: ' . $e->getMessage());
            }
        }
    }

    /**
     * Tampilkan halaman pembayaran QRIS untuk partial payment.
     */
    public function showQrisPayment($id)
    {
        $reservasi = Reservasi::findOrFail($id);
        return view('pelayan.qris-payment', [
            'snapToken'    => $reservasi->payment_token,
            'reservasi'    => $reservasi,
            'jumlah_dibayar'=> $reservasi->payment_amount
        ]);
    }

    /**
     * Handle callback Midtrans untuk partial payment.
     */
    public function handleQrisCallback(Request $request, $id)
    {
        $reservasi = Reservasi::findOrFail($id);

        // TODO: verifikasi signature Midtrans sesuai dokumentasi
        if ($request->transaction_status === 'settlement') {
            DB::beginTransaction();
            try {
                $reservasi->sisa_tagihan_reservasi -= $reservasi->payment_amount;
                if ($reservasi->sisa_tagihan_reservasi <= 0) {
                    $reservasi->status = 'paid';
                    $reservasi->sisa_tagihan_reservasi = 0;
                }
                $reservasi->payment_status = 'paid';
                $reservasi->save();
                DB::commit();
                return response()->json(['status' => 'success']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['status' => $request->transaction_status]);
    }
}
