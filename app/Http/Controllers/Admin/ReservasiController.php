<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservasi::with(['pengguna', 'meja']);

        // Filter waktu
        $filter = $request->input('filter', 'all');
        $today = Carbon::today();

        if ($filter === 'week') {
            $query->whereBetween('waktu_kedatangan', [
                $today->copy()->startOfWeek(), 
                $today->copy()->endOfWeek()
            ]);
        } elseif ($filter === 'month') {
            $query->whereMonth('waktu_kedatangan', $today->month)
                  ->whereYear('waktu_kedatangan', $today->year);
        } elseif ($filter === 'year') {
            $query->whereYear('waktu_kedatangan', $today->year);
        }

        // Filter search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pengguna', function ($userQuery) use ($search) {
                    $userQuery->where('nama', 'like', '%' . $search . '%');
                })->orWhere('kode_reservasi', 'like', '%' . $search . '%');
            });
        }

        $reservasis = $query->latest('waktu_kedatangan')->paginate(10);

        return view('admin.reservasi', [
            'title' => 'Data Reservasi',
            'reservasis' => $reservasis,
        ]);
    }
}
