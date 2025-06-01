<?php

namespace App\Services;

use App\Models\Reservasi;
use Carbon\Carbon;

class ReservasiService
{
    public function getDineInReservations($request)
    {
        $query = Reservasi::with(['pengguna', 'meja'])
            ->where('source', 'dine_in');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_reservasi', 'like', "%$search%")
                    ->orWhere('nama_pelanggan', 'like', "%$search%")
                    ->orWhereHas('pengguna', fn($q2) => $q2->where('nama', 'like', "%$search%"))
                    ->orWhereHas('meja', fn($q3) => $q3->where('nomor_meja', 'like', "%$search%"));
            });
        }

        if ($request->has('filter')) {
            $filter = $request->filter;
            $today = Carbon::today();

            switch ($filter) {
                case 'today':
                    $query->whereDate('waktu_kedatangan', $today);
                    break;
                case 'upcoming':
                    $query->whereDate('waktu_kedatangan', '>', $today);
                    break;
                case 'past_week':
                    $query->whereBetween('waktu_kedatangan', [now()->subDays(7), now()]);
                    break;
                case 'active':
                    $query->whereNotIn('status', ['selesai', 'dibatalkan']);
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'selesai':
                    $query->where('status', 'selesai');
                    break;
                case 'dibatalkan':
                    $query->where('status', 'dibatalkan');
                    break;
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}