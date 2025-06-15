<?php

namespace App\Http\Controllers\Customer;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Services\PaymentService;
use App\Http\Requests\Customer\ReservationRequest;
use App\Http\Controllers\Customer\NotificationController;

class ReservationController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(ReservationRequest $request)
{
    Log::info('Reservation Request Data:', $request->all());
    DB::beginTransaction();

    try {
        $user = Auth::user();

        // 1) Parse & validasi waktu kedatangan
        try {
            $waktuKedatangan = Carbon::createFromFormat('Y-m-d H:i:s', $request->waktu_kedatangan);
        } catch (\Exception $e) {
            $waktuKedatangan = Carbon::parse($request->waktu_kedatangan);
        }
        $minTime = Carbon::now()->addMinutes(15);
        if ($waktuKedatangan->lt($minTime)) {
            return response()->json([
                'message'  => 'Waktu kedatangan minimal 15 menit dari sekarang.',
                'min_time' => $minTime->toDateTimeString(),
            ], 400);
        }

        // 2) Cek ketersediaan meja
        $mejaIds = is_array($request->id_meja) ? $request->id_meja : [];
        if ($mejaIds) {
            $available = Meja::whereIn('id', $mejaIds)
                             ->where('status','tersedia')
                             ->pluck('id')->toArray();
            if (count($available) !== count($mejaIds)) {
                DB::rollBack();
                return response()->json([
                    'message'    => 'Beberapa meja tidak tersedia.',
                    'requested'  => $mejaIds,
                    'available'  => $available,
                ], 400);
            }
        }

        // 3) Generate kode unik
        do {
            $kodeReservasi = 'RES-'.Str::upper(Str::random(6));
        } while (Reservasi::where('kode_reservasi',$kodeReservasi)->exists());

        // 4) Hitung total tagihan dari harga di DB, bukan dari client
        $totalTagihan = 0;
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $it) {
                // Ambil harga sebenarnya
                $menu = \App\Models\Menu::find($it['menu_id']);
                $price = $menu ? ($menu->price ?? $menu->final_price ?? 0) : 0;
                $qty   = intval($it['quantity']);
                $totalTagihan += $price * $qty;
            }
        }

        // 5) Hitung DP 50% & sisa
        $dpBayar     = round(0.5 * $totalTagihan);
        $sisaTagihan = $totalTagihan - $dpBayar;

        // 6) Simpan reservasi
        $reservasi = Reservasi::create([
            'user_id'                => $user->id,
            'waktu_kedatangan'       => $waktuKedatangan->toDateTimeString(),
            'jumlah_tamu'            => $request->jumlah_tamu,
            'nama_pelanggan'         => $user->nama,
            'kehadiran_status'       => 'belum_dikonfirmasi',
            'status'                 => 'pending_payment',
            'payment_status'         => 'partial',
            'payment_method'         => 'qris',
            'source'                 => 'online',
            'kode_reservasi'         => $kodeReservasi,
            'catatan'                => $request->catatan,
            'total_bill'             => $totalTagihan,
            'dp_terbayar'            => $dpBayar,
            'sisa_tagihan_reservasi' => $sisaTagihan,
        ]);

        // 7) Attach meja jika ada
        if ($mejaIds) {
            $reservasi->meja()->attach($mejaIds);
            Meja::whereIn('id',$mejaIds)->update(['status'=>'dipesan']);
        }

        // 8) Notifikasi
        NotificationController::createReservationReminders($reservasi);

        DB::commit();

        Log::info('Reservation created:', [
            'id'           => $reservasi->id,
            'total_bill'   => $totalTagihan,
            'dp_terbayar'  => $dpBayar,
            'sisa_tagihan' => $sisaTagihan,
        ]);

        $reservasi->load('meja');
        return response()->json([
            'message'   => 'Reservasi berhasil dibuat.',
            'reservasi' => $reservasi,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Reservation creation failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Gagal membuat reservasi.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    
    public function index(Request $request)
    {
        $user = Auth::user();

        $reservations = Reservasi::where('user_id', $user->id)
                                 ->with('meja')
                                 ->orderBy('waktu_kedatangan', 'desc')
                                 ->paginate(10);

        return response()->json([
            'message'      => 'Daftar reservasi berhasil diambil.',
            'reservations' => $reservations,
        ], 200);
    }

    public function show(Reservasi $reservasi)
    {
        $userId = Auth::id();
        if ($reservasi->user_id !== $userId) {
            return response()->json([
                'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        $reservasi->load('meja');

        return response()->json([
            'message'   => 'Detail reservasi berhasil diambil.',
            'reservasi' => $reservasi,
        ], 200);
    }

    public function cancel(Reservasi $reservasi)
    {
        $userId = Auth::id();
        if ($reservasi->user_id !== $userId) {
            return response()->json([
                'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        if (! in_array($reservasi->status, ['pending_payment', 'confirmed'])) {
            return response()->json([
                'message' => 'Reservasi tidak dapat dibatalkan pada status ini.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $mejaIds = $reservasi->meja()->pluck('meja_id')->toArray();

            $reservasi->meja()->detach();

            Meja::whereIn('id', $mejaIds)->update(['status' => 'tersedia']);

            $reservasi->update(['status' => 'dibatalkan']);

            DB::commit();

            return response()->json([
                'message'   => 'Reservasi berhasil dibatalkan.',
                'reservasi' => $reservasi->load('meja'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membatalkan reservasi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function processPayment(Request $request, Reservasi $reservasi)
    {
        $userId = Auth::id();
        if ($reservasi->user_id !== $userId || $reservasi->status === 'paid') {
            return response()->json([
                'message' => 'Akses ditolak atau reservasi sudah lunas.'
            ], 403);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'amount_paid'    => 'nullable|numeric|min:0',
        ]);

        $result = $this->paymentService->processPayment($request, $reservasi->id);

        return response()->json(
            $result,
            $result['success'] ? 200 : 400
        );
    }

    public function getBookedTimes(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        $date = $request->input('date');

        $bookedTimes = Reservasi::whereDate('waktu_kedatangan', $date)
            ->whereNotIn('status', ['dibatalkan', 'selesai'])
            ->get()
            ->map(function ($reservation) {
                // Pastikan format waktu konsisten 'HH:MM'
                return Carbon::parse($reservation->waktu_kedatangan)->format('H:i');
            })
            ->toArray();
            
        return response()->json([
            'booked_times' => $bookedTimes
        ]);
    }

public function autoCancel($reservasiId)
{
    DB::beginTransaction();
    try {
        Log::info("Memanggil autoCancel untuk ID: " . $reservasiId);

        $reservasi = \App\Models\Reservasi::with('meja')->where('id', $reservasiId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($reservasi->status !== 'pending_payment') {
            return response()->json([
                'message' => 'Reservasi tidak bisa dibatalkan karena sudah diproses atau dibayar.'
            ], 400);
        }

        // Update status reservasi
        $reservasi->update([
            'status' => 'dibatalkan',
            'kehadiran_status' => 'tidak_hadir',
            'payment_status' => 'dibatalkan',
            'waktu_selesai' => now(),
        ]);

        $mejaYangDilepas = [];

        // Update semua meja terkait
        foreach ($reservasi->meja as $meja) {
            $meja->update([
                'status' => 'tersedia',
                'is_terisi' => 0,
                'current_reservasi_id' => null,
            ]);
            $mejaYangDilepas[] = $meja->nomor ?? 'Meja #' . $meja->id;
        }

        // Detach relasi meja dari reservasi
        $reservasi->meja()->detach();

        DB::commit();

        return response()->json([
            'message' => 'Reservasi berhasil dibatalkan.',
            'meja_dilepas' => $mejaYangDilepas,
            'status_reservasi' => $reservasi->status,
            'waktu_dibatalkan' => now()->toDateTimeString()
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("Gagal membatalkan reservasi: " . $e->getMessage());
        return response()->json([
            'message' => 'Terjadi kesalahan saat membatalkan reservasi. Silakan coba lagi.',
            'error' => $e->getMessage()
        ], 500);
    }
}



private function returnResponse($message, $status = 200)
{
    if (request()->wantsJson()) {
        return response()->json(['message' => $message], $status);
    }

    return redirect()->back()->with('status', $message);
}



}
