<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReservasiExport;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

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

        // Filter pencarian berdasarkan nama pelanggan atau kode reservasi
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_reservasi', 'like', '%' . $search . '%')
                  ->orWhere('nama_pelanggan', 'like', '%' . $search . '%') // langsung di tabel reservasi
                  ->orWhereHas('pengguna', function ($userQuery) use ($search) {
                      $userQuery->where('nama', 'like', '%' . $search . '%'); // dari relasi pengguna
                  });
            });
        }

        $reservasis = $query->latest('waktu_kedatangan')->paginate(10);

        return view('admin.reservasi', [
            'title' => 'Data Reservasi',
            'reservasis' => $reservasis,
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ReservasiExport($request), 'reservasi.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $reservasis = $this->getFilteredReservasi($request);
        $pdf = Pdf::loadView('export.reservasi-pdf', compact('reservasis'));
        return $pdf->download('reservasi.pdf');
    }

    public function exportWord(Request $request)
    {
        $reservasis = $this->getFilteredReservasi($request);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Laporan Reservasi', ['bold' => true, 'size' => 16]);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50,
        ]);

        // Header
        $table->addRow();
        $headers = ['No', 'Kode', 'Nama', 'Catatan', 'Waktu Kedatangan', 'Nomor Meja', 'Status'];
        foreach ($headers as $header) {
            $table->addCell(1500, ['bgColor' => 'd9d9d9'])->addText($header, ['bold' => true]);
        }

        // Rows
        foreach ($reservasis as $index => $r) {
            $table->addRow();
            $table->addCell(500)->addText($index + 1);
            $table->addCell(1500)->addText($r->kode_reservasi);
            $table->addCell(2000)->addText($r->nama_pelanggan ?? ($r->pengguna->nama ?? '-'));
            $table->addCell(2000)->addText($r->catatan ?? '-');
            $table->addCell(2000)->addText(\Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i'));
            $table->addCell(1000)->addText(optional($r->meja)->nomor_meja ?? '-');
            $table->addCell(1000)->addText(ucfirst($r->status));
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $tempFile = tempnam(sys_get_temp_dir(), 'reservasi');
        $writer->save($tempFile);

        return response()->download($tempFile, 'reservasi.docx')->deleteFileAfterSend(true);
    }

    private function getFilteredReservasi(Request $request)
    {
        $query = Reservasi::with(['pengguna', 'meja']);
        $today = Carbon::today();

        // Filter waktu
        $filter = $request->input('filter', 'all');
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

        // Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_reservasi', 'like', '%' . $search . '%')
                  ->orWhere('nama_pelanggan', 'like', '%' . $search . '%')
                  ->orWhereHas('pengguna', function ($userQuery) use ($search) {
                      $userQuery->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query->latest('waktu_kedatangan')->get();
    }
}
