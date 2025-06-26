<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $title = 'Dashboard Admin';
        $filter = $request->get('filter', 'month');

        $ranges = [
            'today' => [Carbon::today(), Carbon::now()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
        ];

        [$startDate, $endDate] = $ranges[$filter] ?? $ranges['month'];

        $totalReservations = Reservasi::whereBetween('created_at', [$startDate, $endDate])->count();
        $presentReservations = Reservasi::where('status', 'selesai')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        $bestSellingItem = DB::table('orders')
            ->join('menus', 'orders.menu_id', '=', 'menus.id')
            ->select('menus.name', DB::raw('SUM(orders.quantity) as total'))
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total')
            ->value('menus.name');

        // ==================== QUERY YANG DIPERBAIKI ADA DI SINI ====================
        $staffPerformances = DB::table('pengguna')
            ->leftJoin('reservasi', 'pengguna.id', '=', 'reservasi.staff_id')
            ->leftJoin('ratings', 'pengguna.id', '=', 'ratings.staff_id')
            ->whereIn('pengguna.peran', ['pelayan', 'koki'])
            ->select(
                'pengguna.id',
                'pengguna.nama',
                'pengguna.peran',
                DB::raw('COUNT(DISTINCT reservasi.id) as jumlah_reservasi'),
                DB::raw('ROUND(AVG(CASE 
                    WHEN pengguna.peran = "pelayan" THEN ratings.rating_pelayanan 
                    WHEN pengguna.peran = "koki" THEN ratings.rating_makanan
                    ELSE NULL END), 2) as rata_rata_rating')
            )
            ->groupBy('pengguna.id', 'pengguna.nama', 'pengguna.peran')
            ->get();
        // ==========================================================================

        $attendanceChart = [];
        foreach ($ranges as $key => [$start, $end]) {
            $attendanceChart[$key] = [
                Reservasi::whereBetween('created_at', [$start, $end])->count(),
                Reservasi::where('status', 'selesai')->whereBetween('created_at', [$start, $end])->count(),
                Order::whereBetween('created_at', [$start, $end])->count()
            ];
        }

        $transactionChart = [];
        foreach ($ranges as $key => [$start, $end]) {
            $stats = DB::table('transactions')
                ->join('menus', 'transactions.menu_id', '=', 'menus.id')
                ->whereBetween('transactions.created_at', [$start, $end])
                ->select('menus.name as menu_name', DB::raw('SUM(transactions.total_price) as total'))
                ->groupBy('menus.name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            $transactionChart[$key] = [
                'labels' => $stats->pluck('menu_name'),
                'data' => $stats->pluck('total'),
            ];
        }

        $ratings = Rating::with('pengguna')
        ->latest()
        ->paginate(5); // ganti jumlah per halaman sesuai kebutuhan


        return view('admin.dashboard', compact(
            'title',
            'filter',
            'totalReservations',
            'bestSellingItem',
            'presentReservations',
            'totalOrders',
            'staffPerformances',
            'attendanceChart',
            'transactionChart',
            'ratings'
        ));
    }

    public function exportPdf(Request $request)
    {
        $filter = $request->query('filter', 'week');
        $ratings = Rating::query();

        if ($filter === 'today') {
            $ratings->whereDate('created_at', today());
        } elseif ($filter === 'week') {
            $ratings->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($filter === 'month') {
            $ratings->whereMonth('created_at', now()->month);
        } elseif ($filter === 'year') {
            $ratings->whereYear('created_at', now()->year);
        }

        $ratings = $ratings->get();

        // Ganti \PDF dengan facade yang benar, contoh: PDF::
        $pdf = \PDF::loadView('export.pdf', compact('ratings'));
        return $pdf->stream('ratings_report.pdf');
    }
}