<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PreOrderRequest; // Akan kita buat nanti
use App\Models\Reservasi; // Menggunakan model Reservasi yang ada
use App\Models\Order; // Menggunakan model Order yang ada
use App\Models\Menu; // Menggunakan model Menu yang ada
use App\Services\OrderService; // Menggunakan OrderService yang ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerNotification;
use Carbon\Carbon;
use Illuminate\Support\Str;


class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Allow customer to place a pre-order (without immediate table reservation).
     * This could be for pickup or future dine-in where table is assigned later.
     * This assumes a 'Reservasi' record is created as a container for the order.
     *
     * @param  \App\Http\Requests\Customer\PreOrderRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePreOrder(PreOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();

            // Create a 'Reservasi' record as a container for the pre-order
            // Meja_id bisa null atau diisi dengan meja default jika ada
            // Source bisa 'pre_order' atau 'pickup'/'delivery'
            $kodeReservasi = 'PO-' . strtoupper(Str::random(6)); // Kode khusus pre-order
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'PO-' . strtoupper(Str::random(6));
            }

            $reservasi = Reservasi::create([
                'user_id'           => $user->id,
                'meja_id'           => null, // Meja akan diassign nanti atau tidak diperlukan
                'nama_pelanggan'    => $user->nama,
                'waktu_kedatangan'  => Carbon::now(), // Waktu order dibuat
                'jumlah_tamu'       => $request->jumlah_tamu ?? 1, // Default 1 jika tidak ada
                'kehadiran_status'  => 'belum',
                'status'            => 'pending_payment', // Atau 'pending_pickup', 'pending_delivery'
                // Tetap menggunakan 'online' jika Anda belum menambahkan 'pre_order' ke ENUM di database
                'source'            => 'online',
                'kode_reservasi'    => $kodeReservasi,
                'catatan'           => $request->catatan,
                'total_bill'        => 0, // Akan diupdate setelah order items ditambahkan
                'sisa_tagihan_reservasi' => 0,
            ]);

            $totalBill = 0;
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                if (!$menu || !$menu->is_available) {
                    DB::rollBack();
                    return response()->json(['message' => "Menu '{$menu->name}' tidak tersedia."], 400);
                }

                // PASTIKAN harga tidak NULL, default ke 0.00 jika $menu->final_price NULL
                $priceAtOrder = $menu->final_price ?? 0.00;
                $totalItemPrice = $priceAtOrder * $item['quantity'];

                Order::create([
                    'reservasi_id' => $reservasi->id,
                    'menu_id'      => $item['menu_id'],
                    'user_id'      => $user->id, // User yang membuat order
                    'quantity'     => $item['quantity'],
                    'price_at_order' => $priceAtOrder,
                    'total_price'  => $totalItemPrice,
                    'notes'        => $item['notes'] ?? null,
                    'status'       => 'pending', // Status awal pesanan item
                ]);
                $totalBill += $totalItemPrice;
            }

            // HITUNG TOTAL BILL TANPA MENYIMPAN SUBTOTAL, SERVICE_CHARGE, DAN TAX DI KOLOM TERPISAH
            $serviceChargeRate = 0.10; // 10%
            $taxRate = 0.11; // 11% PPN (contoh)

            $serviceCharge = (int) ($totalBill * $serviceChargeRate);
            $totalAfterService = $totalBill + $serviceCharge;
            $tax = (int) ($totalAfterService * $taxRate);

            // Update hanya total_bill dan sisa_tagihan_reservasi
            $reservasi->total_bill = $totalBill + $serviceCharge + $tax;
            $reservasi->sisa_tagihan_reservasi = $reservasi->total_bill; // Awalnya sama dengan total_bill
            $reservasi->save();
            
             CustomerNotification::create([
                'user_id' => $user->id,
                'type'    => 'reservation_created', // Menggunakan tipe yang sudah ada di model Anda
                'title'   => 'Pesanan Diterima',
                'message' => "Pesanan Anda dengan kode #{$reservasi->kode_reservasi} telah berhasil dibuat.",
                'data'    => [ // Menyimpan data tambahan jika diperlukan
                    'reservasi_id' => $reservasi->id,
                    'total_bill'   => $reservasi->total_bill
                ]
            ]);


            DB::commit();

            return response()->json([
                'message' => 'Pra-pemesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'reservasi' => $reservasi->load('orders.menu'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat pra-pemesanan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Add items to an existing active reservation (for dine-in scenario).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservasi  $reservasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItemsToReservation(Request $request, Reservasi $reservasi)
    {
        // Pastikan reservasi ini milik pengguna yang sedang login dan statusnya aktif
        if ($reservasi->user_id !== Auth::id() || !in_array($reservasi->status, ['confirmed', 'check_in', 'pending_payment'])) {
            return response()->json(['message' => 'Reservasi tidak ditemukan atau tidak aktif.'], 404);
        }

        // Gunakan AddItemsRequest yang sudah ada, tapi namespace-nya berbeda
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:1000',
        ]);

        $result = $this->orderService->addItemsToOrder($request->items, $reservasi->id);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'reservasi' => $reservasi->fresh()->load('orders.menu'), // Refresh dan load relasi
            ], 200);
        } else {
            return response()->json([
                'message' => $result['message'],
            ], 400);
        }
    }

    /**
     * Get a list of the authenticated customer's orders (linked via reservations).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)
                        ->with('menu', 'reservasi')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil.',
            'orders' => $orders,
        ], 200);
    }
}