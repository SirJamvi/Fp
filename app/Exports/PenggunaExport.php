<?php

namespace App\Exports;

use App\Models\Pengguna;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PenggunaExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        // Base query untuk pelanggan
        $query = Pengguna::where('peran', 'pelanggan');
        $today = Carbon::today();
        $filter = $this->request->input('filter', 'all');

        if ($filter === 'week') {
            $query->whereBetween('created_at', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek(),
            ]);
        } elseif ($filter === 'month') {
            $query->whereMonth('created_at', $today->month)
                  ->whereYear('created_at', $today->year);
        } elseif ($filter === 'year') {
            $query->whereYear('created_at', $today->year);
        }

        if ($search = $this->request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('nomor_hp', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        // View untuk Excel (buat di resources/views/export/pelanggan-excel.blade.php)
        return view('export.pelanggan-excel', compact('customers'));
    }
}
