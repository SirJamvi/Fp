<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller; // <-- TAMBAHKAN BARIS INI
use App\Helpers\MidtransHelper;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Midtrans\Snap;

class MidtransController extends Controller
{
    /**
     * Menangani proses checkout dari keranjang belanja di aplikasi mobile.
     * Menerima detail reservasi & item, menghitung total,
     * menyimpan order, dan mengembalikan Snap Token untuk pembayaran 50%.
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
            'service_fee' => 'required|numeric|min:0',
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

            // Hapus pesanan lama di reservasi ini untuk diganti dengan isi keranjang terbaru
            $reservasi->orders()->delete();

            $subtotal = 0;
            $item_details_midtrans = [];

            // 2. Kalkulasi ulang total di backend dari data cart
            foreach ($request->input('cart') as $item) {
                $menu = Menu::find($item['id']);
                $price = $menu->final_price ?? $menu->price;
                $totalItemPrice = $price * $item['quantity'];
                $subtotal += $totalItemPrice;

                // Buat record Order baru untuk setiap item di keranjang
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

                // Siapkan detail item untuk dikirim ke Midtrans
                $item_details_midtrans[] = [
                    'id'       => (string) $menu->id,
                    'price'    => (int) $price,
                    'quantity' => (int) $item['quantity'],
                    'name'     => substr($menu->name, 0, 50), // Batasi panjang nama menu
                ];
            }

            // Tambahkan Biaya Layanan
            $serviceFee = (int) $request->input('service_fee');
            $item_details_midtrans[] = [
                'id' => 'SERVICE_FEE',
                'price' => $serviceFee,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];

            $totalAmount = $subtotal + $serviceFee;
            $paymentAmountDP = $totalAmount * 0.5; // << INI LOGIKA UTAMA: HITUNG 50% DP

            // Update reservasi dengan total tagihan dan status baru
            $reservasi->update([
                'total_bill' => $totalAmount,
                'sisa_tagihan_reservasi' => $totalAmount, // Sisa tagihan awalnya adalah full
                'status' => 'pending_payment',
            ]);

            // 3. Siapkan parameter dan panggil Midtrans
            MidtransHelper::configure();

            $orderId = 'RES-' . $reservasi->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $paymentAmountDP, // Kirim jumlah 50% ke Midtrans
                ],
                'customer_details' => [
                    'first_name' => $user->nama,
                    'email' => $user->email,
                    'phone' => $user->nomor_hp,
                ],
                'item_details' => $item_details_midtrans,
                 'custom_field1' => $reservasi->id, // Menyimpan ID Reservasi untuk webhook
            ];

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            // 4. Kembalikan token ke frontend
            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Gagal memproses checkout.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menangani notifikasi webhook dari Midtrans.
     */
    public function handleNotification(Request $request)
    {
        MidtransHelper::configure();
        $payload = $request->all();
        $order_id = $payload['order_id'];
        $status_code = $payload['status_code'];
        $gross_amount = $payload['gross_amount'];
        $serverKey = config('services.midtrans.server_key');

        $signature = hash('sha512', $order_id . $status_code . $gross_amount . $serverKey);

        if ($payload['signature_key'] !== $signature) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Cari reservasi berdasarkan custom_field1 jika ada
        $reservasi_id = $payload['custom_field1'] ?? null;
        if (!$reservasi_id) {
             // Fallback jika custom_field1 tidak ada
            $orderIdParts = explode('-', $order_id);
            if(count($orderIdParts) > 1 && $orderIdParts[0] === 'RES') {
                $reservasi_id = $orderIdParts[1];
            }
        }
        
        $reservasi = Reservasi::find($reservasi_id);
        if (!$reservasi) {
            return response()->json(['message' => 'Reservasi tidak ditemukan'], 404);
        }

        // Simpan data pembayaran
        Payment::updateOrCreate(
            ['order_id' => $order_id],
            [
                'reservasi_id' => $reservasi->id,
                'amount' => $gross_amount,
                'payment_type' => $payload['payment_type'],
                'status' => $payload['transaction_status'],
                'deposit' => true,
            ]
        );

        // Update status reservasi jika pembayaran DP berhasil
        if ($payload['transaction_status'] === 'settlement' && $reservasi->status === 'pending_payment') {
             $reservasi->update([
                 'sisa_tagihan_reservasi' => DB::raw("total_bill - {$gross_amount}"),
                 'status' => 'confirmed' // Status berubah menjadi terkonfirmasi setelah DP lunas
             ]);
        }
        
        return response()->json(['message' => 'Notification handled']);
    }
}