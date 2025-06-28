<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PenggunaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Language;

class InfoCustController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengguna::where('peran', 'pelanggan');
        $today = Carbon::today();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search');

        // Filter periode
        if ($filter === 'week') {
            $query->whereBetween('created_at', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek()
            ]);
        } elseif ($filter === 'month') {
            $query->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year);
        } elseif ($filter === 'year') {
            $query->whereYear('created_at', $today->year);
        }

        // Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_hp', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.info-cust', [
            'title'      => 'Info Customer',
            'customers'  => $customers,
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PenggunaExport($request), 'pelanggan.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $customers = $this->getFilteredPengguna($request);
        $pdf = Pdf::loadView('export.pelanggan-pdf', compact('customers'));
        return $pdf->download('pelanggan.pdf');
    }

   public function exportWord(Request $request)
{
    $customers = $this->getFilteredPengguna($request);

    $phpWord = new PhpWord();
    $phpWord->setDefaultFontName('Arial');
    $phpWord->setDefaultFontSize(10);

    $section = $phpWord->addSection();

    // Header dokumen
    $section->addText('LAPORAN PELANGGAN', [
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
    $section->addTextBreak(1);

    // Buat tabel dengan styling profesional
    $tableStyle = [
        'borderSize' => 6,
        'borderColor' => '999999',
        'alignment' => 'center',
        'cellMargin' => 50
    ];
    
    $headerStyle = ['bgColor' => '1F497D'];
    $headerFontStyle = ['bold' => true, 'color' => 'FFFFFF'];
    $cellCenterStyle = ['alignment' => 'center'];
    $cellLeftStyle = ['alignment' => 'left'];

    $table = $section->addTable($tableStyle);

    // Header tabel
    $table->addRow(400);
    $headers = ['No', 'Nama', 'Email', 'Nomor HP', 'Tanggal Daftar'];
    $cellWidths = [800, 3000, 3500, 2000, 2000]; // Lebar sel disesuaikan
    
    foreach ($headers as $i => $header) {
        $table->addCell($cellWidths[$i], $headerStyle)
              ->addText($header, $headerFontStyle, $cellCenterStyle);
    }

    // Isi tabel dengan row bergantian warna
    foreach ($customers as $index => $cust) {
        $rowColor = ($index % 2 == 0) ? ['bgColor' => 'E7E6E6'] : ['bgColor' => 'FFFFFF'];
        
        $table->addRow();
        $table->addCell($cellWidths[0], $rowColor)->addText($index + 1, null, $cellCenterStyle);
        $table->addCell($cellWidths[1], $rowColor)->addText($cust->nama, null, $cellLeftStyle);
        $table->addCell($cellWidths[2], $rowColor)->addText($cust->email, null, $cellLeftStyle);
        $table->addCell($cellWidths[3], $rowColor)->addText($cust->nomor_hp, null, $cellCenterStyle);
        $table->addCell($cellWidths[4], $rowColor)->addText($cust->created_at->format('d M Y'), null, $cellCenterStyle);
    }

    // Footer dengan nomor halaman
    $footer = $section->addFooter();
    $footer->addPreserveText('Halaman {PAGE} dari {NUMPAGES}', null, ['alignment' => 'center']);
    $footer->addText('Dokumen ini dicetak dari sistem informasi pelanggan', [
        'size' => 8, 
        'italic' => true, 
        'alignment' => 'center'
    ]);

    // Simpan file
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $tempFile = tempnam(sys_get_temp_dir(), 'pelanggan');
    $writer->save($tempFile);

    return response()->download($tempFile, 'pelanggan.docx')->deleteFileAfterSend(true);
}
    private function getFilteredPengguna(Request $request)
    {
        $query = Pengguna::where('peran', 'pelanggan');
        $today = Carbon::today();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search');

        if ($filter === 'week') {
            $query->whereBetween('created_at', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek()
            ]);
        } elseif ($filter === 'month') {
            $query->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year);
        } elseif ($filter === 'year') {
            $query->whereYear('created_at', $today->year);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_hp', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}