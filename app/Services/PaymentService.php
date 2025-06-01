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

    public function processPayment($request, $reservasi_id)
    {
        DB::beginTransaction();

        try {
            $reservasi = Reservasi::with('meja', 'orders.menu')->findOrFail($reservasi_id);

            if ($reservasi->status === 'paid') {
                DB::rollBack();
                return ['success' => false, 'message' => 'Pesanan ini sudah lunas.'];
            }

            $totalBill = $reservasi->total_bill;
            $changeGiven = 0;
            $snapToken = null;

            if ($request->payment_method === 'cash') {
                if (is_null($request->amount_paid) || $request->amount_paid < $totalBill) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Jumlah uang tunai yang dibayarkan kurang dari total tagihan.'];
                }

                $amountPaid = $request->amount_paid;
                $changeGiven = $amountPaid - $totalBill;

                $reservasi->payment_status = 'paid';
                $reservasi->payment_method = $request->payment_method;
                $reservasi->amount_paid = $request->amount_paid;
                $reservasi->change_given = $changeGiven;
                $reservasi->sisa_tagihan_reservasi = 0;
                $reservasi->waktu_selesai = now();
                $reservasi->status = 'selesai';
                $reservasi->save();
            } elseif ($request->payment_method === 'qris') {
                Config::$serverKey = config('services.midtrans.server_key');
                Config::$isProduction = config('services.midtrans.is_production', false);
                Config::$isSanitized = true;
                Config::$is3ds = true;

                $item_details = [];
                foreach ($reservasi->orders as $order) {
                    if ($order->menu) {
                        $item_details[] = [
                            'id' => $order->menu->id,
                            'price' => (int) $order->price_at_order,
                            'quantity' => (int) $order->quantity,
                            'name' => $order->menu->name,
                        ];
                    }
                }

                if ($reservasi->service_charge > 0) {
                    $item_details[] = [
                        'id' => 'service_charge',
                        'price' => (int) $reservasi->service_charge,
                        'quantity' => 1,
                        'name' => 'Biaya Layanan',
                    ];
                }
                if ($reservasi->tax > 0) {
                    $item_details[] = [
                        'id' => 'tax',
                        'price' => (int) $reservasi->tax,
                        'quantity' => 1,
                        'name' => 'Pajak (PPN)',
                    ];
                }

                $transaction_details = [
                    'order_id' => $reservasi->kode_reservasi . '-' . time(),
                    'gross_amount' => (int) $totalBill,
                ];

                $params = [
                    'transaction_details' => $transaction_details,
                    'item_details' => $item_details,
                    'customer_details' => [
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
                    return ['success' => false, 'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()];
                }

                $reservasi->payment_method = 'qris';
                $reservasi->status = 'pending_payment';
                $reservasi->save();
            }

            DB::commit();

            $response = [
                'success' => true,
                'change' => $changeGiven,
                'redirect_url' => ($request->payment_method === 'cash') ? route('pelayan.order.summary', $reservasi->id) : null,
            ];

            if ($request->payment_method === 'qris' && $snapToken) {
                $response['snap_token'] = $snapToken;
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()];
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
            'metode' => 'required|string|in:tunai,qris',
        ]);

        $totalTagihan = $reservasi->orders->sum('total_price');
        $sisa = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

        if ($request->jumlah_dibayar > $sisa) {
            return ['success' => false, 'message' => 'Jumlah dibayar melebihi sisa tagihan.'];
        }

        if ($request->metode === 'tunai') {
            try {
                DB::beginTransaction();

                $reservasi->sisa_tagihan_reservasi = $sisa - $request->jumlah_dibayar;
                $reservasi->payment_method = 'tunai';

                if ($reservasi->sisa_tagihan_reservasi <= 0) {
                    $reservasi->status = 'paid';
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

        if ($request->metode === 'qris') {
            try {
                DB::beginTransaction();

                $grossAmount = (int) round($request->jumlah_dibayar);

                $transaction_details = [
                    'order_id' => $reservasi->kode_reservasi . '-PART-' . time(),
                    'gross_amount' => $grossAmount,
                ];

                $item_details = [[
                    'id' => 'partial-payment',
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Pembayaran Sebagian Reservasi #' . $reservasi->kode_reservasi
                ]];

                $customer_details = [
                    'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
                    'email' => $reservasi->pengguna->email ?? 'customer@restaurant.com',
                    'phone' => $reservasi->pengguna->phone ?? '081234567890',
                ];

                $params = [
                    'transaction_details' => $transaction_details,
                    'item_details' => $item_details,
                    'customer_details' => $customer_details,
                    'callbacks' => [
                        'finish' => route('pelayan.reservasi.bayarSisa.callback', $reservasi->id),
                    ],
                    'expiry' => [
                        'unit' => 'hour',
                        'duration' => 24
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);

                $reservasi->payment_method = 'qris';
                $reservasi->payment_token = $snapToken;
                $reservasi->payment_amount = $grossAmount;
                $reservasi->payment_status = 'pending';
                $reservasi->save();

                DB::commit();

                return [
                    'success' => true,
                    'snap_token' => $snapToken,
                    'redirect' => route('pelayan.reservasi.bayarSisa.qris', $reservasi->id),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Midtrans QRIS payment failed: ' . $e->getMessage());
                return ['success' => false, 'message' => 'Gagal memproses pembayaran QRIS: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'Metode pembayaran tidak dikenali.'];
    }

    public function showQrisPayment($id)
    {
        $reservasi = Reservasi::findOrFail($id);

        return [
            'snapToken' => $reservasi->payment_token,
            'reservasi' => $reservasi,
            'jumlah_dibayar' => $reservasi->payment_amount,
        ];
    }

    public function handleQrisCallback(Request $request, $id)
    {
        $reservasi = Reservasi::findOrFail($id);

        // Signature Key Verification
        $serverKey = config('services.midtrans.server_key');
        $calculatedSignature = hash('sha512',
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($calculatedSignature !== $request->signature_key) {
            return response()->json(['status' => 'invalid_signature'], 403);
        }

        if ($request->transaction_status === 'settlement') {
            try {
                DB::beginTransaction();

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
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => $request->transaction_status]);
    }
}