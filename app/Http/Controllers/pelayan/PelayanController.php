<?php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Models\Pengguna; // Ganti App\Models\User menjadi App\Models\Pengguna jika itu model user Anda
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config; // Import Midtrans Config
use Midtrans\Snap; // Import Midtrans Snap

class PelayanController extends Controller
{
    // Method index to display the dashboard view
    public function index()
    {
        try {
            // Fetch menus grouped by category
            $menusByCategory = Menu::where('is_available', true)
                ->orderBy('category')
                ->get()
                ->groupBy('category');

            // Get distinct categories
            $categories = Menu::select('category')->distinct()->pluck('category');

            // Fetch tables that are available or occupied
            // Status 'terisi' should mean occupied by a customer
            $mejas = Meja::whereIn('status', ['tersedia', 'terisi'])->orderBy('nomor_meja')->get();

            return view('pelayan.dashboard', [
                'title' => 'Buat Pesanan Baru',
                'menusByCategory' => $menusByCategory,
                'categories' => $categories,
                'mejas' => $mejas,
            ]);
        } catch (\Exception $e) {
            Log::error("Error loading pelayan dashboard: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat halaman. Silakan coba lagi.');
        }
    }

    // Method storeOrder creates the reservation and initial orders
   public function storeOrder(Request $request)
{
    // Validasi input form
    $request->validate([
        'meja_id' => 'required|exists:meja,id',
        'nama_pelanggan' => 'nullable|string|max:255',
        'jumlah_tamu' => 'required|integer|min:1',
        'items' => 'required|array|min:1',
        'items.*.menu_id' => 'required|exists:menus,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.notes' => 'nullable|string|max:1000',
    ]);

    DB::beginTransaction();

    try {
        $mejaUtama = Meja::findOrFail($request->meja_id);
        $pelayan = Auth::user();
        $jumlahTamu = $request->jumlah_tamu;

        // Pastikan meja utama tersedia
        if ($mejaUtama->status !== 'tersedia') {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Meja nomor {$mejaUtama->nomor_meja} sedang tidak tersedia."
            ], 400);
        }

        $combinedTables = [$mejaUtama->id];
        $totalCapacity = $mejaUtama->kapasitas;

        // Jika kapasitas meja utama kurang dari jumlah tamu,
        // cari meja tambahan untuk menutupi kekurangan kapasitas.
        if ($totalCapacity < $jumlahTamu) {
            $mejaTambahanList = Meja::where('status', 'tersedia')
                ->where('id', '!=', $mejaUtama->id)
                ->where('area', $mejaUtama->area)
                ->orderBy('kapasitas', 'asc')
                ->get();

            foreach ($mejaTambahanList as $mejaTambahan) {
                $combinedTables[] = $mejaTambahan->id;
                $totalCapacity += $mejaTambahan->kapasitas;

                if ($totalCapacity >= $jumlahTamu) {
                    break; // kapasitas sudah cukup
                }
            }

            // Jika kapasitas gabungan masih kurang, batalkan transaksi
            if ($totalCapacity < $jumlahTamu) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada kombinasi meja yang tersedia untuk menampung jumlah tamu.'
                ], 400);
            }
        }
        // Jika kapasitas meja utama sudah cukup (jumlahTamu <= kapasitas meja),
        // tidak perlu cari tambahan meja dan langsung pakai meja utama saja.

        // Generate kode reservasi unik
        $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
        while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
            $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
        }

        // Hitung subtotal berdasarkan menu dan quantity
        $subtotal = 0;
        $orderItemsData = [];
        foreach ($request->items as $itemData) {
            $menu = Menu::findOrFail($itemData['menu_id']);
            if (!$menu->is_available) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Menu '{$menu->name}' tidak tersedia saat ini."
                ], 400);
            }

            $itemSubtotal = $menu->price * $itemData['quantity'];
            $subtotal += $itemSubtotal;

            $orderItemsData[] = [
                'menu_id' => $itemData['menu_id'],
                'quantity' => $itemData['quantity'],
                'price_at_order' => $menu->price,
                'total_price' => $itemSubtotal,
                'notes' => $itemData['notes'] ?? null,
            ];
        }

        $serviceCharge = 0; // Jika ada perhitungan service charge, tambahkan di sini
        $tax = 0;           // Jika ada perhitungan pajak, tambahkan di sini
        $finalTotalBill = $subtotal + $serviceCharge + $tax;

        // Simpan data reservasi
        $reservasi = Reservasi::create([
            'kode_reservasi' => $kodeReservasi,
            'meja_id' => $mejaUtama->id,
            'combined_tables' => json_encode($combinedTables),
            'user_id' => null,
            'staff_id' => $pelayan->id,
            'nama_pelanggan' => $request->nama_pelanggan ?? 'Walk-in Customer',
            'jumlah_tamu' => $jumlahTamu,
            'waktu_kedatangan' => now(),
            'status' => 'active_order',
            'source' => 'dine_in',
            'kehadiran_status' => 'hadir',
            'total_bill' => $finalTotalBill,
            'subtotal' => $subtotal,
            'service_charge' => $serviceCharge,
            'tax' => $tax,
        ]);

        // Simpan data order item
        foreach ($orderItemsData as $itemData) {
            Order::create([
                'reservasi_id' => $reservasi->id,
                'menu_id' => $itemData['menu_id'],
                'user_id' => $pelayan->id,
                'quantity' => $itemData['quantity'],
                'price_at_order' => $itemData['price_at_order'],
                'total_price' => $itemData['total_price'],
                'notes' => $itemData['notes'],
                'status' => 'pending',
            ]);
        }

        // Update status semua meja gabungan jadi 'terisi'
        foreach ($combinedTables as $mejaId) {
            $meja = Meja::find($mejaId);
            if ($meja && $meja->status == 'tersedia') {
                $meja->status = 'terisi';
                $meja->current_reservasi_id = $reservasi->id;
                $meja->save();
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
            'reservasi_id' => $reservasi->id,
            'total_bill' => $reservasi->total_bill,
            'kode_reservasi' => $reservasi->kode_reservasi,
            'combined_tables' => $combinedTables,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating order during AJAX store: ' . $e->getMessage() . ' Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()
        ], 500);
    }
}


    // Method to process payment via AJAX
    public function processPayment(Request $request, $reservasi_id)
    {
        // Validasi request data
        $request->validate([
            'payment_method' => 'required|in:cash,qris', // Ensure only 'cash' or 'qris'
            'amount_paid' => 'nullable|numeric|min:0', // Only required if payment_method is cash
        ]);

        DB::beginTransaction();

        try {
            // Load reservation with necessary relations
            $reservasi = Reservasi::with('meja', 'orders.menu')->findOrFail($reservasi_id); // Load meja and orders.menu relation here

            // Ensure the reservation is not already paid
            if ($reservasi->status === 'paid') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Pesanan ini sudah lunas.'], 400);
            }

            // --- Get the FINAL total bill from the reservation ---
            // This value was already calculated and saved in the storeOrder method
            $totalBill = $reservasi->total_bill;
            // --- End Get Total Bill ---

            $changeGiven = 0; // Default change is 0
            $snapToken = null; // Variable to hold Snap Token

            if ($request->payment_method === 'cash') {
                // Additional validation for cash payment
                if (is_null($request->amount_paid) || $request->amount_paid < $totalBill) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Jumlah uang tunai yang dibayarkan kurang dari total tagihan.'], 422);
                }

                $amountPaid = $request->amount_paid;
                $changeGiven = $amountPaid - $totalBill;

                // Update reservation status and payment details for Cash
                $reservasi->payment_method = 'cash';
                $reservasi->amount_paid = $amountPaid;
                $reservasi->change_given = $changeGiven;
                $reservasi->status = 'paid'; // Change status to paid
                $reservasi->waktu_selesai = now(); // Record completion time (if status is paid)
                $reservasi->save();

                // Update status of all related order items to 'served' or 'completed'
                $reservasi->orders()->update(['status' => 'served']);

                // *** REMOVED: Do NOT set table status to 'tersedia' immediately after payment ***
                // The table status should remain 'terisi' until manually changed later.
                // $meja = $reservasi->meja;
                // if ($meja) {
                //     $meja->status = 'tersedia'; // Or 'cleaning'
                //     $meja->save();
                // }


            } elseif ($request->payment_method === 'qris') {
                // --- MIDTRANS INTEGRATION FOR QRIS ---

                // Set your Merchant Server Key
                Config::$serverKey = config('services.midtrans.server_key');
                // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
                Config::$isProduction = config('services.midtrans.is_production', false); // Ambil dari config
                // Set sanitization on (default)
                Config::$isSanitized = true;
                // Set 3DS transaction for credit card to true
                Config::$is3ds = true;

                // Prepare item details for Midtrans payload
                // Now item_details should include the original order items,
                // PLUS the service charge and tax as separate items if you want them detailed in Midtrans.
                // The total gross_amount will be the $totalBill already calculated in storeOrder.
                $item_details = [];
                foreach ($reservasi->orders as $order) {
                    // Pastikan menu relation dimuat (sudah ditambahkan di with('orders.menu'))
                    if ($order->menu) {
                        $item_details[] = [
                            'id' => $order->menu->id, // Use menu ID or order item ID
                            'price' => (int) $order->price_at_order, // Pastikan integer
                            'quantity' => (int) $order->quantity, // Pastikan integer
                            'name' => $order->menu->name // Menu name
                        ];
                    }
                }

                // --- Add Service Charge and Tax as separate items for Midtrans detail ---
                // These amounts are already calculated and potentially saved in the reservation
                if ($reservasi->service_charge > 0) {
                    $item_details[] = [
                        'id' => 'service_charge',
                        'price' => (int) $reservasi->service_charge,
                        'quantity' => 1,
                        'name' => 'Biaya Layanan'
                    ];
                }
                 if ($reservasi->tax > 0) {
                    $item_details[] = [
                        'id' => 'tax',
                        'price' => (int) $reservasi->tax,
                        'quantity' => 1,
                        'name' => 'Pajak (PPN)'
                    ];
                }
                // --- End Add Service Charge and Tax ---


                // Prepare customer details (optional but recommended)
                $customer_details = [
                    'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan', // Use customer name from reservation
                    // You can add last_name, email, phone if available in your Reservasi model or related user model
                ];

                // Prepare transaction details
                $transaction_details = [
                    'order_id' => $reservasi->kode_reservasi . '-' . time(), // Gunakan kode reservasi + timestamp agar unik
                    'gross_amount' => (int) $totalBill, // <-- Use the FINAL totalBill from the reservation
                ];

                // Optional: Set expiry time for the transaction
                // $expiry_time = [
                //     'unit' => \Midtrans\Expire::UNIT_HOUR,
                //     'quantity' => 24, // Example: 24 hours expiry
                // ];


                // Assemble the payload
                $params = [
                    'transaction_details' => $transaction_details,
                    'item_details' => $item_details, // Now includes base items + fees
                    'customer_details' => $customer_details,
                    // 'expiry' => $expiry_time, // Include expiry if set
                    'callbacks' => [
                        // Finish URL ini hanya redirect browser setelah selesai
                        // Anda perlu mengkonfigurasi Notification URL (Webhook) di Midtrans Dashboard
                        // untuk menerima notifikasi status pembayaran real-time di backend.
                        'finish' => route('pelayan.order.summary', $reservasi->id),
                        // Anda juga bisa menambahkan 'error' dan 'pending' URLs jika perlu
                        // 'error' => route('pelayan.payment.error', $reservasi->id),
                        // 'pending' => route('pelayan.payment.pending', $reservasi->id),
                    ],
                ];

                // Get Snap Token from Midtrans
                try {
                    $snapToken = Snap::getSnapToken($params);
                    Log::info("Midtrans Snap Token generated for Reservasi ID {$reservasi_id}: " . $snapToken); // Log the token
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Midtrans Snap Token generation failed: ' . $e->getMessage() . ' Stack trace: ' . $e->getTraceAsString());
                    return response()->json(['success' => false, 'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()], 500);
                }

                // Update reservation status to pending payment
                // Status 'paid' akan diupdate via Midtrans webhook, BUKAN di sini.
                $reservasi->payment_method = 'qris';
                $reservasi->status = 'pending_payment'; // Set status to pending payment
                $reservasi->save();

                // --- END MIDTRANS INTEGRATION FOR QRIS ---
            }

            DB::commit();

            // Return JSON response with success message, change (if cash), redirect URL, AND snap_token (if qris)
            $response = [
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'change' => $changeGiven, // Will be 0 for QRIS
                // redirect_url hanya digunakan oleh frontend untuk pembayaran tunai
                // Untuk QRIS, frontend akan menggunakan snap_token untuk menampilkan popup
                'redirect_url' => ($request->payment_method === 'cash') ? route('pelayan.order.summary', $reservasi->id) : null,
            ];

            // Add snap_token to response ONLY if payment method is qris
            if ($request->payment_method === 'qris' && $snapToken) {
                $response['snap_token'] = $snapToken;
                // We don't set a redirect_url for QRIS here because the frontend
                // will handle the Snap popup and subsequent actions based on Midtrans callbacks.
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage() . ' Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500); // Use 500 status code for server error
        }
    }

    // Method showOrderSummary displays the summary view
    public function showOrderSummary($reservasi_id)
{
    try {
        $reservasi = Reservasi::with(['meja', 'orders.menu', 'staffYangMembuat'])->findOrFail($reservasi_id);

        // Tangani meja gabungan
        $combinedTables = [];
        if ($reservasi->combined_tables) {
            $combinedIds = is_string($reservasi->combined_tables)
                ? json_decode($reservasi->combined_tables, true)
                : $reservasi->combined_tables;

            if (is_array($combinedIds)) {
                $combinedTables = Meja::whereIn('id', $combinedIds)
                    ->orderBy('nomor_meja')
                    ->get()
                    ->toArray();
            }
        }

        $orderSummary = [
            'reservasi_id' => $reservasi->id,
            'kode_reservasi' => $reservasi->kode_reservasi,
            'nomor_meja' => $reservasi->meja->nomor_meja ?? 'N/A',
            'combined_tables' => $combinedTables,
            'area_meja' => $reservasi->meja->area ?? 'N/A',
            'nama_pelanggan' => $reservasi->nama_pelanggan,
            'nama_pelayan' => $reservasi->staffYangMembuat->name ?? (Auth::check() ? Auth::user()->name : 'N/A'),
            'waktu_pesan' => $reservasi->created_at,
            'items' => [],
            'total_keseluruhan' => $reservasi->total_bill,
            'subtotal' => $reservasi->subtotal ?? $reservasi->orders->sum('total_price'),
            'service_charge' => $reservasi->service_charge ?? 0,
            'tax' => $reservasi->tax ?? 0,
            'payment_method' => $reservasi->payment_method ?? 'N/A',
            'payment_status' => $reservasi->status,
            'waktu_pembayaran' => $reservasi->waktu_selesai,
        ];

        foreach ($reservasi->orders as $order) {
            $orderSummary['items'][] = [
                'nama_menu' => $order->menu->name ?? 'N/A',
                'quantity' => $order->quantity,
                'harga_satuan' => $order->price_at_order,
                'subtotal' => $order->total_price,
                'catatan' => $order->notes,
                'status' => $order->status,
            ];
        }

        return view('pelayan.summary', [
            'title' => 'Ringkasan Pesanan #' . $reservasi->kode_reservasi,
            'orderSummary' => $orderSummary,
            'reservasi' => $reservasi
        ]);
    } catch (\Exception $e) {
        Log::error("Error showing order summary: " . $e->getMessage());
        return redirect()->route('pelayan.dashboard')->with('error', 'Gagal menampilkan ringkasan pesanan.');
    }
}



    // Method reservasi (list of reservations)
    public function reservasi(Request $request)
    {
         // Use 'staffYangMembuat' relation if staff_id is foreign key to pengguna/users
         $query = Reservasi::with(['pengguna', 'meja', 'orders', 'staffYangMembuat'])
                      ->whereIn('status', ['confirmed', 'pending_arrival', 'active_order', 'paid', 'pending_payment', 'selesai', 'dibatalkan']); // Include all relevant status

         // Add filter for source if needed
         if ($request->has('source') && in_array($request->source, ['online', 'dine_in'])) {
             $query->where('source', $request->source);
         }


         if ($request->has('search') && !empty($request->search)) {
             $searchTerm = $request->search;
             $query->where(function($q) use ($searchTerm) {
                 $q->where('nama_pelanggan', 'like', '%' . $searchTerm . '%')
                      ->orWhere('kode_reservasi', 'like', '%' . $searchTerm . '%') // Search by reservation code
                      ->orWhere('id', 'like', '%' . $searchTerm . '%') // Search by reservation ID
                      ->orWhereHas('meja', function ($subq) use ($searchTerm) {
                           $subq->where('nomor_meja', 'like', '%' . $searchTerm . '%');
                      })
                      // Search by customer/user name (if user_id is filled)
                      ->orWhereHas('pengguna', function ($subq) use ($searchTerm) {
                           $subq->where('name', 'like', '%' . $searchTerm . '%');
                      })
                      // Search by staff name (if staff_id is filled)
                      ->orWhereHas('staffYangMembuat', function ($subq) use ($searchTerm) {
                           $subq->where('name', 'like', '%' . $searchTerm . '%');
                      });
             });
         }

         if ($request->has('filter') && !empty($request->filter)) {
             switch ($request->filter) {
                 case 'today':
                     $query->whereDate('waktu_kedatangan', Carbon::today());
                     break;
                 case 'upcoming':
                     $query->where('waktu_kedatangan', '>=', Carbon::now());
                     break;
                 case 'past_week':
                     $query->whereBetween('waktu_kedatangan', [Carbon::now()->subWeek(), Carbon::now()]);
                     break;
                 case 'paid': // Filter for paid status
                      $query->where('status', 'paid');
                      break;
                 case 'active': // Filter for active status (including active_order, pending_payment)
                      $query->whereIn('status', ['confirmed', 'pending_arrival', 'active_order', 'pending_payment']);
                      break;
                 case 'selesai': // Filter for manually completed
                      $query->where('status', 'selesai');
                      break;
                 case 'dibatalkan': // Filter for cancelled
                      $query->where('status', 'dibatalkan');
                      break;
             }
         } else {
             // Default filter: today and upcoming + active orders
              $query->where(function($q) {
                     $q->where('waktu_kedatangan', '>=', Carbon::today()->startOfDay())
                      ->orWhereIn('status', ['active_order', 'pending_payment']); // Include pending_payment
              });
         }

         // Add ordering by creation date descending by default, or arrival time if specified
         if ($request->has('filter') && ($request->filter === 'upcoming' || $request->filter === 'today')) {
              $query->orderBy('waktu_kedatangan', 'asc');
         } else {
              $query->orderBy('created_at', 'desc'); // Order by creation date for history view
         }


         $reservasi = $query->paginate(10);

         return view('pelayan.reservasi', [
              'title' => 'Daftar Reservasi',
              'reservasi' => $reservasi,
              'filter' => $request->filter ?? null,
              'search' => $request->search ?? null,
              'sourceFilter' => $request->source ?? null, // Pass source filter to view
         ]);
    }

    public function scanQr(Request $request)
    {
        return view('pelayan.scanqr', [
            'title' => 'Scan QR Code'
        ]);
    }

   public function bayarSisa($id)
{
    $reservasi = Reservasi::with('orders')->findOrFail($id);

    if ($reservasi->status === 'paid') {
        return redirect()->route('pelayan.reservasi')->with('info', 'Reservasi sudah dibayar lunas.');
    }

    $totalTagihan = $reservasi->orders->sum('total_price');
    $totalDibayar = $totalTagihan - ($reservasi->sisa_tagihan_reservasi ?? $totalTagihan);
    $sisa = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

    return view('pelayan.bayar-sisa', compact('reservasi', 'totalTagihan', 'totalDibayar', 'sisa'));
}

public function bayarSisaPost(Request $request, $id)
{
    $reservasi = Reservasi::with('orders')->findOrFail($id);

    $request->validate([
        'jumlah_dibayar' => 'required|numeric|min:1', // Minimal Rp 1
        'metode' => 'required|string|in:tunai,qris',
    ]);

    $totalTagihan = $reservasi->orders->sum('total_price');
    $sisa = $reservasi->sisa_tagihan_reservasi ?? $totalTagihan;

    // Validasi: pastikan pembayaran tidak melebihi sisa tagihan
    if ($request->jumlah_dibayar > $sisa) {
        return back()->with('error', 'Jumlah dibayar melebihi sisa tagihan.');
    }

    // Jika metode pembayaran tunai
    if ($request->metode === 'tunai') {
        DB::beginTransaction();
        try {
            // Kurangi sisa tagihan
            $reservasi->sisa_tagihan_reservasi = $sisa - $request->jumlah_dibayar;
            $reservasi->payment_method = $request->metode;

            if ($reservasi->sisa_tagihan_reservasi <= 0) {
                $reservasi->status = 'paid';
                $reservasi->sisa_tagihan_reservasi = 0;
            }

            $reservasi->save();
            DB::commit();

            return redirect()->route('pelayan.reservasi')->with('success', 'Pembayaran sisa berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }
    // Jika metode pembayaran QRIS
    elseif ($request->metode === 'qris') {
        DB::beginTransaction();
        try {
            // Konversi jumlah dibayar ke integer (dalam rupiah)
            $grossAmount = (int) round($request->jumlah_dibayar);
            
            // Validasi minimal pembayaran untuk Midtrans
            if ($grossAmount < 1) {
                throw new \Exception('Minimal pembayaran QRIS adalah Rp 1');
            }

            // Set Midtrans configuration
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Prepare transaction details
            $transaction_details = [
                'order_id' => $reservasi->kode_reservasi . '-PART-' . time(),
                'gross_amount' => $grossAmount,
            ];

            // Prepare item details (optional for partial payment)
            $item_details = [
                [
                    'id' => 'partial-payment',
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Pembayaran Sebagian Reservasi #' . $reservasi->kode_reservasi
                ]
            ];

            // Prepare customer details
           $customer_details = [
                'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan',
                'last_name' => '',
                'email' => $reservasi->pengguna->email ?? 'customer@restaurant.com', // Default email
                'phone' => $reservasi->pengguna->phone ?? '081234567890' // Default phone
            ];
            // Assemble the payload
            $params = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details,
                'payment_type' => 'qris',
                'callbacks' => [
                    'finish' => route('pelayan.reservasi.bayarSisa.callback', $reservasi->id),
                ],
                'expiry' => [
                    'unit' => 'hour',
                    'duration' => 24
                ]
            ];

            // Get Snap Token from Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Save payment data
            $reservasi->payment_method = 'qris';
            $reservasi->payment_token = $snapToken;
            $reservasi->payment_amount = $grossAmount;
            $reservasi->payment_status = 'pending';
            $reservasi->save();

            DB::commit();

            // Redirect to payment page
            return redirect()->route('pelayan.reservasi.bayarSisa.qris', $reservasi->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans QRIS payment failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pembayaran QRIS: ' . $e->getMessage());
        }
    }
}

public function showQrisPayment($id)
{
    $reservasi = Reservasi::findOrFail($id);
    
    return view('pelayan.qris-payment', [
        'snapToken' => $reservasi->payment_token,
        'reservasi' => $reservasi,
        'jumlah_dibayar' => $reservasi->payment_amount
    ]);
}

public function handleQrisCallback(Request $request, $id)
{
    $reservasi = Reservasi::findOrFail($id);
    
    // Verifikasi signature (penting untuk keamanan)
    // Implementasikan sesuai dokumentasi Midtrans
    
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    return response()->json(['status' => $request->transaction_status]);
}


 public function prosesScanQr($kodeReservasi)
{
    $kodeReservasi = trim($kodeReservasi);

    $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();

    if (!$reservasi) {
        return redirect()->route('pelayan.scanqr')->with('error', 'Reservasi tidak ditemukan.');
    }

    // Cek jika sudah pernah hadir
    if ($reservasi->kehadiran_status === 'hadir') {
        return redirect()->route('pelayan.reservasi')->with('error', 'Kehadiran sudah dikonfirmasi sebelumnya.');
    }

    // Update hanya kehadiran, tanpa mengubah status kalau sudah paid/selesai
    $updateData = [
        'kehadiran_status' => 'hadir',
        'waktu_kedatangan' => now(),
    ];

    // Kalau status masih dipesan, ubah jadi active_order
    if ($reservasi->status === 'dipesan') {
        $updateData['status'] = 'active_order';
    }

    $reservasi->update($updateData);

    return redirect()->route('pelayan.reservasi')->with('success', 'Kehadiran untuk reservasi #' . $reservasi->kode_reservasi . ' berhasil dikonfirmasi.');
}




    // Method to manually mark a reservation as completed (e.g., after table is cleared)
    public function completeReservation($reservasi_id)
    {
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::with('meja')->findOrFail($reservasi_id);

            if ($reservasi->status !== 'paid') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Reservasi hanya bisa diselesaikan jika statusnya sudah lunas.');
            }

            $reservasi->status = 'selesai';
            $reservasi->waktu_selesai = $reservasi->waktu_selesai ?? now();
            $reservasi->save();

            // Update status of all combined tables
            $combinedTables = $reservasi->combined_tables ?: [$reservasi->meja_id];
            
            foreach ($combinedTables as $mejaId) {
                $meja = Meja::find($mejaId);
                if ($meja) {
                    $meja->status = 'tersedia';
                    $meja->current_reservasi_id = null;
                    $meja->save();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Reservasi berhasil diselesaikan dan meja diatur kembali menjadi tersedia.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing reservation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyelesaikan reservasi. Silakan coba lagi.');
        }
    }

     // Method to cancel a reservation
     public function cancelReservation($reservasi_id)
     {
         DB::beginTransaction();
         try {
             $reservasi = Reservasi::with('meja')->findOrFail($reservasi_id);

             // Prevent cancellation if already paid or completed
             if (in_array($reservasi->status, ['paid', 'selesai'])) {
                  DB::rollBack();
                  return redirect()->back()->with('error', 'Reservasi yang sudah lunas atau selesai tidak bisa dibatalkan.');
             }

             $reservasi->status = 'dibatalkan'; // Set status to cancelled
             $reservasi->waktu_selesai = now(); // Record cancellation time
             $reservasi->save();

             // If the table was occupied by this reservation, set it back to available
             // Check if the table's current reservation is this one before changing status
             $meja = $reservasi->meja;
             if ($meja && $meja->status === 'terisi' && $meja->current_reservasi_id === $reservasi->id) { // Assuming you have a current_reservasi_id on Meja
                 $meja->status = 'tersedia';
                 $meja->current_reservasi_id = null; // Clear the current reservation link
                 $meja->save();
             } else if ($meja && $meja->status === 'terisi' && is_null($meja->current_reservasi_id)) {
                  // Handle case where table is terisi but no specific reservation linked (e.g. old data)
                  // You might decide how to handle this - setting to tersedia might be okay
                  $meja->status = 'tersedia';
                  $meja->save();
             }


             DB::commit();
             return redirect()->back()->with('success', 'Reservasi berhasil dibatalkan.');

         } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Error cancelling reservation: ' . $e->getMessage());
             return redirect()->back()->with('error', 'Gagal membatalkan reservasi. Silakan coba lagi.');
         }
     }

     // Method to add more items to an existing active order
     public function addItemsToOrder(Request $request, $reservasi_id)
     {
         // Validate request data
         $request->validate([
             'items' => 'required|array|min:1',
             'items.*.menu_id' => 'required|exists:menus,id',
             'items.*.quantity' => 'required|integer|min:1',
             'items.*.notes' => 'nullable|string|max:1000',
         ]);

         DB::beginTransaction();
         try {
             $reservasi = Reservasi::with('orders.menu')->findOrFail($reservasi_id);
             $pelayan = Auth::user(); // Get the currently logged-in user (pelayan)

             // Only allow adding items to active or pending_payment orders
             if (!in_array($reservasi->status, ['active_order', 'pending_payment'])) {
                  DB::rollBack();
                  return response()->json(['success' => false, 'message' => 'Tidak bisa menambahkan item ke reservasi dengan status ' . $reservasi->status], 400);
             }

             $newItemsSubtotal = 0;

             // Create new Order records for each item
             foreach ($request->items as $itemData) {
                  $menu = Menu::findOrFail($itemData['menu_id']);
                  if (!$menu->is_available) {
                       DB::rollBack();
                       return response()->json(['success' => false, 'message' => "Menu '{$menu->name}' tidak tersedia saat ini."], 400);
                  }
                  $itemSubtotal = $menu->price * $itemData['quantity'];
                  $newItemsSubtotal += $itemSubtotal;

                  Order::create([
                      'reservasi_id' => $reservasi->id,
                      'menu_id' => $itemData['menu_id'],
                      'user_id' => $pelayan->id, // Pelayan who added the item
                      'quantity' => $itemData['quantity'],
                      'price_at_order' => $menu->price,
                      'total_price' => $itemSubtotal,
                      'notes' => $itemData['notes'] ?? null,
                      'status' => 'pending', // Initial status for new items
                  ]);
             }

             // Recalculate the total bill for the reservation
             // Get current subtotal, service charge, and tax (if you saved them)
             $currentSubtotal = $reservasi->subtotal ?? $reservasi->orders->sum('total_price'); // Recalculate if not saved
             $currentServiceCharge = $reservasi->service_charge ?? 0;
             $currentTax = $reservasi->tax ?? 0;

             // Add new items subtotal to the current subtotal
             $updatedSubtotal = $currentSubtotal + $newItemsSubtotal;

             // Recalculate service charge and tax based on the updated subtotal
             $serviceChargeRate = 0.10; // 10%
             $taxRate = 0.11; // 11% (PPN)

             $updatedServiceCharge = (int) ($updatedSubtotal * $serviceChargeRate);
             $totalAfterService = $updatedSubtotal + $updatedServiceCharge;
             $updatedTax = (int) ($totalAfterService * $taxRate);

             $updatedTotalBill = $updatedSubtotal + $updatedServiceCharge + $updatedTax;

             // Update the reservation with the new totals
             $reservasi->total_bill = $updatedTotalBill;
             $reservasi->subtotal = $updatedSubtotal; // Save updated subtotal
             $reservasi->service_charge = $updatedServiceCharge; // Save updated service charge
             $reservasi->tax = $updatedTax; // Save updated tax
             $reservasi->save();


             DB::commit();

             // Return JSON response with updated reservation details
             return response()->json([
                 'success' => true,
                 'message' => 'Item berhasil ditambahkan ke pesanan.',
                 'reservasi_id' => $reservasi->id,
                 'total_bill' => $reservasi->total_bill, // Send the new total bill
                 'kode_reservasi' => $reservasi->kode_reservasi,
                 'updated_subtotal' => $reservasi->subtotal, // Send updated subtotal
                 'updated_service_charge' => $reservasi->service_charge, // Send updated service charge
                 'updated_tax' => $reservasi->tax, // Send updated tax
             ]);

         } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Error adding items to order: ' . $e->getMessage() . ' Stack trace: ' . $e->getTraceAsString());
             return response()->json(['success' => false, 'message' => 'Gagal menambahkan item ke pesanan: ' . $e->getMessage()], 500);
         }
     }
}
