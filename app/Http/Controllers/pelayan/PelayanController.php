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
        // Validate request data
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
            $meja = Meja::findOrFail($request->meja_id);
            $pelayan = Auth::user(); // Get the currently logged-in user (pelayan)

            // Check if the selected table is actually available before creating order
            if ($meja->status !== 'tersedia') {
                 DB::rollBack();
                 return response()->json(['success' => false, 'message' => "Meja nomor {$meja->nomor_meja} sedang tidak tersedia."], 400);
            }


            // Generate a unique reservation code
            $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . Carbon::now()->format('YmdHis') . Str::random(6);
            }

            // Create the Reservation record
            $reservasi = Reservasi::create([
                'kode_reservasi' => $kodeReservasi,
                'meja_id' => $request->meja_id,
                'user_id' => null, // Customer ID (null if walk-in). ENSURE THIS COLUMN IS NULLABLE IN DB
                'staff_id' => $pelayan->id, // ID of the pelayan creating the reservation. ENSURE THIS COLUMN EXISTS IN DB & IS IN $fillable
                'nama_pelanggan' => $request->nama_pelanggan ?? 'Walk-in Customer',
                'jumlah_tamu' => $request->jumlah_tamu,
                'waktu_kedatangan' => now(), // Assuming arrival time is now for walk-in orders
                'status' => 'active_order', // Status when order is created and table is occupied
                'source' => 'dine_in', // <--- Set source to 'dine_in' for orders created by pelayan
                'kehadiran_status' => 'hadir', // <--- Set kehadiran_status to 'hadir' for dine-in orders
                // 'total_bill' will be updated after orders are created
            ]);

            $totalHargaKeseluruhan = 0;

            // Create Order records for each item
            // Ensure 'reservasi_id', 'menu_id', 'user_id', 'quantity',
            // 'price_at_order', 'total_price', 'notes', and 'status'
            // are in the $fillable array on the Order model.
            foreach ($request->items as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);
                if (!$menu->is_available) {
                    DB::rollBack();
                    // Return JSON error instead of redirect
                    return response()->json(['success' => false, 'message' => "Menu '{$menu->name}' tidak tersedia saat ini."], 400);
                }
                Order::create([
                    'reservasi_id' => $reservasi->id,
                    'menu_id' => $itemData['menu_id'],
                    'user_id' => $pelayan->id, // ID of the pelayan inputting this order item. ENSURE THIS COLUMN EXISTS IN DB & IS IN $fillable, and this user ID EXISTS IN users/pengguna table
                    'quantity' => $itemData['quantity'],
                    'price_at_order' => $menu->price,
                    'total_price' => $menu->price * $itemData['quantity'],
                    'notes' => $itemData['notes'] ?? null,
                    'status' => 'pending', // Initial status for kitchen/bar
                ]);
                $totalHargaKeseluruhan += ($menu->price * $itemData['quantity']);
            }

            // Update total_bill in the reservation
            // Ensure 'total_bill' is in the $fillable array on the Reservasi model
            $reservasi->total_bill = $totalHargaKeseluruhan;
            $reservasi->save();

             // Update table status to 'terisi' if it was 'tersedia'
             // Table is considered occupied once the order is placed
             if ($meja->status == 'tersedia') {
                 // Ensure 'status' is in the $fillable array on the Meja model
                 $meja->status = 'terisi';
                 $meja->save();
             }

            DB::commit();

            // Return JSON response with reservation details for the payment modal
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'reservasi_id' => $reservasi->id,
                'total_bill' => $reservasi->total_bill,
                'kode_reservasi' => $reservasi->kode_reservasi // Send reservation code as well
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            Log::error('Error creating order during AJAX store: ' . $e->getMessage() . ' Stack trace: ' . $e->getTraceAsString());
            // Return JSON error response
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()], 500);
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

            $totalBill = $reservasi->total_bill; // Get the total bill from the reservation
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
                //      $meja->status = 'tersedia'; // Or 'cleaning'
                //      $meja->save();
                // }


            } elseif ($request->payment_method === 'qris') {
                // --- ACTUAL MIDTRANS INTEGRATION FOR QRIS STARTS HERE ---

                // Set your Merchant Server Key
                Config::$serverKey = config('services.midtrans.server_key');
                // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
                Config::$isProduction = config('services.midtrans.is_production', false); // Ambil dari config
                // Set sanitization on (default)
                Config::$isSanitized = true;
                // Set 3DS transaction for credit card to true
                Config::$is3ds = true;

                // Prepare item details for Midtrans payload
                $item_details = [];
                foreach ($reservasi->orders as $order) {
                    $item_details[] = [
                        'id' => $order->menu->id, // Use menu ID or order item ID
                        'price' => $order->price_at_order,
                        'quantity' => $order->quantity,
                        'name' => $order->menu->name // Menu name
                    ];
                }

                // Prepare customer details (optional but recommended)
                $customer_details = [
                    'first_name' => $reservasi->nama_pelanggan ?? 'Pelanggan', // Use customer name from reservation
                    // You can add last_name, email, phone if available in your Reservasi model or related user model
                ];

                // Prepare transaction details
                $transaction_details = [
                    'order_id' => $reservasi->kode_reservasi, // Use reservation code as order ID
                    'gross_amount' => $totalBill, // Total amount
                ];

                // Optional: Set expiry time for the transaction
                // $expiry_time = [
                //     'unit' => \Midtrans\Expire::UNIT_HOUR,
                //     'quantity' => 24, // Example: 24 hours expiry
                // ];


                // Assemble the payload
                $params = [
                    'transaction_details' => $transaction_details,
                    'item_details' => $item_details,
                    'customer_details' => $customer_details,
                    // 'expiry' => $expiry_time, // Include expiry if set
                    'callbacks' => [
                        'finish' => route('pelayan.order.summary', $reservasi->id), // Redirect after payment success/finish
                        // Anda juga bisa menambahkan 'error' dan 'pending' URLs jika perlu
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
                $reservasi->payment_method = 'qris';
                $reservasi->status = 'pending_payment'; // Set status to pending payment
                $reservasi->save();

                // --- ACTUAL MIDTRANS INTEGRATION FOR QRIS ENDS HERE ---

                // Note: The actual payment status update (to 'paid') should happen
                // via Midtrans Notification URL (webhook) that you configure in Midtrans Dashboard.
                // This method only initiates the transaction and gets the token.
            }

            DB::commit();

            // Return JSON response with success message, change (if cash), redirect URL, AND snap_token (if qris)
            $response = [
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Lanjutkan ke pembayaran.',
                'change' => $changeGiven, // Will be 0 for QRIS
                'redirect_url' => route('pelayan.order.summary', $reservasi->id), // URL to redirect after success (used by frontend for cash)
            ];

            // Add snap_token to response ONLY if payment method is qris
            if ($request->payment_method === 'qris' && $snapToken) {
                $response['snap_token'] = $snapToken;
                // For QRIS, frontend will use snap_token to show the popup, not redirect immediately
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
            // Load reservation with necessary relations
            // Ensure 'meja', 'orders.menu', and 'staffYangMembuat' relations exist on the Reservasi model
            $reservasi = Reservasi::with(['meja', 'orders.menu', 'staffYangMembuat'])->findOrFail($reservasi_id);

            // Prepare data for the summary view
            $orderSummary = [
                'reservasi_id' => $reservasi->id,
                'kode_reservasi' => $reservasi->kode_reservasi,
                'nomor_meja' => $reservasi->meja->nomor_meja ?? 'N/A',
                'area_meja' => $reservasi->meja->area ?? 'N/A',
                'nama_pelanggan' => $reservasi->nama_pelanggan,
                // Get pelayan name from staffYangMembuat relation (if staff_id is foreign key to users/pengguna)
                'nama_pelayan' => $reservasi->staffYangMembuat->name ?? (Auth::check() ? Auth::user()->name : 'N/A'),
                'waktu_pesan' => $reservasi->created_at,
                'items' => [],
                'total_keseluruhan' => $reservasi->total_bill ?? 0, // Get from total_bill or default to 0
                'payment_method' => $reservasi->payment_method ?? 'N/A', // Add payment method
                'payment_status' => $reservasi->status, // Add payment status (reservation status)
                'waktu_pembayaran' => $reservasi->waktu_selesai, // Add payment time (completion time)
            ];

            // Populate items array
            foreach ($reservasi->orders as $order) {
                $orderSummary['items'][] = [
                    'nama_menu' => $order->menu->name ?? 'N/A',
                    'quantity' => $order->quantity,
                    'harga_satuan' => $order->price_at_order,
                    'subtotal' => $order->total_price,
                    'catatan' => $order->notes,
                    'status' => $order->status, // Optional, display item status if relevant
                ];
            }

            // Recalculate total if total_bill is null (should not happen after storeOrder fix)
            if(is_null($reservasi->total_bill)) {
                 $orderSummary['total_keseluruhan'] = $reservasi->orders->sum('total_price');
            }


            // Pass data to the view
            return view('pelayan.summary', [
                'title' => 'Ringkasan Pesanan #' . $reservasi->kode_reservasi, // Use reservation code in title
                'orderSummary' => $orderSummary,
                'reservasi' => $reservasi // Pass the full reservation object as well (useful in view)
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
                     ->whereIn('status', ['confirmed', 'pending_arrival', 'active_order', 'paid', 'pending_payment']); // Include 'paid' and 'pending_payment' status

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
                 case 'paid': // New filter for paid status
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

 public function prosesScanQr($kodeReservasi)
{
    // Bersihkan kode reservasi dari URL jika ada
    $baseUrl = url('/pelayan/scanqr/proses/');
    if (strpos($kodeReservasi, $baseUrl) !== false) {
        $kodeReservasi = str_replace($baseUrl, '', $kodeReservasi);
    }
    
    $kodeReservasi = trim($kodeReservasi);

    // Proses reservasi...
    $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();

    if (!$reservasi) {
        return redirect()->route('pelayan.scanqr')->with('error', 'Reservasi tidak ditemukan');
    }

    // Update status reservasi
    $reservasi->update([
        'status' => 'selesai',
        'waktu_kedatangan' => now()
    ]);

    return redirect()->route('pelayan.reservasi')->with('success', 'Reservasi berhasil dikonfirmasi');
}

    // Method detailReservasi to show details of an existing reservation
    public function detailReservasi($id)
    {
        try {
            // Use 'staffYangMembuat' relation if staff_id is foreign key to pengguna/users
            $reservasi = Reservasi::with(['pengguna', 'meja', 'orders.menu', 'staffYangMembuat'])->findOrFail($id);
            $totalHarga = $reservasi->total_bill ?? $reservasi->orders->sum('total_price'); // Get from total_bill or calculate if null

            return view('pelayan.reservasi_detail', [
                'title' => 'Detail Reservasi #' . $reservasi->kode_reservasi, // Use reservation code in title
                'reservasi' => $reservasi,
                'totalHarga' => $totalHarga
            ]);
        } catch (\Exception $e) {
            Log::error("Error showing reservation detail: " . $e->getMessage());
            return redirect()->route('pelayan.reservasi')->with('error', 'Gagal menampilkan detail reservasi.');
        }
    }
}
