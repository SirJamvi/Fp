<?php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Reservasi;
use Carbon\Carbon;

class PelayanController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        return view('pelayan.dashboard', [
            'title' => 'Dashboard Pelayan',
            'menus' => $menus
        ]);
    }

    public function reservasi(Request $request)
    {
        $query = Reservasi::with(['pengguna', 'meja', 'orders']);

        if ($request->has('search') && $request->search) {
            $query->whereHas('pengguna', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'week':
                    $query->whereBetween('waktu_kedatangan', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('waktu_kedatangan', Carbon::now()->month);
                    break;
                case 'year':
                    $query->whereYear('waktu_kedatangan', Carbon::now()->year);
                    break;
            }
        }

        $reservasi = $query->orderBy('waktu_kedatangan', 'desc')->paginate(10);

        return view('pelayan.reservasi', [
            'title' => 'Daftar Reservasi',
            'reservasi' => $reservasi,
            'filter' => $request->filter,
            'search' => $request->search,
        ]);
    }

    public function meja()
    {
        return view('pelayan.meja', [
            'title' => 'Daftar Meja'
        ]);
    }

    public function detailReservasi($id)
    {
        $reservasi = Reservasi::with(['pengguna', 'meja'])->findOrFail($id);

        $orders = Order::with('menu')
            ->where('reservasi_id', $id)
            ->get();

        $totalHarga = $orders->sum('total_price');

        return view('pelayan.detail', [
            'title' => 'Detail Reservasi',
            'reservasi' => $reservasi,
            'orders' => $orders,
            'totalHarga' => $totalHarga
        ]);
    }
}
