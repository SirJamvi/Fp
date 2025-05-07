<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $title = 'Dashboard Admin';

        // Statistik utama
        $totalReservations = Reservasi::count();
        $totalOrders = Order::count();
        $presentReservations = Reservasi::where('status', 'selesai')->count(); // "present" diganti "selesai"

        // Menu terlaris
        $bestSellingItem = DB::table('orders')
            ->join('menus', 'orders.menu_id', '=', 'menus.id')
            ->select('menus.name', DB::raw('SUM(orders.quantity) as total'))
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total')
            ->value('menus.name');

        // Kinerja staff (pelayan & koki)
        $staffPerformance = DB::table('pengguna')
            ->leftJoin('reservasi', 'pengguna.id', '=', 'reservasi.staff_id')
            ->leftJoin('ratings', 'pengguna.id', '=', 'ratings.staff_id')
            ->select(
                'pengguna.id',
                'pengguna.nama',
                'pengguna.peran',
                DB::raw('COUNT(DISTINCT reservasi.id) as jumlah_reservasi'),
                DB::raw('ROUND(AVG(ratings.rating), 2) as rata_rata_rating')
            )
            ->whereIn('pengguna.peran', ['pelayan', 'koki'])
            ->groupBy('pengguna.id', 'pengguna.nama', 'pengguna.peran')
            ->get();

        return view('admin.dashboard', compact(
            'title',
            'totalReservations',
            'bestSellingItem',
            'presentReservations',
            'totalOrders',
            'staffPerformance'
        ));
    }
}
