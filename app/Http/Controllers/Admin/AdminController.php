<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $title = 'Dashboard Admin';

        // Default filter
        $filter = $request->get('filter', 'month');

        // Define date ranges
        $ranges = [
            'today' => [Carbon::today(), Carbon::now()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
        ];

        // Ensure fallback to 'month'
        [$startDate, $endDate] = $ranges[$filter] ?? $ranges['month'];

        // Stat cards
        $totalReservations = Reservasi::whereBetween('created_at', [$startDate, $endDate])->count();
        $presentReservations = Reservasi::where('status', 'selesai')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        // Best selling menu item
        $bestSellingItem = DB::table('orders')
            ->join('menus', 'orders.menu_id', '=', 'menus.id')
            ->select('menus.name', DB::raw('SUM(orders.quantity) as total'))
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total')
            ->value('menus.name');

        // Staff performance
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

        // Attendance Chart (Doughnut)
        $attendanceChart = [];
        foreach ($ranges as $key => [$start, $end]) {
            $attendanceChart[$key] = [
                Reservasi::whereBetween('created_at', [$start, $end])->count(),
                Reservasi::where('status', 'selesai')->whereBetween('created_at', [$start, $end])->count(),
                Order::whereBetween('created_at', [$start, $end])->count()
            ];
        }

        // Transaction Chart (Bar)
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

        return view('admin.dashboard', compact(
            'title',
            'filter',
            'totalReservations',
            'bestSellingItem',
            'presentReservations',
            'totalOrders',
            'staffPerformance',
            'attendanceChart',
            'transactionChart'
        ));
    }
}


