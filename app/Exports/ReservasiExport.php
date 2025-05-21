<?php

namespace App\Exports;

use App\Models\Reservasi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;

class ReservasiExport implements FromView
{
    protected $request;
    

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = Reservasi::with(['pengguna', 'meja']);
        $today = Carbon::today();
        $filter = $this->request->input('filter', 'all');

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

        if ($search = $this->request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pengguna', function ($userQuery) use ($search) {
                    $userQuery->where('nama', 'like', '%' . $search . '%');
                })->orWhere('kode_reservasi', 'like', '%' . $search . '%');
            });
        }

        $reservasis = $query->latest('waktu_kedatangan')->get();

        return view('export.reservasi-excel', compact('reservasis'));
        
    }
}
