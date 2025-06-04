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

class InfoCustController extends Controller
{
    /**
     * Tampilkan halaman Info Customer (role = 'pelanggan'), dengan filter, search, paginate.
     */
    public function index(Request $request)
    {
        // Base query: hanya yang peran = 'pelanggan'
        $query = Pengguna::where('peran', 'pelanggan');

        // 1) Filter berdasarkan periode created_at (all, week, month, year)
        $filter = $request->input('filter', 'all');
        $today = Carbon::today();

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
        // filter = 'all' → tidak di‐kurangi apa pun

        // 2) Search: nama, email, atau nomor_hp
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('nomor_hp', 'like', '%' . $search . '%');
            });
        }

        // 3) Ambil hasil dengan pagination (10 per halaman)
        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.info-cust', [
            'title'      => 'Info Customer',
            'customers'  => $customers,
        ]);
    }

    /**
     * Export daftar pelanggan ke Excel.
     */
    public function exportExcel(Request $request)
    {
        // Akan mem‐pass request ke PenggunaExport supaya filter & search diterapkan
        return Excel::download(new PenggunaExport($request), 'pelanggan.xlsx');
    }

    /**
     * Export daftar pelanggan ke PDF.
     */
    public function exportPdf(Request $request)
    {
        // Ambil data yang sudah difilter & dicari
        $customers = $this->getFilteredPengguna($request);

        // Buat view khusus untuk PDF (misalnya di resources/views/export/pelanggan-pdf.blade.php)
        $pdf = Pdf::loadView('export.pelanggan-pdf', compact('customers'));
        return $pdf->download('pelanggan.pdf');
    }

    /**
     * Export daftar pelanggan ke Word (docx).
     */
    public function exportWord(Request $request)
    {
        $customers = $this->getFilteredPengguna($request);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Laporan Pelanggan', ['bold' => true, 'size' => 16]);

        // Buat tabel dengan header
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50,
        ]);

        // Header kolom
        $table->addRow();
        $headers = ['No', 'Nama', 'Email', 'Nomor HP', 'Tanggal Daftar'];
        foreach ($headers as $header) {
            $table->addCell(2000, ['bgColor' => 'd9d9d9'])->addText($header, ['bold' => true]);
        }

        // Baris data
        foreach ($customers as $idx => $cust) {
            $table->addRow();
            $table->addCell(500)->addText($idx + 1);
            $table->addCell(2000)->addText($cust->nama);
            $table->addCell(3000)->addText($cust->email);
            $table->addCell(2000)->addText($cust->nomor_hp);
            $table->addCell(2000)->addText($cust->created_at->format('d M Y'));
        }

        // Simpan ke file temporer dan download
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $tempFile = tempnam(sys_get_temp_dir(), 'pelanggan');
        $writer->save($tempFile);

        return response()->download($tempFile, 'pelanggan.docx')->deleteFileAfterSend(true);
    }

    /**
     * Helper: ambil koleksi Pengguna yang sudah difilter & dicari (tanpa paginate).
     */
    private function getFilteredPengguna(Request $request)
    {
        $query = Pengguna::where('peran', 'pelanggan');
        $today = Carbon::today();
        $filter = $request->input('filter', 'all');

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

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('nomor_hp', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
