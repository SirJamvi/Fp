<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Helpers\MidtransHelper;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Reservasi;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use App\Models\CustomerNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Midtrans\Snap;

class MidtransController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Menangani proses checkout dari keranjang belanja di aplikasi mobile.
     * TANPA FINISH URL - Mengandalkan callback onSuccess di frontend dan webhook
     */
    public function checkoutFromCart(Request $request)
    {
        // 1. Validasi input dari Ionic
        $validator = Validator::make($request->all(), [
            'reservasi_id' => 'required|exists:reservasi,id',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:menus,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data keranjang tidak valid.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $reservasi = Reservasi::findOrFail($request->input('reservasi_id'));

            if ($reservasi->user_id !== $user->id) {
                return response()->json(['message' => 'Reservasi tidak ditemukan.'], 403);
            }

            // Hapus pesanan lama di reservasi ini
            $reservasi->orders()->delete();

            $subtotal = 0;
            $item_details_midtrans = [];

            // 2. Kalkulasi dan buat order baru
            foreach ($request->input('cart') as $item) {
                $menu = Menu::find($item['id']);
                $price = $menu->final_price ?? $menu->price;
                $totalItemPrice = $price * $item['quantity'];
                $subtotal += $totalItemPrice;

                // Buat record Order
                Order::create([
                    'reservasi_id' => $reservasi->id,
                    'menu_id'      => $menu->id,
                    'user_id'      => $user->id,
                    'quantity'     => $item['quantity'],
                    'price_at_order' => $price,
                    'total_price'  => $totalItemPrice,
                    'notes'        => $item['note'] ?? null,
                    'status'       => 'pending',
                ]);

                // Item DP
                $dpPrice = $price * 0.5;
                $item_details_midtrans[] = [
                    'id'       => (string) $menu->id,
                    'price'    => (int) $dpPrice,
                    'quantity' => (int) $item['quantity'],
                    'name'     => substr($menu->name . ' (DP 50%)', 0, 50),
                ];
            }

            // Pajak 10%
            $taxAmount = $subtotal * 0.10;
            $dpTaxAmount = $taxAmount * 0.5;

            // Tambahkan item pajak sebagai item terpisah
            $item_details_midtrans[] = [
                'id' => 'tax-10',
                'price' => (int) $dpTaxAmount,
                'quantity' => 1,
                'name' => 'Pajak 10% (DP)'
            ];

            // Total termasuk pajak
            $totalAmount = $subtotal + $taxAmount;
            $paymentAmountDP = $totalAmount * 0.5;

            // VALIDASI: Pastikan total item_details sama dengan gross_amount
            $calculatedTotal = 0;
            foreach ($item_details_midtrans as $item) {
                $calculatedTotal += $item['price'] * $item['quantity'];
            }

            if ($calculatedTotal != $paymentAmountDP) {
                Log::error('Mismatch between calculated total and payment amount', [
                    'calculated_total' => $calculatedTotal,
                    'payment_amount_dp' => $paymentAmountDP,
                    'item_details' => $item_details_midtrans
                ]);
                
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error dalam kalkulasi pembayaran.',
                    'debug' => [
                        'calculated_total' => $calculatedTotal,
                        'expected_amount' => $paymentAmountDP
                    ]
                ], 500);
            }

            // Update reservasi
            $reservasi->update([
                'total_bill' => $totalAmount,
                'sisa_tagihan_reservasi' => $totalAmount,
                'status' => 'pending_payment',
            ]);

            // 3. Konfigurasi Midtrans TANPA finish_url
            MidtransHelper::configure();

            $orderId = 'RES-' . $reservasi->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $paymentAmountDP, // ← Sekarang akan sama dengan total item_details
                ],
                'customer_details' => [
                    'first_name' => $user->nama,
                    'email' => $user->email,
                    'phone' => $user->nomor_hp,
                ],
                'item_details' => $item_details_midtrans, // ← Item dengan harga DP
                'custom_field1' => $reservasi->id,
               
                // PENTING: Hapus finish_url, unfinish_url, error_url
                // Hanya andalkan callback onSuccess/onError di frontend dan webhook
            ];

            // Log untuk debugging
            Log::info('Midtrans params prepared', [
                'order_id' => $orderId,
                'gross_amount' => $paymentAmountDP,
                'item_details_count' => count($item_details_midtrans),
                'calculated_total' => $calculatedTotal,
                'item_details' => $item_details_midtrans
            ]);

            // PERBAIKAN: Sesuaikan dengan struktur tabel customer_notifications yang ada
            CustomerNotification::create([
                'user_id'      => $user->id,
                'reservasi_id' => $reservasi->id,
                'type'         => 'reservation_created',
                'title'        => 'Menunggu Pembayaran',
                'message'      => "Pesanan Anda dengan kode #{$reservasi->id} telah dibuat. Segera selesaikan pembayaran DP 50%.",
                'data'         => [
                    'order_id'     => $orderId,
                    'total_amount' => $totalAmount,
                    'dp_amount'    => $paymentAmountDP
                ]
            ]);

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            Log::info('Checkout processed successfully', [
                'reservasi_id' => $reservasi->id,
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'payment_amount' => $paymentAmountDP,
                'midtrans_gross_amount' => $paymentAmountDP,
                'item_details_total' => $calculatedTotal
            ]);

            // 4. Return response ke frontend
            return response()->json([
            'success' => true,
            'snap_token' => $snapToken,
            'order_id' => $orderId,
            'reservasi_id' => $reservasi->id,
            'total_amount' => $totalAmount,
            'tax' => $taxAmount,
            'payment_amount' => $paymentAmountDP,
            'message' => 'Checkout berhasil. Silakan lanjutkan pembayaran DP 50% termasuk pajak.'
        ]);


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage() . ' on line ' . $e->getLine(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses checkout.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Menangani notifikasi webhook dari Midtrans
     * Method ini tetap penting untuk memastikan status pembayaran ter-update
     */
    public function handleNotification(Request $request)
    {
        MidtransHelper::configure();
        $payload = $request->all();
       
        Log::info('Midtrans notification received', ['payload' => $payload]);

        $order_id = $payload['order_id'];
        $status_code = $payload['status_code'];
        $gross_amount = $payload['gross_amount'];
        $serverKey = config('services.midtrans.server_key');

        // Verify signature
        $signature = hash('sha512', $order_id . $status_code . $gross_amount . $serverKey);
        if ($payload['signature_key'] !== $signature) {
            Log::warning('Invalid signature from Midtrans', ['order_id' => $order_id]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Get reservasi ID
        $reservasi_id = $payload['custom_field1'] ?? null;
        if (!$reservasi_id) {
            $orderIdParts = explode('-', $order_id);
            if(count($orderIdParts) > 1 && $orderIdParts[0] === 'RES') {
                $reservasi_id = $orderIdParts[1];
            }
        }
       
        $reservasi = Reservasi::find($reservasi_id);
        if (!$reservasi) {
            Log::error('Reservasi not found for notification', ['reservasi_id' => $reservasi_id, 'order_id' => $order_id]);
            return response()->json(['message' => 'Reservasi tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            // Save/update payment record
            $payment = Payment::updateOrCreate(
                ['order_id' => $order_id],
                [
                    'reservasi_id' => $reservasi->id,
                    'amount' => $gross_amount,
                    'payment_type' => $payload['payment_type'],
                    'status' => $payload['transaction_status'],
                    'deposit' => true, // ← Menandai ini adalah pembayaran DP
                    'midtrans_response' => json_encode($payload)
                ]
            );

            // Handle different payment statuses
            if ($payload['transaction_status'] === 'settlement') {
                // Payment successful - DP sudah dibayar
                $totalPaid = Payment::where('reservasi_id', $reservasi->id)
                                  ->where('status', 'settlement')
                                  ->sum('amount');

                $reservasi->update([
                    'amount_paid' => $totalPaid,
                    'sisa_tagihan_reservasi' => $reservasi->total_bill - $totalPaid,
                    'payment_method' => $payload['payment_type'],
                    'status' => 'confirmed' // ← Status confirmed karena DP sudah dibayar
                ]);

                // Update orders status
                Order::where('reservasi_id', $reservasi->id)->update(['status' => 'confirmed']);

                CustomerNotification::create([
                    'user_id'      => $reservasi->user_id,
                    'reservasi_id' => $reservasi->id,
                    'type'         => 'payment_success',
                    'title'        => 'Pembayaran DP Berhasil!',
                    'message'      => "Pembayaran DP 50% untuk pesanan #{$reservasi->id} telah kami terima. Sisa pembayaran akan dilakukan saat kedatangan.",
                    'data'         => [
                        'order_id'     => $order_id,
                        'amount_paid'  => $gross_amount,
                        'remaining_amount' => $reservasi->total_bill - $totalPaid
                    ]
                ]);

                // Generate invoice setelah pembayaran berhasil
                try {
                    $invoiceResult = $this->invoiceService->generateInvoice($reservasi->id);
                    Log::info('Invoice generated after DP payment', [
                        'reservasi_id' => $reservasi->id,
                        'invoice_success' => $invoiceResult['success'] ?? false
                    ]);
                } catch (\Exception $invoiceError) {
                    Log::error('Failed to generate invoice after DP payment', [
                        'reservasi_id' => $reservasi->id,
                        'error' => $invoiceError->getMessage()
                    ]);
                }

                Log::info('DP Payment settlement processed', [
                    'order_id' => $order_id,
                    'reservasi_id' => $reservasi->id,
                    'dp_amount' => $gross_amount,
                    'total_paid' => $totalPaid,
                    'remaining_amount' => $reservasi->total_bill - $totalPaid
                ]);

            } elseif ($payload['transaction_status'] === 'pending') {
                // Payment pending - tidak perlu update status reservasi
                Log::info('DP Payment pending', ['order_id' => $order_id, 'reservasi_id' => $reservasi->id]);

            } elseif (in_array($payload['transaction_status'], ['cancel', 'deny', 'expire', 'failure'])) {
                // Payment failed
                $reservasi->update([
                    'status' => 'cancelled',
                    'cancelled_reason' => 'DP Payment failed: ' . $payload['transaction_status']
                ]);

                // Update orders status
                Order::where('reservasi_id', $reservasi->id)->update(['status' => 'cancelled']);

                CustomerNotification::create([
                    'user_id'      => $reservasi->user_id,
                    'reservasi_id' => $reservasi->id,
                    'type'         => 'payment_failed',
                    'title'        => 'Pembayaran DP Gagal',
                    'message'      => "Pembayaran DP untuk pesanan #{$reservasi->id} gagal. Pesanan dibatalkan. Status: {$payload['transaction_status']}",
                    'data'         => [
                        'order_id'     => $order_id,
                        'status'       => $payload['transaction_status']
                    ]
                ]);
               
                Log::warning('DP Payment failed, reservation cancelled', [
                    'order_id' => $order_id,
                    'reservasi_id' => $reservasi->id,
                    'status' => $payload['transaction_status']
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Notification handled successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle payment notification', [
                'order_id' => $order_id,
                'reservasi_id' => $reservasi_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);//
           
            return response()->json(['message' => 'Failed to process notification'], 500);
        }
    }
}