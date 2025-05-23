<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservasiService
{
    /**
     * Daftar reservasi dengan filter, search, dan paging.
     */
    public function list(Request $request)
    {
        $query = Reservasi::with(['pengguna', 'meja', 'orders', 'staffYangMembuat'])
            ->whereIn('status', [
                'confirmed', 'pending_arrival', 'active_order',
                'paid', 'pending_payment', 'selesai', 'dibatalkan'
            ]);

        // Filter berdasarkan sumber (online / dine_in)
        if ($request->has('source') && in_array($request->source, ['online', 'dine_in'])) {
            $query->where('source', $request->source);
        }

        // Pencarian teks
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_pelanggan', 'like', "%{$searchTerm}%")
                  ->orWhere('kode_reservasi', 'like', "%{$searchTerm}%")
                  ->orWhere('id', 'like', "%{$searchTerm}%")
                  ->orWhereHas('meja', function ($subq) use ($searchTerm) {
                      $subq->where('nomor_meja', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('pengguna', function ($subq) use ($searchTerm) {
                      $subq->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('staffYangMembuat', function ($subq) use ($searchTerm) {
                      $subq->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Filter berdasarkan kriteria (today, upcoming, past_week, paid, active, selesai, dibatalkan)
        if ($request->has('filter') && !empty($request->filter)) {
            switch ($request->filter) {
                case 'today':
                    $query->whereDate('waktu_kedatangan', Carbon::today());
                    break;
                case 'upcoming':
                    $query->where('waktu_kedatangan', '>=', Carbon::now());
                    break;
                case 'past_week':
                    $query->whereBetween('waktu_kedatangan', [Carbon::now()->subWeek(), Carbon::now()]);
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'active':
                    $query->whereIn('status', ['confirmed', 'pending_arrival', 'active_order', 'pending_payment']);
                    break;
                case 'selesai':
                    $query->where('status', 'selesai');
                    break;
                case 'dibatalkan':
                    $query->where('status', 'dibatalkan');
                    break;
            }
        } else {
            // Default: today/upcoming + active orders
            $query->where(function ($q) {
                $q->where('waktu_kedatangan', '>=', Carbon::today()->startOfDay())
                  ->orWhereIn('status', ['active_order', 'pending_payment']);
            });
        }

        // Penentuan urutan
        if ($request->has('filter') && in_array($request->filter, ['today', 'upcoming'])) {
            $query->orderBy('waktu_kedatangan', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $reservasi = $query->paginate(10);

        return view('pelayan.reservasi', [
            'title'        => 'Daftar Reservasi',
            'reservasi'    => $reservasi,
            'filter'       => $request->filter ?? null,
            'search'       => $request->search ?? null,
            'sourceFilter' => $request->source ?? null,
        ]);
    }

    /**
     * Proses hasil scan QR (confirm kehadiran).
     */
    public function handleScan($kodeReservasi)
    {
        $kodeReservasi = trim($kodeReservasi);
        $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();

        if (! $reservasi) {
            return redirect()->route('pelayan.scanqr')
                ->with('error', 'Reservasi tidak ditemukan.');
        }

        if ($reservasi->kehadiran_status === 'hadir') {
            return redirect()->route('pelayan.reservasi')
                ->with('error', 'Kehadiran sudah dikonfirmasi sebelumnya.');
        }

        $dataUpdate = [
            'kehadiran_status' => 'hadir',
            'waktu_kedatangan' => now(),
        ];

        if ($reservasi->status === 'dipesan') {
            $dataUpdate['status'] = 'active_order';
        }

        $reservasi->update($dataUpdate);

        return redirect()->route('pelayan.reservasi')
            ->with('success', "Kehadiran untuk reservasi #{$reservasi->kode_reservasi} berhasil dikonfirmasi.");
    }

    /**
     * Selesaikan reservasi (meja kembali tersedia).
     */
    public function complete($reservasi_id)
    {
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::with('meja')->findOrFail($reservasi_id);

            if ($reservasi->status !== 'paid') {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Reservasi hanya bisa diselesaikan jika sudah lunas.');
            }

            $reservasi->status = 'selesai';
            $reservasi->waktu_selesai = $reservasi->waktu_selesai ?? now();
            $reservasi->save();

            $combinedTables = $reservasi->combined_tables
                ? json_decode($reservasi->combined_tables, true)
                : [$reservasi->meja_id];

            foreach ($combinedTables as $mejaId) {
                $meja = Meja::find($mejaId);
                if ($meja) {
                    $meja->status = 'tersedia';
                    $meja->current_reservasi_id = null;
                    $meja->save();
                }
            }

            DB::commit();
            return redirect()->back()
                ->with('success', 'Reservasi berhasil diselesaikan. Meja dikembalikan ke status tersedia.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error completing reservation: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menyelesaikan reservasi.');
        }
    }

    /**
     * Batalkan reservasi (meja kembali tersedia).
     */
    public function cancel($reservasi_id)
    {
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::with('meja')->findOrFail($reservasi_id);

            if (in_array($reservasi->status, ['paid', 'selesai'])) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Reservasi yang sudah lunas atau selesai tidak bisa dibatalkan.');
            }

            $reservasi->status = 'dibatalkan';
            $reservasi->waktu_selesai = now();
            $reservasi->save();

            $meja = $reservasi->meja;
            if ($meja && $meja->status === 'terisi'
                && $meja->current_reservasi_id === $reservasi->id) {
                $meja->status = 'tersedia';
                $meja->current_reservasi_id = null;
                $meja->save();
            } elseif ($meja && $meja->status === 'terisi' && is_null($meja->current_reservasi_id)) {
                $meja->status = 'tersedia';
                $meja->save();
            }

            DB::commit();
            return redirect()->back()
                ->with('success', 'Reservasi berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error cancelling reservation: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membatalkan reservasi.');
        }
    }
}
