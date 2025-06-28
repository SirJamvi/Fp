<?php

namespace App\Exports;

use App\Models\Reservasi;
use App\Models\Meja;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ReservasiExport implements FromView, WithStyles, WithColumnWidths, WithEvents
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
                $q->where('kode_reservasi', 'like', '%' . $search . '%')
                  ->orWhere('nama_pelanggan', 'like', '%' . $search . '%')
                  ->orWhereHas('pengguna', function ($userQuery) use ($search) {
                      $userQuery->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        $reservasis = $query->latest('waktu_kedatangan')->get()->map(function($reservasi) {
            $reservasi->meja_display = '-';
            
            // 1. Handle single table
            if ($reservasi->meja_id && $meja = Meja::find($reservasi->meja_id)) {
                $reservasi->meja_display = $meja->nomor_meja ?? 'Meja ' . $meja->id;
            } 
            // 2. Handle combined tables
            elseif ($reservasi->combined_tables) {
                $decoded = json_decode($reservasi->combined_tables, true);
                
                // Handle kemungkinan double encoding
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                
                if (is_array($decoded)) {
                    $mejaNumbers = [];
                    
                    foreach ($decoded as $mejaId) {
                        if ($meja = Meja::find($mejaId)) {
                            $mejaNumbers[] = $meja->nomor_meja ?? 'Meja ' . $meja->id;
                        }
                    }
                    
                    $reservasi->meja_display = implode(', ', $mejaNumbers);
                }
            }
            
            return $reservasi;
        });

        return view('export.reservasi-excel', [
            'reservasis' => $reservasis,
            'title' => 'Laporan Reservasi',
            'printed_at' => now()->format('d F Y H:i')
        ]);
    }
    
    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A3:G3')->applyFromArray([
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
            $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($fillColor);
        }

        // Set border for all cells
        $sheet->getStyle('A3:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align for specific columns
        $centerColumns = ['A', 'B', 'E', 'F', 'G'];
        foreach ($centerColumns as $col) {
            $sheet->getStyle($col . '3:' . $col . $lastRow)->getAlignment()->setHorizontal('center');
        }
        
        // Title style
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('1F497D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Subtitle style
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        
        // Auto size for name and note columns
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 18,  // Kode Reservasi
            'E' => 20,  // Waktu Kedatangan
            'F' => 15,  // Nomor Meja
            'G' => 15,  // Status
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set title
                $event->sheet->setCellValue('A1', 'LAPORAN RESERVASI');
                
                // Set subtitle
                $event->sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d F Y H:i'));
                
                // Merge cells for title and subtitle
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->mergeCells('A2:G2');
                
                // Set row height for header
                $event->sheet->getRowDimension(3)->setRowHeight(25);
                
                // Set print area
                $event->sheet->getPageSetup()->setPrintArea('A1:G' . $event->sheet->getHighestRow());
            },
        ];
    }
}