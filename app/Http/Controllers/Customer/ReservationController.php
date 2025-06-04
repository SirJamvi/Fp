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
        // Log untuk debugging
        Log::info('Reservation Request Data:', $request->all());

        DB::beginTransaction();

        try {
            // Ambil user yang sedang login (model Pengguna)
            $user = Auth::user();

            // Parse waktu kedatangan dengan lebih hati-hati
            try {
                $waktuKedatangan = Carbon::createFromFormat('Y-m-d H:i:s', $request->waktu_kedatangan);
            } catch (\Exception $e) {
                // Jika parsing gagal, coba parse dengan format lain
                try {
                    $waktuKedatangan = Carbon::parse($request->waktu_kedatangan);
                } catch (\Exception $e2) {
                    return response()->json([
                        'message' => 'Format waktu kedatangan tidak valid.',
                        'error' => 'Gunakan format YYYY-MM-DD HH:mm:ss'
                    ], 400);
                }
            }

            // Validasi minimal 15 menit dari sekarang
            $now = Carbon::now();
            $minTime = $now->copy()->addMinutes(15);
            
            if ($waktuKedatangan->lt($minTime)) {
                return response()->json([
                    'message' => 'Waktu kedatangan minimal 15 menit dari sekarang.',
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'min_time' => $minTime->format('Y-m-d H:i:s'),
                    'requested_time' => $waktuKedatangan->format('Y-m-d H:i:s')
                ], 400);
            }

            // Cek ketersediaan meja: kapasitas >= jumlah_tamu, status tersedia,
            // dan tidak ada reservasi lain yang overlap ±2 jam
            $availableMeja = Meja::where('kapasitas', '>=', $request->jumlah_tamu)
                                 ->where('status', 'tersedia')
                                 ->whereNotExists(function ($query) use ($waktuKedatangan) {
                                     $query->select(DB::raw(1))
                                           ->from('reservasi')
                                           ->whereRaw('reservasi.meja_id = meja.id')
                                           ->whereNotIn('reservasi.status', ['dibatalkan', 'selesai'])
                                           ->whereBetween('reservasi.waktu_kedatangan', [
                                               $waktuKedatangan->copy()->subHours(2),
                                               $waktuKedatangan->copy()->addHours(2)
                                           ])
                                           ->whereNull('reservasi.deleted_at');
                                 })
                                 ->first();

            if (! $availableMeja) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tidak ada meja yang tersedia untuk jumlah tamu ini pada waktu tersebut.',
                    'waktu_requested' => $waktuKedatangan->format('Y-m-d H:i:s'),
                    'jumlah_tamu' => $request->jumlah_tamu
                ], 400);
            }

            // Buat kode reservasi unik
            $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            }

            // Simpan data reservasi
            $reservasi = Reservasi::create([
                'user_id'                 => $user->id,
                'meja_id'                 => $availableMeja->id,
                'nama_pelanggan'          => $user->nama,
                'waktu_kedatangan'        => $waktuKedatangan->format('Y-m-d H:i:s'),
                'jumlah_tamu'             => $request->jumlah_tamu,
                'kehadiran_status'        => 'belum_dikonfirmasi',
                'status'                  => 'pending_payment',
                'source'                  => 'online',
                'kode_reservasi'          => $kodeReservasi,
                'catatan'                 => $request->catatan,
                'total_bill'              => 0,
                'sisa_tagihan_reservasi'  => 0,
            ]);

            // Update status meja menjadi 'dipesan'
            $availableMeja->update(['status' => 'dipesan']);

            DB::commit();

            Log::info('Reservation created successfully:', [
                'reservation_id' => $reservasi->id,
                'code' => $reservasi->kode_reservasi,
                'waktu_kedatangan' => $reservasi->waktu_kedatangan
            ]);

            return response()->json([
                'message'   => 'Reservasi berhasil dibuat.',
                'reservasi' => $reservasi->load('meja'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Reservation creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat reservasi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a list of the authenticated customer's reservations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Display the specified reservation.
     *
     * @param  \App\Models\Reservasi  $reservasi
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @param  \App\Models\Reservasi  $reservasi
     * @return \Illuminate\Http\JsonResponse
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
            $reservasi->update(['status' => 'dibatalkan']);

            // Kembalikan status meja menjadi 'tersedia' jika bukan combined_tables
            if ($reservasi->meja && ! $reservasi->combined_tables) {
                $reservasi->meja->update(['status' => 'tersedia']);
            }

            DB::commit();

            return response()->json([
                'message'   => 'Reservasi berhasil dibatalkan.',
                'reservasi' => $reservasi,
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservasi    $reservasi
     * @return \Illuminate\Http\JsonResponse
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