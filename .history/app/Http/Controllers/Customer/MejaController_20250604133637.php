<?php

namespace App\Http\Controllers\Customer;

use App\Models\Meja;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class TableController extends Controller
{
    /**
     * Get all tables with their current status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $tables = Meja::select([
                'id',
                'nomor_meja',
                'area',
                'kapasitas',
                'status',
                'current_reservasi_id',
                'created_at',
                'updated_at'
            ])
            ->orderBy('area')
            ->orderBy('nomor_meja')
            ->get();

            return response()->json([
                'message' => 'Tables retrieved successfully',
                'data' => $tables
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve tables',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available tables for a specific date/time and guest count
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTables(Request $request)
    {
        $request->validate([
            'waktu_kedatangan' => 'nullable|date',
            'jumlah_tamu' => 'nullable|integer|min:1'
        ]);

        try {
            $waktuKedatangan = $request->waktu_kedatangan ? 
                Carbon::parse($request->waktu_kedatangan) : Carbon::now();
            $jumlahTamu = $request->jumlah_tamu ?? 1;

            $query = Meja::where('status', 'tersedia');

            // Filter by capacity if guest count is provided
            if ($jumlahTamu) {
                $query->where('kapasitas', '>=', $jumlahTamu);
            }

            $availableTables = $query->orderBy('area')
                                  ->orderBy('kapasitas')
                                  ->orderBy('nomor_meja')
                                  ->get();

            return response()->json([
                'message' => 'Available tables retrieved successfully',
                'data' => $availableTables,
                'filters' => [
                    'waktu_kedatangan' => $waktuKedatangan->format('Y-m-d H:i:s'),
                    'jumlah_tamu' => $jumlahTamu
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve available tables',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get table statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTableStats()
    {
        try {
            $stats = [
                'total' => Meja::count(),
                'tersedia' => Meja::where('status', 'tersedia')->count(),
                'terisi' => Meja::where('status', 'terisi')->count(),
                'dipesan' => Meja::where('status', 'dipesan')->count(),
                'nonaktif' => Meja::where('status', 'nonaktif')->count(),
                'by_area' => [
                    'indoor' => [
                        'total' => Meja::where('area', 'indoor')->count(),
                        'tersedia' => Meja::where('area', 'indoor')->where('status', 'tersedia')->count(),
                    ],
                    'outdoor' => [
                        'total' => Meja::where('area', 'outdoor')->count(),
                        'tersedia' => Meja::where('area', 'outdoor')->where('status', 'tersedia')->count(),
                    ]
                ]
            ];

            return response()->json([
                'message' => 'Table statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve table statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}