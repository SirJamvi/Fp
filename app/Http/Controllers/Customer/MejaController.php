<?php

namespace App\Http\Controllers\Customer;

use App\Models\Meja;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MejaController extends Controller
{
    /**
     * Get all tables grouped by area
     */
    public function index()
    {
        try {
            $meja = Meja::select('id', 'nomor_meja', 'area', 'kapasitas', 'status')
                        ->orderBy('area')
                        ->orderBy('nomor_meja')
                        ->get();

            // Group by area
            $groupedMeja = $meja->groupBy('area')->map(function ($tables, $area) {
                return $tables->map(function ($table) {
                    return [
                        'id'          => $table->nomor_meja,    // nomor meja
                        'full'        => in_array($table->status, ['terisi', 'dipesan']),
                        'seats'       => $table->kapasitas,
                        'selected'    => false,
                        'status'      => $table->status,
                        'database_id' => $table->id            // primary key
                    ];
                })->values();
            });

            return response()->json([
                'success' => true,
                'data'    => $groupedMeja
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tables',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available areas
     */
    public function getAreas()
    {
        try {
            $areas = Meja::select('area')
                         ->distinct()
                         ->orderBy('area')
                         ->pluck('area');

            return response()->json([
                'success' => true,
                'data'    => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch areas',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check table availability (optional)
     */
    public function checkAvailability(Request $request)
    {
        try {
            $request->validate([
                'table_ids' => 'required|array',
                'date'      => 'required|date',
                'time'      => 'required|string'
            ]);

            $tableIds         = $request->table_ids;
            $unavailableTables = [];

            foreach ($tableIds as $tableId) {
                $table = Meja::where('nomor_meja', $tableId)->first();

                if (!$table) {
                    $unavailableTables[] = [
                        'table_id' => $tableId,
                        'reason'   => 'Table not found'
                    ];
                    continue;
                }

                if (in_array($table->status, ['terisi', 'dipesan', 'nonaktif'])) {
                    $unavailableTables[] = [
                        'table_id' => $tableId,
                        'reason'   => 'Table is ' . $table->status
                    ];
                }
            }

            return response()->json([
                'success'           => true,
                'available'         => empty($unavailableTables),
                'unavailable_tables'=> $unavailableTables
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
