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

class ReservationController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Store a newly created reservation for the customer.
     *
     * @param  \App\Http\Requests\Customer\ReservationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ReservationRequest $request)
    {
        // Log payload untuk debugging
        Log::info('Reservation Request Data:', $request->all());

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // 1) Parse waktu kedatangan
            try {
                $waktuKedatangan = Carbon::createFromFormat('Y-m-d H:i:s', $request->waktu_kedatangan);
            } catch (\Exception $e) {
                try {
                    $waktuKedatangan = Carbon::parse($request->waktu_kedatangan);
                } catch (\Exception $e2) {
                    return response()->json([
                        'message' => 'Format waktu kedatangan tidak valid.',
                        'error'   => 'Gunakan format YYYY-MM-DD HH:mm:ss'
                    ], 400);
                }
            }

            // 2) Validasi minimal 15 menit dari sekarang
            $now     = Carbon::now();
            $minTime = $now->copy()->addMinutes(15);
            if ($waktuKedatangan->lt($minTime)) {
                return response()->json([
                    'message'        => 'Waktu kedatangan minimal 15 menit dari sekarang.',
                    'current_time'   => $now->format('Y-m-d H:i:s'),
                    'min_time'       => $minTime->format('Y-m-d H:i:s'),
                    'requested_time' => $waktuKedatangan->format('Y-m-d H:i:s'),
                ], 400);
            }

            // 3) Ambil array id_meja
            $mejaIds = $request->input('id_meja'); // misal: [4,8,9]

            // 4) Validasi availability --- cek status tersedia
            $availableMejas = Meja::whereIn('id', $mejaIds)
                                  ->where('status', 'tersedia')
                                  ->get();

            if ($availableMejas->count() !== count($mejaIds)) {
                DB::rollBack();
                return response()->json([
                    'message'        => 'Ada satu atau lebih meja yang dipilih tidak tersedia.',
                    'requested_ids'  => $mejaIds,
                    'available_ids'  => $availableMejas->pluck('id')->toArray(),
                ], 400);
            }

            // 5) Cek overlapping ±2 jam untuk setiap meja
            $blocked = [];
            foreach ($mejaIds as $idMeja) {
                $overlap = Reservasi::whereHas('meja', function($q) use ($idMeja) {
                                     $q->where('meja.id', $idMeja);
                                 })
                                 ->whereNotIn('status', ['dibatalkan', 'selesai'])
                                 ->whereBetween('waktu_kedatangan', [
                                     $waktuKedatangan->copy()->subHours(2),
                                     $waktuKedatangan->copy()->addHours(2),
                                 ])
                                 ->whereNull('deleted_at')
                                 ->exists();

                if ($overlap) {
                    $blocked[] = $idMeja;
                }
            }

            if (!empty($blocked)) {
                DB::rollBack();
                return response()->json([
                    'message'         => 'Beberapa meja sudah terreservasi di rentang waktu tersebut.',
                    'blocked_meja_id' => $blocked,
                    'waktu_requested' => $waktuKedatangan->format('Y-m-d H:i:s'),
                ], 400);
            }

            // 6) Semua meja lolos validasi → buat kode reservasi unik
            $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            }

            // 7) Simpan data reservasi (tanpa memasukkan meja_id tunggal)
            $reservasi = Reservasi::create([
                'user_id'                => $user->id,
                'waktu_kedatangan'       => $waktuKedatangan->format('Y-m-d H:i:s'),
                'jumlah_tamu'            => $request->jumlah_tamu,
                'nama_pelanggan'         => $user->nama,
                'kehadiran_status'       => 'belum_dikonfirmasi',
                'status'                 => 'pending_payment',
                'source'                 => 'online',
                'kode_reservasi'         => $kodeReservasi,
                'catatan'                => $request->catatan,
                'total_bill'             => 0,
                'sisa_tagihan_reservasi' => 0,
            ]);

            // 8) Attach semua meja ke pivot meja_reservasi
            $reservasi->meja()->attach($mejaIds);

            // 9) Update status setiap meja menjadi 'dipesan'
            Meja::whereIn('id', $mejaIds)->update(['status' => 'dipesan']);

            DB::commit();

            Log::info('Reservation created successfully:', [
                'reservation_id'   => $reservasi->id,
                'kode_reservasi'   => $reservasi->kode_reservasi,
                'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                'meja_ids'         => $mejaIds,
            ]);

            // Muat relasi `meja` agar client tahu detailnya
            $reservasi->load('meja');

            return response()->json([
                'message'   => 'Reservasi berhasil dibuat.',
                'reservasi' => $reservasi,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reservation creation failed:', [
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat reservasi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a list of the authenticated customer's reservations.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Muat relasi meja (many-to-many)
        $reservations = Reservasi::where('user_id', $user->id)
                                 ->with('meja')
                                 ->orderBy('waktu_kedatangan', 'desc')
                                 ->paginate(10);

        return response()->json([
            'message'      => 'Daftar reservasi berhasil diambil.',
            'reservations' => $reservations,
        ], 200);
    }

    /**
     * Display the specified reservation.
     */
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

    /**
     * Cancel a customer's reservation.
     */
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
            // Hapus pivot relasi meja terlebih dahulu
            $mejaIds = $reservasi->meja()->pluck('meja_id')->toArray();

            $reservasi->meja()->detach();

            // Kembalikan status meja menjadi 'tersedia'
            Meja::whereIn('id', $mejaIds)->update(['status' => 'tersedia']);

            // Update status reservasi
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

    /**
     * Process payment for a reservation.
     */
    public function processPayment(Request $request, Reservasi $reservasi)
    {
        $userId = Auth::id();
        if ($reservasi->user_id !== $userId || $reservasi->status === 'paid') {
            return response()->json([
                'message' => 'Akses ditolak atau reservasi sudah lunas.'
            ], 403);
        }

        // Validasi metode pembayaran
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
}
