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

    public function toggleStatus($id)
    {
        $meja = Meja::findOrFail($id);
        $meja->status = $meja->status === 'nonaktif' ? 'tersedia' : 'nonaktif';
        $meja->save();

        return redirect()->route('pelayan.meja')->with('success', 'Status meja diperbarui.');
    }
}
