<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PreOrderRequest;
use App\Models\Reservasi;
use App\Models\Order;
use App\Models\Menu;
use App\Services\OrderService;
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
     * Buat pra-pemesanan (PreOrder) dengan DP 50%
     */
    public function storePreOrder(PreOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();

            // 1) Generate kode pra-order
            do {
                $kodeReservasi = 'PO-'.Str::upper(Str::random(6));
            } while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists());

            // 2) Simpan reservasi kosong dulu
            $reservasi = Reservasi::create([
                'user_id'                => $user->id,
                'meja_id'                => null,
                'nama_pelanggan'         => $user->nama,
                'waktu_kedatangan'       => Carbon::now()->toDateTimeString(),
                'jumlah_tamu'            => $request->jumlah_tamu ?? 1,
                'kehadiran_status'       => 'belum',
                'status'                 => 'pending_payment',
                'payment_status'         => 'partial',     // DP = partial
                'payment_method'         => null,
                'source'                 => 'online',
                'kode_reservasi'         => $kodeReservasi,
                'catatan'                => $request->catatan,
                'total_bill'             => 0,
                'dp_terbayar'            => 0,
                'sisa_tagihan_reservasi' => 0,
            ]);

            // 3) Tambah order item & hitung subtotal
            $subtotal = 0;
            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);

                $priceAtOrder = $menu->final_price ?? 0;
                $totalItem    = $priceAtOrder * $item['quantity'];

                Order::create([
                    'reservasi_id'   => $reservasi->id,
                    'menu_id'        => $menu->id,
                    'user_id'        => $user->id,
                    'quantity'       => $item['quantity'],
                    'price_at_order' => $priceAtOrder,
                    'total_price'    => $totalItem,
                    'notes'          => $item['notes'] ?? null,
                    'status'         => 'pending',
                ]);

                $subtotal += $totalItem;
            }

            // 4) Hitung service charge & pajak jika perlu
            $serviceChargeRate = 0.10; // 10%
            $taxRate           = 0.11; // 11%
            $serviceCharge     = (int) round($subtotal * $serviceChargeRate);
            $afterService      = $subtotal + $serviceCharge;
            $tax               = (int) round($afterService * $taxRate);

            // 5) Hitung total akhir
            $totalBill = $subtotal + $serviceCharge + $tax;

            // 6) DP 50% & sisa
            $dpBayar       = (int) round(0.5 * $totalBill);
            $sisaTagihan   = $totalBill - $dpBayar;

            // 7) Update reservasi dengan angka final
            $reservasi->update([
                'total_bill'             => $totalBill,
                'dp_terbayar'            => $dpBayar,
                'sisa_tagihan_reservasi' => $sisaTagihan,
            ]);

            // 8) Notifikasi
            CustomerNotification::create([
                'user_id' => $user->id,
                'type'    => 'reservation_created',
                'title'   => 'Pesanan Diterima',
                'message' => "Kode #{$kodeReservasi}, total: Rp{$totalBill}",
                'data'    => ['reservasi_id' => $reservasi->id],
            ]);

            DB::commit();

            return response()->json([
                'message'   => 'Pra-pemesanan berhasil dibuat.',
                'reservasi' => $reservasi->load('orders.menu'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat pra-pemesanan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tambah item ke pesanan yang sudah ada (DP tetap 50% dari total baru)
     */
    public function addItemsToReservation(Request $request, Reservasi $reservasi)
    {
        $this->authorize('update', $reservasi); // Pastikan policy atau manual check user_id

        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.menu_id'  => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes'    => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1) Tambah items & hitung subtotal baru
            $newSubtotal = 0;
            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $priceAtOrder = $menu->final_price ?? 0;
                $totalItem    = $priceAtOrder * $item['quantity'];

                Order::create([
                    'reservasi_id'   => $reservasi->id,
                    'menu_id'        => $menu->id,
                    'user_id'        => $reservasi->user_id,
                    'quantity'       => $item['quantity'],
                    'price_at_order' => $priceAtOrder,
                    'total_price'    => $totalItem,
                    'notes'          => $item['notes'] ?? null,
                    'status'         => 'pending',
                ]);

                $newSubtotal += $totalItem;
            }

            // 2) Hitung kembali total (termasuk service & pajak)
            $serviceCharge = (int) round($newSubtotal * 0.10);
            $afterService  = $newSubtotal + $serviceCharge;
            $tax           = (int) round($afterService * 0.11);
            $newTotalBill  = $newSubtotal + $serviceCharge + $tax;

            // 3) Total keseluruhan = yang lama + yang baru
            $grandTotal    = $reservasi->total_bill + $newTotalBill;

            // 4) Recalculate DP & sisa: DP = 50% × grandTotal
            $newDpBayar     = (int) round(0.5 * $grandTotal);
            $newSisaTagihan = $grandTotal - $newDpBayar;

            // 5) Update reservasi
            $reservasi->update([
                'total_bill'             => $grandTotal,
                'dp_terbayar'            => $newDpBayar,
                'sisa_tagihan_reservasi' => $newSisaTagihan,
            ]);

            DB::commit();

            return response()->json([
                'message'   => 'Item berhasil ditambahkan.',
                'reservasi' => $reservasi->fresh()->load('orders.menu'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan item.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar pesanan milik user
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $orders = Order::where('user_id', $user->id)
                      ->with('menu','reservasi')
                      ->orderBy('created_at','desc')
                      ->paginate(10);

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil.',
            'orders'  => $orders,
        ], 200);
    }
}
