<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Store a new rating from the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservasi_id' => 'required|exists:reservasi,id',
            'rating_makanan' => 'required|integer|min:1|max:5',
            'rating_pelayanan' => 'required|integer|min:1|max:5',
            'rating_aplikasi' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Cek apakah reservasi milik user yang sedang login
        $reservasi = Reservasi::where('id', $request->reservasi_id)
                             ->where('user_id', $user->id)
                             ->first();

        if (!$reservasi) {
            return response()->json([
                'message' => 'Reservasi tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        // ==================== LOGIKA YANG DIPERBARUI ====================
        // Definisikan status apa saja yang boleh diberi rating.
        // Gunakan nilai status asli dari database (bukan label dari frontend).
        // Sesuaikan array ini jika nama status di database Anda berbeda.
        $allowedStatusForRating = ['selesai', 'pending_payment', 'paid'];

        // Cek apakah status reservasi ada di dalam array yang diizinkan.
        if (!in_array($reservasi->status, $allowedStatusForRating)) {
            return response()->json([
                'message' => 'Rating hanya bisa diberikan untuk reservasi yang sudah lunas atau selesai.'
            ], 403); // HTTP 403 Forbidden
        }
        // ================================================================

        // Cek apakah sudah pernah memberi rating untuk reservasi ini
        $existingRating = Rating::where('user_id', $user->id)
                                  ->where('reservasi_id', $request->reservasi_id)
                                  ->first();

        if ($existingRating) {
            return response()->json([
                'message' => 'Anda sudah memberikan rating untuk reservasi ini'
            ], 409); // HTTP 409 Conflict
        }

        // Hitung rata-rata rating
        $averageRating = ($request->rating_makanan + $request->rating_pelayanan + $request->rating_aplikasi) / 3;

        $rating = Rating::create([
            'user_id' => $user->id,
            'reservasi_id' => $request->reservasi_id,
            'rating_makanan' => $request->rating_makanan,
            'rating_pelayanan' => $request->rating_pelayanan,
            'rating_aplikasi' => $request->rating_aplikasi,
            'rating' => round($averageRating, 1), // Rating rata-rata untuk tabel
            'komentar' => $request->komentar,
        ]);

        return response()->json([
            'message' => 'Rating berhasil disimpan. Terima kasih atas umpan baliknya!',
            'rating' => $rating,
        ], 201);
    }

    /**
     * Get a list of ratings submitted by the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $ratings = Rating::where('user_id', Auth::id())
                        ->with(['reservasi.meja', 'pengguna'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return response()->json([
            'message' => 'Daftar rating Anda berhasil diambil.',
            'ratings' => $ratings,
        ], 200);
    }

    /**
     * Check if user has already rated a specific reservation
     *
     * @param  int  $reservasiId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExistingRating($reservasiId)
    {
        $user = Auth::user();
        
        // Cek apakah reservasi milik user
        $reservasi = Reservasi::where('id', $reservasiId)
                             ->where('user_id', $user->id)
                             ->first();

        if (!$reservasi) {
            return response()->json([
                'message' => 'Reservasi tidak ditemukan'
            ], 404);
        }
        
        $rating = Rating::where('user_id', $user->id)
                       ->where('reservasi_id', $reservasiId)
                       ->first();

        // Definisikan juga di sini status mana saja yang boleh di-rate
        $allowedStatusForRating = ['selesai', 'pending_payment', 'paid'];

        return response()->json([
            'has_rating' => $rating ? true : false,
            'rating' => $rating,
            'can_rate' => in_array($reservasi->status, $allowedStatusForRating) && !$rating
        ]);
    }
}