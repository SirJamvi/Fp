<?php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;

class PelayanMejaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $meja = Meja::when($search, function ($query, $search) {
                return $query->where('nomor_meja', 'like', "%$search%")
                             ->orWhere('area', 'like', "%$search%")
                             ->orWhere('kapasitas', 'like', "%$search%");
            })
            ->orderBy('nomor_meja')
            ->get();

        return view('pelayan.meja', compact('meja', 'search'));
    }

    public function setTersedia($id)
    {
        $meja = Meja::findOrFail($id);
        
        // Kalau statusnya terisi, ubah ke tersedia (kosongkan meja)
        if ($meja->status === 'terisi') {
            $meja->status = 'tersedia';
            $meja->save();

            return redirect()->route('pelayan.meja')->with('success', 'Status meja berhasil diubah menjadi tersedia.');
        }

        return redirect()->route('pelayan.meja')->with('error', 'Status meja tidak bisa diubah.');
    }

    public function getMejaByArea(Request $request)
{
    $area = $request->query('area'); // Ubah dari input() ke query()
    $mejas = Meja::where('area', $area)
               ->where('status', 'tersedia')
               ->get(['id', 'nomor_meja', 'kapasitas']);

    return response()->json($mejas);
}


    public function toggle($id)
    {
        $meja = Meja::findOrFail($id);
        $meja->status = $meja->status === 'nonaktif' ? 'tersedia' : 'nonaktif';
        $meja->save();

        return redirect()->route('pelayan.meja')->with('success', 'Status meja diperbarui.');
    }
}
