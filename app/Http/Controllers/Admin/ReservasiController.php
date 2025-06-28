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

    // Pre-process meja data (sama seperti sebelumnya)
    $reservasis->transform(function ($r) {
        // ... [logika pemrosesan meja yang sama] ...
        return $r;
    });

    $phpWord = new PhpWord();
    
    // Setting font global
    $phpWord->setDefaultFontName('Arial');
    $phpWord->setDefaultFontSize(10);
    
    $section = $phpWord->addSection();
    
    // JUDUL UTAMA DI BODY SECTION (BUKAN DI HEADER)
    $section->addText('LAPORAN RESERVASI', [
        'bold' => true, 
        'size' => 16, 
        'color' => '1F497D',
        'alignment' => 'center'
    ]);
    
    $section->addText('Dicetak pada: ' . now()->format('d F Y H:i'), [
        'italic' => true, 
        'size' => 9,
        'alignment' => 'center'
    ]);
    
    $section->addTextBreak(1); // Spasi

    // Membuat tabel dengan styling
    $tableStyle = [
        'borderSize' => 6,
        'borderColor' => '999999',
        'cellMargin' => 50,
        'alignment' => 'center'
    ];
    
    $firstRowStyle = ['bgColor' => '1F497D'];
    $headerStyle = ['bold' => true, 'color' => 'FFFFFF'];
    $cellStyle = ['alignment' => 'left'];
    $centerStyle = ['alignment' => 'center'];
    
    $table = $section->addTable($tableStyle);

    // Header tabel
    $table->addRow(400, ['exactHeight' => true]);
    $headers = ['No', 'Kode', 'Nama', 'Catatan', 'Waktu Kedatangan', 'Nomor Meja', 'Status'];
    foreach ($headers as $header) {
        $table->addCell(1500, $firstRowStyle)->addText($header, $headerStyle, ['alignment' => 'center']);
    }

    // Rows dengan alternating color
    foreach ($reservasis as $index => $r) {
        $rowColor = ($index % 2 == 0) ? ['bgColor' => 'D0D0D0'] : ['bgColor' => 'FFFFFF'];
        
        $table->addRow();
        $table->addCell(500, $rowColor)->addText($index + 1, null, $centerStyle);
        $table->addCell(1500, $rowColor)->addText($r->kode_reservasi, null, $cellStyle);
        $table->addCell(2000, $rowColor)->addText($r->nama_pelanggan ?? ($r->pengguna->nama ?? '-'), null, $cellStyle);
        $table->addCell(2000, $rowColor)->addText($r->catatan ?? '-', null, $cellStyle);
        $table->addCell(2000, $rowColor)->addText(\Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i'), null, $cellStyle);
        $table->addCell(1000, $rowColor)->addText($r->meja_display, null, $centerStyle);
        
        // Styling khusus untuk status
        $statusText = ucfirst(str_replace('_', ' ', $r->status)); // Mengganti underscore dengan spasi
        $statusStyle = ['bold' => true];
        $statusColor = strtolower($r->status);
        
        if ($statusColor === 'diproses') {
            $statusStyle['color'] = '0070C0';
        } elseif ($statusColor === 'selesai' || $statusColor === 'paid') {
            $statusStyle['color'] = '00B050';
        } elseif ($statusColor === 'batal' || $statusColor === 'dibatalkan') {
            $statusStyle['color'] = 'FF0000';
        } elseif ($statusColor === 'pending_payment') {
            $statusStyle['color'] = 'FFC000';
        } else {
            $statusStyle['color'] = '000000';
        }
        
        $table->addCell(1000, $rowColor)->addText($statusText, $statusStyle, $centerStyle);
    }

    // Footer dengan nomor halaman
    $footer = $section->addFooter();
    $footer->addPreserveText('Halaman {PAGE} dari {NUMPAGES}', null, ['alignment' => 'center']);
    $footer->addText('Dokumen ini dicetak dari sistem reservasi', ['size' => 8, 'italic' => true, 'alignment' => 'center']);

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
