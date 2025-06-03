<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\RatingRequest; // Akan kita buat nanti
use App\Models\Rating; // Menggunakan model Rating yang ada
use App\Models\Reservasi; // Mungkin perlu untuk mengaitkan rating dengan reservasi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Store a new rating from the authenticated customer.
     *
     * @param  \App\Http\Requests\Customer\RatingRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RatingRequest $request)
    {
        $user = $request->user();

        // Opsional: Cek apakah reservasi sudah selesai atau transaksi sudah lunas
        // Contoh: $reservasi = Reservasi::where('id', $request->reservasi_id)->where('user_id', $user->id)->first();
        // if (!$reservasi || $reservasi->status !== 'selesai' && $reservasi->payment_status !== 'paid') {
        //     return response()->json(['message' => 'Anda hanya bisa memberi rating pada reservasi yang sudah selesai atau lunas.'], 403);
        // }

        $rating = Rating::create([
            'user_id' => $user->id,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            // 'reservasi_id' => $request->reservasi_id, // Jika ingin mengaitkan dengan reservasi tertentu
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
        $ratings = $request->user()
                           ->ratings() // Asumsi ada relasi hasMany di model Pengguna
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        return response()->json([
            'message' => 'Daftar rating Anda berhasil diambil.',
            'ratings' => $ratings,
        ], 200);
    }
}