<?php

namespace App\Http\Controllers\Customer;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Request;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Services\PaymentService;
use App\Http\Requests\Customer\ReservationRequest;

class ReservationController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(ReservationRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $waktuKedatangan = Carbon::parse($request->waktu_kedatangan);

            $availableMeja = Meja::where('kapasitas', '>=', $request->jumlah_tamu)
                                 ->where('status', 'tersedia')
                                 ->first();

            if (!$availableMeja) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tidak ada meja yang tersedia untuk jumlah tamu ini atau pada waktu tersebut. Silakan coba waktu lain.'
                ], 400);
            }

            $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            }

            $reservasi = Reservasi::create([
                'user_id' => $user->id,
                'meja_id' => $availableMeja->id,
                'nama_pelanggan' => $user->nama,
                'waktu_kedatangan' => $waktuKedatangan,
                'jumlah_tamu' => $request->jumlah_tamu,
                'kehadiran_status' => 'belum_dikonfirmasi',
                'status' => 'pending_payment',
                'source' => 'online',
                'kode_reservasi' => $kodeReservasi,
                'catatan' => $request->catatan,
                'total_bill' => 0,
                'sisa_tagihan_reservasi' => 0,
            ]);

            $availableMeja->status = 'dipesan';
            $availableMeja->save();

            DB::commit();

            return response()->json([
                'message' => 'Reservasi berhasil dibuat. Lanjutkan ke halaman detail untuk melihat atau melakukan pra-pemesanan.',
                'reservasi' => $reservasi,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat reservasi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $reservations = $request->pengguna()
                                ->reservasis()
                                ->with(['meja', 'orders.menu'])
                                ->orderBy('waktu_kedatangan', 'desc')
                                ->paginate(10);

        return response()->json([
            'message' => 'Daftar reservasi berhasil diambil.',
            'reservations' => $reservations,
        ], 200);
    }

    public function show(Reservasi $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            return response()->json(['message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        $reservasi->load(['meja', 'orders.menu']);

        return response()->json([
            'message' => 'Detail reservasi berhasil diambil.',
            'reservasi' => $reservasi,
        ], 200);
    }

    public function cancel(Reservasi $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            return response()->json(['message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        if (!in_array($reservasi->status, ['pending_payment', 'confirmed'])) {
            return response()->json([
                'message' => 'Reservasi tidak dapat dibatalkan pada status ini.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $reservasi->status = 'dibatalkan';
            $reservasi->save();

            if ($reservasi->meja && !$reservasi->combined_tables) {
                $reservasi->meja->status = 'tersedia';
                $reservasi->meja->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Reservasi berhasil dibatalkan.',
                'reservasi' => $reservasi,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membatalkan reservasi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function processPayment(Request $request, Reservasi $reservasi)
    {
        if ($reservasi->user_id !== Auth::id() || $reservasi->payment_status === 'paid') {
            return response()->json(['message' => 'Akses ditolak atau reservasi sudah lunas.'], 403);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $result = $this->paymentService->processPayment($request, $reservasi->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }
}
