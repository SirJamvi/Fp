<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
     */
    public function checkoutFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservasi_id' => 'required|exists:reservasi,id',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:menus,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.note' => 'nullable|string|max:255',
            'service_fee' => 'required|numeric|min:0',

            // --- PERBARUI BARIS INI ---
            // Tambahkan semua metode pembayaran yang valid dari frontend Anda
            'payment_method' => 'required|string|in:gopay,shopeepay,dana,ovo,bca_va,mandiri_va,bri_va,bni_va,credit_card,indomaret,alfamart',
        ]);

        if ($validator->fails()) {
            // Error akan muncul dari sini jika payment_method tidak ada dalam daftar 'in' di atas
            return response()->json(['message' => 'Data keranjang atau pembayaran tidak valid.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $reservasi = Reservasi::findOrFail($request->input('reservasi_id'));

            if ($reservasi->user_id !== $user->id) {
                return response()->json(['message' => 'Reservasi tidak ditemukan.'], 403);
            }

            $reservasi->orders()->delete();

            $subtotal = 0;
            $item_details_midtrans = [];

            foreach ($request->input('cart') as $item) {
                $menu = Menu::find($item['id']);
                $price = $menu->final_price ?? $menu->price;
                $totalItemPrice = $price * $item['quantity'];
                $subtotal += $totalItemPrice;

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

                $item_details_midtrans[] = [
                    'id'       => (string) $menu->id,
                    'price'    => (int) $price,
                    'quantity' => (int) $item['quantity'],
                    'name'     => substr($menu->name, 0, 50),
                ];
            }

            $serviceFee = (int) $request->input('service_fee');
            $item_details_midtrans[] = [
                'id' => 'SERVICE_FEE',
                'price' => $serviceFee,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];

            $totalAmount = $subtotal + $serviceFee;
            $paymentAmountDP = $totalAmount * 0.5;

            $reservasi->update([
                'total_bill' => $totalAmount,
                'sisa_tagihan_reservasi' => $totalAmount,
                'status' => 'pending_payment',
            ]);

            MidtransHelper::configure();
            $orderId = 'RES-' . $reservasi->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $paymentAmountDP,
                ],
                'customer_details' => [
                    'first_name' => $user->nama,
                    'email' => $user->email,
                    'phone' => $user->nomor_hp,
                ],
                'item_details' => $item_details_midtrans,
                 'custom_field1' => $reservasi->id,
            ];

            $selectedPayment = $request->input('payment_method');
            if ($selectedPayment) {
                 $params['enabled_payments'] = [$selectedPayment];
            }

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

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

        $reservasi_id = $payload['custom_field1'] ?? null;
        if (!$reservasi_id) {
            $orderIdParts = explode('-', $order_id);
            if(count($orderIdParts) > 1 && $orderIdParts[0] === 'RES') {
                $reservasi_id = $orderIdParts[1];
            }
        }
        
        $reservasi = Reservasi::find($reservasi_id);
        if (!$reservasi) {
            return response()->json(['message' => 'Reservasi tidak ditemukan'], 404);
        }

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

        if ($payload['transaction_status'] === 'settlement' && $reservasi->status === 'pending_payment') {
             $reservasi->update([
                 'sisa_tagihan_reservasi' => DB::raw("total_bill - {$gross_amount}"),
                 'status' => 'confirmed'
             ]);
        }
        
        return response()->json(['message' => 'Notification handled']);
    }
}