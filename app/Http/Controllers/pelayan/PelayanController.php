<?php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\AddItemsRequest;
use App\Services\OrderService;
use App\Services\ReservasiService;
use App\Services\PaymentService;
use App\Models\Reservasi;
use App\Models\Order;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Area;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PelayanController extends Controller
{
    protected $orderService;
    protected $reservasiService;
    protected $paymentService;

    public function __construct(OrderService $orderService, ReservasiService $reservasiService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->reservasiService = $reservasiService;
        $this->paymentService = $paymentService;
    }

     public function index()
    {
        try {
            // Ambil daftar area meja unik
            $areas = Meja::select('area')->distinct()->pluck('area');

            // Ambil kategori menu dari model (array)
            $categories = Menu::getCategoryOptions();

            // Ambil semua menu yang tersedia dan kelompokkan berdasarkan kategori
            $menusByCategory = Menu::where('is_available', true)
                ->orderBy('category')
                ->get()
                ->groupBy('category');

            // Ambil meja yang statusnya tersedia atau terisi
            $mejas = Meja::whereIn('status', ['tersedia', 'terisi'])
                ->orderBy('nomor_meja')
                ->get();

            return view('pelayan.dashboard', compact('areas', 'categories', 'menusByCategory', 'mejas'))
                ->with('title', 'Dashboard Pelayan');
        } catch (\Exception $e) {
            Log::error("Error loading pelayan dashboard: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat halaman. Silakan coba lagi.');
        }
    }


    public function storeOrder(StoreOrderRequest $request)
    {
        $result = $this->orderService->storeOrder($request);
        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }
    
    

    public function dinein(Request $request)
    {
        $dineInReservations = $this->reservasiService->getDineInReservations($request);
        return view('pelayan.dinein', compact('dineInReservations'));
    }

    public function showDetailReservasi($id)
{
    try {
        // 1) Tangkap context (reservasi atau dinein)
        $from = request()->query('from', 'reservasi');

        // 2) Eager‐load orders.menu dan mejaUtama (kolom meja_id)
        $reservasi = Reservasi::with(['orders.menu', 'mejaUtama'])
                              ->findOrFail($id);

        // 3) Ambil orders dan hitung total
        $orders     = Order::with('menu')
                           ->where('reservasi_id', $id)
                           ->get();
        $totalHarga = $orders->sum(fn($o) => $o->quantity * $o->menu->price);

        // 4) Kirim ke view, termasuk $from
        return view('pelayan.detail', [
            'title'      => 'Detail Menu',
            'reservasi'  => $reservasi,
            'orders'     => $orders,
            'totalHarga' => $totalHarga,
            'from'       => $from,
        ]);

    } catch (\Exception $e) {
        Log::error("Error loading detail reservasi: " . $e->getMessage());
        return redirect()->route('pelayan.reservasi')
                         ->with('error', 'Gagal memuat detail reservasi.');
    }
}

    public function processPayment(Request $request, $reservasi_id)
    {
        $result = $this->paymentService->processPayment($request, $reservasi_id);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    // PelayanController.php

public function bayarSisa(Request $request, $id, $status = null)
{
    // 1) Tangani flash jika datang dari Midtrans finish (suffix 'f')
    if ($status === 'f') {
        session()->flash('success', 'Pembayaran berhasil!');
    }
    // (bila mau juga untuk 'e' atau 'u', bisa tambahkan elseif di sini)

    // 2) Load data sisa via service (tidak perlu tahu $status)
    $result = $this->paymentService->bayarSisa($id);

    if (! $result['success']) {
        return redirect($result['redirect'])
               ->with('info', $result['message']);
    }

        // 3) Tampilkan view form bayar-sisa
        return view('pelayan.bayar-sisa', [
        'reservasi'         => $result['reservasi'],
        'totalTagihan'      => $result['totalTagihan'],
        'totalDibayar'      => $result['totalDibayar'],
        'sisa'              => $result['sisa'],
        'pajakNominal'      => $result['pajakNominal'],
        'pajakPersen'       => $result['pajakPersen'],
        'totalSetelahPajak' => $result['totalSetelahPajak'],
    ]);

}


    public function bayarSisaPost(Request $request, $id)
    {
        $result = $this->paymentService->bayarSisaPost($request, $id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        if (isset($result['redirect'])) {
            return redirect($result['redirect']);
        }

        return redirect()->route('pelayan.reservasi')->with('success', $result['message']);
    }

   public function showQrisPayment($id)
{
    $data = $this->paymentService->showQrisPayment($id);

    return view('pelayan.qris-payment', [
        'snap_token'     => $data['snap_token'],
        'reservasi'      => $data['reservasi'],
        'jumlah_dibayar' => $data['jumlah_dibayar'],
    ]);
}

public function settleQrisPayment(Request $request, $id)
{
    return $this->paymentService->settlePayment($request, $id);
}



    public function showOrderSummary($reservasi_id)
{
    try {
        $from = request()->query('from', 'reservasi');

        // Ambil reservasi beserta relasi orders.menu dan staffYangMembuat
        $reservasi = Reservasi::with([
            'orders.menu',
            'mejaReservasi.meja',  // untuk reservasi (online)
            'meja',                // untuk dine-in
            'staffYangMembuat'
        ])->findOrFail($reservasi_id);


        // Siapkan array untuk menyimpan semua data meja dari combined_tables
        $combinedTables = [];
        if ($reservasi->combined_tables) {
            $combinedIds = is_string($reservasi->combined_tables)
                ? json_decode($reservasi->combined_tables, true)
                : $reservasi->combined_tables;

            if (is_array($combinedIds) && count($combinedIds) > 0) {
                $combinedTables = Meja::whereIn('id', $combinedIds)
                    ->orderBy('nomor_meja')
                    ->get()
                    ->toArray();
            }
        }

        // Ambil data meja utama (first) jika memang ada
        $firstMeja = count($combinedTables) ? $combinedTables[0] : null;

        $orderSummary = [
            'reservasi_id'      => $reservasi->id,
            'kode_reservasi'    => $reservasi->kode_reservasi,
            // Ganti akses langsung ke $reservasi->meja dengan data dari $firstMeja
            'nomor_meja'        => $firstMeja['nomor_meja'] ?? 'N/A',
            'combined_tables'   => $combinedTables,
            'area_meja'         => $firstMeja['area'] ?? 'N/A',
            'nama_pelanggan'    => $reservasi->nama_pelanggan,
            'nama_pelayan'      => $reservasi->staffYangMembuat->name
                                     ?? (auth()->check() ? auth()->user()->name : 'N/A'),
            'waktu_pesan'       => $reservasi->created_at,
            'items'             => [],
            'total_keseluruhan' => $reservasi->total_bill,
            'subtotal'          => $reservasi->subtotal ?? $reservasi->orders->sum('total_price'),
            'service_charge'    => $reservasi->service_charge ?? 0,
            'tax'               => $reservasi->tax ?? 0,
            'payment_method'    => $reservasi->payment_method ?? 'N/A',
            'payment_status'    => $reservasi->status,
            'waktu_pembayaran'  => $reservasi->waktu_selesai,
        ];

        foreach ($reservasi->orders as $order) {
            $orderSummary['items'][] = [
                'nama_menu'     => $order->menu->name ?? 'N/A',
                'quantity'      => $order->quantity,
                'harga_satuan'  => $order->price_at_order,
                'subtotal'      => $order->total_price,
                'catatan'       => $order->notes,
                'status'        => $order->status,
            ];
        }

        return view('pelayan.summary', [
            'title'        => 'Ringkasan Pesanan #' . $reservasi->kode_reservasi,
            'orderSummary' => $orderSummary,
            'reservasi'    => $reservasi,
            'from'         => $from,
        ]);
    } catch (\Exception $e) {
        Log::error("Error showing order summary: " . $e->getMessage());
        return redirect()->route('pelayan.dashboard')->with('error', 'Gagal menampilkan ringkasan pesanan.');
    }
}


    public function addItemsToOrder(AddItemsRequest $request, $reservasi_id)
    {
        try {
            $reservasi = Reservasi::findOrFail($reservasi_id);
            $result = $this->orderService->addItemsToOrder($request, $reservasi);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error("Error adding items to order: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan item ke pesanan.'], 500);
        }
    }

   public function reservasi(Request $request)
{
    $query = Reservasi::with(['pengguna', 'meja', 'orders', 'staffYangMembuat'])
        ->whereNull('deleted_at') // Hanya data yang belum dihapus (soft delete)
        ->whereIn('status', [
            'confirmed', 'pending_arrival', 'active_order',
            'paid', 'pending_payment', 'selesai', 'dibatalkan'
        ])
        ->where('source', 'online');

    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function ($q) use ($searchTerm) {
            $q->where('nama_pelanggan', 'like', '%' . $searchTerm . '%')
              ->orWhere('kode_reservasi', 'like', '%' . $searchTerm . '%')
              ->orWhere('id', 'like', '%' . $searchTerm . '%')
              ->orWhereHas('meja', fn($subq) => $subq->where('nomor_meja', 'like', '%' . $searchTerm . '%'))
              ->orWhereHas('pengguna', fn($subq) => $subq->where('nama', 'like', '%' . $searchTerm . '%'))
              ->orWhereHas('staffYangMembuat', fn($subq) => $subq->where('nama', 'like', '%' . $searchTerm . '%'));
        });
    }

    if ($request->filled('filter')) {
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
            case 'paid':
                $query->where('status', 'paid');
                break;
            case 'active':
                $query->whereIn('status', ['confirmed', 'pending_arrival', 'active_order', 'pending_payment']);
                break;
            case 'selesai':
                $query->where('status', 'selesai');
                break;
            case 'dibatalkan':
                $query->where('status', 'dibatalkan');
                break;
        }
    } else {
        $query->where(function ($q) {
            $q->whereNotIn('status', ['dibatalkan', 'selesai'])
              ->orWhere(function ($sub) {
                  $sub->where('waktu_kedatangan', '>=', Carbon::today()->startOfDay())
                      ->orWhereNull('waktu_kedatangan');
              });
        });
    }

    // Urutan berdasarkan filter
    if ($request->filled('filter') && in_array($request->filter, ['today', 'upcoming'])) {
        $query->orderBy('waktu_kedatangan', 'asc');
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $reservasi = $query->paginate(10)->withQueryString();

    return view('pelayan.reservasi', [
        'title' => 'Daftar Reservasi',
        'reservasi' => $reservasi,
        'filter' => $request->filter ?? null,
        'search' => $request->search ?? null,
    ]);
}

public function storeReservasi(Request $request)
{
    $request->validate([
        'area' => 'required|string',
        'meja_id' => 'required|exists:meja,id',  // pastikan tabel benar
    ]);

    // Cek apakah meja masih tersedia
    $meja = Meja::where('id', $request->meja_id)
                ->where('status', 'tersedia')
                ->first();

    if (!$meja) {
        return redirect()->back()->withInput()->with('error', 'Meja tidak tersedia.');
    }

    Reservasi::create([
        'user_id' => auth()->id(),
        'meja_id' => $request->meja_id,
        'area' => $request->area,
        // field lain jika perlu
    ]);

    // update status meja menjadi terisi agar tidak double booking
    $meja->update([
        'status' => 'terisi',
        'current_reservasi_id' => auth()->id(),
    ]);

    return redirect()->route('pelayan.reservasi')->with('success', 'Reservasi berhasil dibuat!');
}
     public function getMejaByArea($area)
{
    try {
        $mejas = Meja::where('area', $area)
                   ->where('status', 'tersedia')
                   ->get(['id', 'nomor_meja', 'kapasitas', 'area', 'status']);

        return response()->json([
            'success' => true,
            'mejas' => $mejas
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
}


    public function scanQr(Request $request)
    {
        return view('pelayan.scanqr', [
            'title' => 'Scan QR Code'
        ]);
    }

    public function prosesScanQr($kodeReservasi)
    {
        $kodeReservasi = trim($kodeReservasi);

        $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();

        if (!$reservasi) {
            return redirect()->route('pelayan.scanqr')->with('error', 'Reservasi tidak ditemukan.');
        }

        if ($reservasi->kehadiran_status === 'hadir') {
            return redirect()->route('pelayan.reservasi')->with('error', 'Kehadiran sudah dikonfirmasi sebelumnya.');
        }

        $updateData = [
            'kehadiran_status' => 'hadir',
            'waktu_kedatangan' => now(),
        ];

        if ($reservasi->status === 'dipesan') {
            $updateData['status'] = 'active_order';
        }

        $reservasi->update($updateData);

        return redirect()->route('pelayan.reservasi')->with('success', 'Kehadiran untuk reservasi #' . $reservasi->kode_reservasi . ' berhasil dikonfirmasi.');
    }

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

 public function cancelReservation($reservasi_id)
    {
        DB::beginTransaction();
        try {
            // Ambil reservasi beserta relasi 'meja' (primary meja)
            $reservasi = Reservasi::with('meja')->findOrFail($reservasi_id);

            // 1) Set status reservasi menjadi 'dibatalkan'
            $reservasi->status       = 'dibatalkan';
            $reservasi->waktu_selesai = now();
            $reservasi->save();

            // 2) Kumpulkan ID‐ID meja yang perlu dilepaskan
            $allMejaIds = [];

            // - Meja utama (kolom meja_id)
            if (!empty($reservasi->meja_id)) {
                $allMejaIds[] = $reservasi->meja_id;
            }

            // - Meja gabungan: kolom combined_tables disimpan sebagai JSON string (misal "[3,5]")
            if (!empty($reservasi->combined_tables)) {
                // Coba decode JSON; jika gagal, abaikan
                $decoded = @json_decode($reservasi->combined_tables, true);
                if (is_array($decoded)) {
                    // Hanya masukkan ID yang valid (integer)
                    foreach ($decoded as $mId) {
                        // Pastikan bukan null dan bukan meja utama yang sudah tercantum
                        if (is_numeric($mId) && !in_array($mId, $allMejaIds)) {
                            $allMejaIds[] = (int) $mId;
                        }
                    }
                }
            }

            // 3) Lepaskan setiap meja yang ditemukan
            foreach ($allMejaIds as $mejaId) {
                $meja = Meja::find($mejaId);
                if ($meja) {
                    // Hanya set ke 'tersedia' kalau current_reservasi_id sama dengan $reservasi_id
                    if ($meja->current_reservasi_id == $reservasi_id) {
                        $meja->status               = 'tersedia';
                        $meja->current_reservasi_id = null;
                        $meja->save();
                    }
                }
            }

            DB::commit();

            // Jika request AJAX (expectsJson), kembalikan JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reservasi dibatalkan dan semua meja kembali tersedia.'
                ], 200);
            }

            // Jika bukan AJAX, redirect balik dengan flash message
            return redirect()->back()->with('success', 'Reservasi berhasil dibatalkan, meja sudah tersedia kembali.');
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling reservation: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membatalkan reservasi: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal membatalkan reservasi. Silakan coba lagi.');
        }
    }

    public function destroy($id)
{
    $reservasi = Reservasi::findOrFail($id);
    $reservasi->delete(); // Soft delete
    return redirect()->back()->with('success', 'Reservasi berhasil dihapus.');
}

public function settlePayment(Request $request, $id)
{
    // Pastikan hanya AJAX
    if (! $request->ajax()) {
        abort(403);
    }

    $reservasi = Reservasi::findOrFail($id);
    // Jika sudah lunas, langsung sukses
    if ($reservasi->status === 'paid' || $reservasi->status === 'selesai') {
        return response()->json(['success' => true]);
    }

    // Tandai lunas
    $reservasi->payment_status = 'paid';
    $reservasi->sisa_tagihan_reservasi = 0;
    $reservasi->status           = 'paid';
    $reservasi->waktu_selesai    = now();
    $reservasi->save();

    return response()->json(['success' => true]);
}




}
