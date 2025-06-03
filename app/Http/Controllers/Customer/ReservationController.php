<?php

namespace App\Http\Controllers\Customer;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Request;
use App\Models\Meja; // Menggunakan model Meja yang ada
use App\Models\Reservasi; // Menggunakan model Reservasi yang ada
use App\Services\PaymentService; // Menggunakan PaymentService yang ada
use App\Http\Requests\Customer\ReservationRequest; // Akan kita buat nanti

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
        DB::beginTransaction();

        try {
            $user = $request->user();
            $waktuKedatangan = Carbon::parse($request->waktu_kedatangan);

            // Cek ketersediaan meja
            $availableMeja = Meja::where('kapasitas', '>=', $request->jumlah_tamu)
                                 ->where('status', 'tersedia')
                                 ->first();

            if (!$availableMeja) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tidak ada meja yang tersedia untuk jumlah tamu ini atau pada waktu tersebut. Silakan coba waktu lain.'
                ], 400);
            }

            // Buat kode reservasi unik
            $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            while (Reservasi::where('kode_reservasi', $kodeReservasi)->exists()) {
                $kodeReservasi = 'RES-' . strtoupper(Str::random(6));
            }

            $reservasi = Reservasi::create([
                'user_id' => $user->id,
                'meja_id' => $availableMeja->id,
                'nama_pelanggan' => $user->nama, // Ambil dari data pengguna
                'waktu_kedatangan' => $waktuKedatangan,
                'jumlah_tamu' => $request->jumlah_tamu,
                'kehadiran_status' => 'belum_dikonfirmasi',
                'status' => 'pending_payment', // Set default status for customer initiated reservation
                'source' => 'online', // Reservasi dari aplikasi pelanggan
                'kode_reservasi' => $kodeReservasi,
                'catatan' => $request->catatan,
                'total_bill' => 0, // Awalnya 0, nanti diupdate jika ada pre-order atau pembayaran
                'sisa_tagihan_reservasi' => 0,
            ]);

            // Update status meja menjadi 'reserved'
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

    /**
     * Get a list of the authenticated customer's reservations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $reservations = $request->user()
                                ->reservasis() // Asumsi ada relasi hasMany di model Pengguna
                                ->with(['meja', 'orders.menu'])
                                ->orderBy('waktu_kedatangan', 'desc')
                                ->paginate(10);

        return response()->json([
            'message' => 'Daftar reservasi berhasil diambil.',
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
        // Pastikan reservasi ini milik pengguna yang sedang login
        if ($reservasi->user_id !== Auth::id()) {
            return response()->json(['message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        $reservasi->load(['meja', 'orders.menu']); // Load relasi detail

        return response()->json([
            'message' => 'Detail reservasi berhasil diambil.',
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
        // Pastikan reservasi ini milik pengguna yang sedang login
        if ($reservasi->user_id !== Auth::id()) {
            return response()->json(['message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        // Hanya reservasi dengan status tertentu yang bisa dibatalkan
        if (!in_array($reservasi->status, ['pending_payment', 'confirmed'])) {
            return response()->json([
                'message' => 'Reservasi tidak dapat dibatalkan pada status ini.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Ubah status reservasi menjadi dibatalkan
            $reservasi->status = 'dibatalkan';
            $reservasi->save();

            // Kembalikan status meja menjadi 'tersedia' jika meja hanya 1
            if ($reservasi->meja && !$reservasi->combined_tables) {
                $reservasi->meja->status = 'tersedia';
                $reservasi->meja->save();
            }
            // Jika ada combined_tables, logika pengembalian meja mungkin lebih kompleks
            // Untuk saat ini, asumsikan reservasi customer hanya menggunakan 1 meja

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

    /**
     * Process payment for a reservation.
     * This will likely be called after a reservation is created or a pre-order is placed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservasi  $reservasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request, Reservasi $reservasi)
    {
        // Pastikan reservasi ini milik pengguna yang sedang login dan belum lunas
        if ($reservasi->user_id !== Auth::id() || $reservasi->payment_status === 'paid') {
            return response()->json(['message' => 'Akses ditolak atau reservasi sudah lunas.'], 403);
        }

        // Validasi metode pembayaran
        $request->validate([
            'payment_method' => 'required|in:cash,qris', // 'cash' hanya untuk simulasi jika pelanggan membayar di tempat
            'amount_paid' => 'nullable|numeric|min:0', // Hanya untuk metode 'cash'
        ]);

        $result = $this->paymentService->processPayment($request, $reservasi->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }
}