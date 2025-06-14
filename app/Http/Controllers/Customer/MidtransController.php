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

                // PERBAIKAN: Siapkan detail item untuk Midtrans dengan harga DP 50%
                $dpPrice = $price * 0.5; // Harga DP 50%
                $item_details_midtrans[] = [
                    'id'       => (string) $menu->id,
                    'price'    => (int) $dpPrice, // ← PERBAIKAN: Gunakan harga DP, bukan harga penuh
                    'quantity' => (int) $item['quantity'],
                    'name'     => substr($menu->name . ' (DP 50%)', 0, 50), // ← PERBAIKAN: Tambah keterangan DP
                ];
            }

            $totalAmount = $subtotal;
            $paymentAmountDP = $totalAmount * 0.5;

            // >>>>>>>>>>>>>> START OF NEW CODE <<<<<<<<<<<<<<
            $serviceFeeAmount = round($paymentAmountDP * 0.10); // Hitung 10% biaya layanan dari DP 50%
            $totalAmountToPay = $paymentAmountDP + $serviceFeeAmount;

            // Tambahkan biaya layanan sebagai item terpisah di Midtrans
            $item_details_midtrans[] = [
                'id'       => 'SERVICE_FEE', // ID unik untuk biaya layanan
                'price'    => (int) $serviceFeeAmount,
                'quantity' => 1,
                'name'     => 'Biaya Layanan (10%)',
            ];
            // >>>>>>>>>>>>>> END OF NEW CODE <<<<<<<<<<<<<<

            // VALIDASI: Pastikan total item_details sama dengan gross_amount yang baru
            $calculatedTotalItems = 0;
            foreach ($item_details_midtrans as $item) {
                $calculatedTotalItems += $item['price'] * $item['quantity'];
            }

            // >>>>>>>>>>>>>> START OF MODIFIED VALIDATION <<<<<<<<<<<<<<
            if ($calculatedTotalItems != $totalAmountToPay) { // Validasi terhadap totalAmountToPay yang baru
            // >>>>>>>>>>>>>> END OF MODIFIED VALIDATION <<<<<<<<<<<<<<
                Log::error('Mismatch between calculated total and payment amount for Midtrans', [
                    'calculated_total_items' => $calculatedTotalItems,
                    'expected_amount_to_pay' => $totalAmountToPay, // Menggunakan variabel baru
                    'item_details' => $item_details_midtrans
                ]);
                
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error dalam kalkulasi pembayaran Midtrans.',
                    'debug' => [
                        'calculated_total_items' => $calculatedTotalItems,
                        'expected_amount' => $totalAmountToPay // Menggunakan variabel baru
                    ]
                ], 500);
            }

            // Update reservasi (total_bill dan sisa_tagihan_reservasi harus tetap mencerminkan TOTAL tagihan penuh)
            $reservasi->update([
                'total_bill' => $totalAmount, // Total tagihan penuh (subtotal)
                'sisa_tagihan_reservasi' => $totalAmount, // Ini akan dikurangi saat pembayaran Midtrans berhasil
                'status' => 'pending_payment',
            ]);

            // 3. Konfigurasi Midtrans TANPA finish_url
            MidtransHelper::configure();

            $orderId = 'RES-' . $reservasi->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $totalAmountToPay, // <<<<<<<<<<<<<< PENTING: Gunakan totalAmountToPay
                ],
                'customer_details' => [
                    'first_name' => $user->nama,
                    'email' => $user->email,
                    'phone' => $user->nomor_hp,
                ],
                'item_details' => $item_details_midtrans, // Item dengan harga DP + Biaya Layanan
                'custom_field1' => $reservasi->id,
                
                // PENTING: Hapus finish_url, unfinish_url, error_url
                // Hanya andalkan callback onSuccess/onError di frontend dan webhook
            ];

            // Log untuk debugging
            Log::info('Midtrans params prepared', [
                'order_id' => $orderId,
                'gross_amount' => $totalAmountToPay, // Log nilai yang benar
                'payment_amount_dp_original' => $paymentAmountDP, // Log DP original
                'service_fee_amount' => $serviceFeeAmount, // Log biaya layanan
                'item_details_count' => count($item_details_midtrans),
                'calculated_total_items' => $calculatedTotalItems, // Log total item_details
                'item_details' => $item_details_midtrans
            ]);

            // PERBAIKAN: Sesuaikan dengan struktur tabel customer_notifications yang ada
            CustomerNotification::create([
                'user_id'      => $user->id,
                'reservasi_id' => $reservasi->id,
                'type'         => 'reservation_created',
                'title'        => 'Menunggu Pembayaran',
                'message'      => "Pesanan Anda dengan kode #{$reservasi->id} telah dibuat. Segera selesaikan pembayaran DP 50% ditambah biaya layanan.",
                'data'         => [
                    'order_id'     => $orderId,
                    'total_amount' => $totalAmount, // Ini total bill penuh
                    'dp_amount'    => $paymentAmountDP, // Ini DP 50% saja
                    'service_fee'  => $serviceFeeAmount, // Ini biaya layanan
                    'amount_to_pay_midtrans' => $totalAmountToPay // Ini yang dibayarkan ke Midtrans
                ]
            ]);

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            Log::info('Checkout processed successfully', [
                'reservasi_id' => $reservasi->id,
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'payment_amount_dp' => $paymentAmountDP,
                'service_fee' => $serviceFeeAmount,
                'midtrans_gross_amount' => $totalAmountToPay,
                'item_details_total' => $calculatedTotalItems
            ]);

            // 4. Return response ke frontend
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'reservasi_id' => $reservasi->id,
                'total_amount' => $totalAmount,
                'payment_amount' => $paymentAmountDP,
                'message' => 'Checkout berhasil. Silakan lanjutkan pembayaran DP 50%.'
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
        $gross_amount = $payload['gross_amount']; // Ini adalah jumlah total yang dibayar via Midtrans (termasuk biaya layanan)
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
            // PERHATIAN: amount di sini adalah GROSS_AMOUNT dari Midtrans, yang sudah termasuk biaya layanan
            $payment = Payment::updateOrCreate(
                ['order_id' => $order_id],
                [
                    'reservasi_id' => $reservasi->id,
                    'amount' => $gross_amount, // <-- Ini adalah jumlah yang dibayar ke Midtrans (termasuk biaya layanan)
                    'payment_type' => $payload['payment_type'],
                    'status' => $payload['transaction_status'],
                    'deposit' => true, // Menandai ini adalah pembayaran DP
                    'midtrans_response' => json_encode($payload)
                ]
            );

            // Handle different payment statuses
            if ($payload['transaction_status'] === 'settlement') {
                // Payment successful - DP sudah dibayar (termasuk biaya layanan)
                // Kita perlu menghitung ulang total_bill dikurangi hanya DP (tanpa biaya layanan)
                // Atau, kita bisa menyimpan jumlah DP original saat checkout.
                
                // Asumsi: Gross_amount yang diterima di webhook sudah termasuk biaya layanan.
                // Jika ingin sisa tagihan dikurangi hanya DP 50% (tanpa biaya layanan),
                // Anda perlu menyimpan nilai DP 50% murni (paymentAmountDP) di `payments` table
                // atau di `reservasi` table saat checkoutFromCart.
                
                // Untuk saat ini, kita akan asumsikan 'amount' di table payments adalah jumlah DP + biaya layanan.
                // Kita perlu menyesuaikan logika sisa_tagihan_reservasi jika 'amount_paid' hanya untuk DP murni.

                // Untuk sementara, kita akan kurangi sisa_tagihan_reservasi dengan amount yang dibayarkan Midtrans.
                // Jika Anda ingin sisa tagihan hanya berkurang sebesar DP 50% saja, Anda perlu menyimpan
                // `paymentAmountDP` (tanpa biaya layanan) di `Payment` model atau di `Reservasi` model
                // saat `checkoutFromCart` dan menggunakannya di sini.

                // Mendapatkan nilai DP 50% murni dari total_bill
                $dp_murni = $reservasi->total_bill * 0.5;
                // Menambahkan biaya layanan ke DP murni
                $dp_plus_biaya_layanan = $dp_murni + round($dp_murni * 0.10);

                // Jika `gross_amount` (amount yang dibayar di midtrans) sama dengan `dp_plus_biaya_layanan`,
                // maka yang kita anggap sebagai 'amount_paid' untuk mengurangi 'sisa_tagihan_reservasi'
                // adalah `dp_murni`.
                $amount_for_reducing_bill = $dp_murni;

                $totalPaid = Payment::where('reservasi_id', $reservasi->id)
                                    ->where('status', 'settlement')
                                    ->sum('amount'); // Ini masih jumlah total Midtrans
                
                // Logika sisa_tagihan_reservasi harus dikurangi dengan DP murni, BUKAN totalMidtrans
                // Jadi kita perlu tahu berapa DP murni yang terkait dengan pembayaran ini.
                // Cara paling aman adalah menyimpannya di `payments` table atau `reservasi` table
                // saat checkout. Karena kita tidak mengubah struktur tabel,
                // kita akan menghitung ulang `dp_murni` dari `reservasi->total_bill`.
                
                $reservasi->update([
                    'amount_paid' => $dp_murni, // Simpan hanya DP murni sebagai amount_paid
                    'sisa_tagihan_reservasi' => $reservasi->total_bill - $dp_murni,
                    'payment_method' => $payload['payment_type'],
                    'status' => 'confirmed' // Status confirmed karena DP sudah dibayar
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
                        'amount_paid'  => $gross_amount, // Ini yang dibayar di Midtrans (termasuk biaya layanan)
                        'remaining_amount' => $reservasi->total_bill - $dp_murni // Sisa tagihan dikurangi DP murni
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
                    'midtrans_gross_amount' => $gross_amount, // Jumlah yang diterima dari Midtrans
                    'dp_murni_yang_diakui' => $dp_murni,
                    'remaining_amount_after_dp' => $reservasi->total_bill - $dp_murni
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
            ]);
            
            return response()->json(['message' => 'Failed to process notification'], 500);
        }
    }
}