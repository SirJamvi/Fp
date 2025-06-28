<?php

namespace App\Exports;

use App\Models\Pengguna;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PenggunaExport implements FromView, WithStyles, WithColumnWidths, WithEvents
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = Pengguna::where('peran', 'pelanggan');
        $today = Carbon::today();
        $filter = $this->request->input('filter', 'all');

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

        if ($search = $this->request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('nomor_hp', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        return view('export.pelanggan-excel', [
            'customers' => $customers,
            'title' => 'Laporan Pelanggan',
            'printed_at' => now()->format('d F Y H:i')
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F497D'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Alternating row colors
        $lastRow = $sheet->getHighestRow();
        for ($row = 4; $row <= $lastRow; $row++) {
            $fillColor = $row % 2 == 0 ? 'E7E6E6' : 'FFFFFF';
            $sheet->getStyle('A' . $row . ':E' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($fillColor);
        }

        // Set border for all cells
        $sheet->getStyle('A3:E' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align for specific columns
        $sheet->getStyle('A3:A' . $lastRow)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('D3:D' . $lastRow)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E3:E' . $lastRow)->getAlignment()->setHorizontal('center');
        
        // Title style
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('1F497D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Subtitle style
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        
        // Auto size for name and email columns
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'D' => 15,  // Nomor HP
            'E' => 18,  // Tanggal Daftar
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set title
                $event->sheet->setCellValue('A1', 'LAPORAN PELANGGAN');
                
                // Set subtitle
                $event->sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d F Y H:i'));
                
                // Merge cells for title and subtitle
                $event->sheet->mergeCells('A1:E1');
                $event->sheet->mergeCells('A2:E2');
                
                // Set row height for header
                $event->sheet->getRowDimension(3)->setRowHeight(25);
                
                // Set print area
                $event->sheet->getPageSetup()->setPrintArea('A1:E' . $event->sheet->getHighestRow());
            },
        ];
    }
}