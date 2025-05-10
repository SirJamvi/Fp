<?php

namespace App\Http\Controllers\Admin;

use App\Models\Meja;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MejaController extends Controller
{
    public function index()
    {
        $meja = Meja::all();
        return view('admin.meja.index', compact('meja'));
    }

    public function create()
    {
        return view('admin.meja.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nomor_meja' => 'required|unique:meja',
            'kapasitas' => 'required|integer',
            'area' => 'required|string', // Validasi untuk field area
        ]);

        // Simpan data meja ke database
        Meja::create([
            'nomor_meja' => $request->nomor_meja,
            'kapasitas' => $request->kapasitas,
            'area' => $request->area,
        ]);

        return redirect()->route('admin.meja.index')->with('success', 'Table successfully added.');
    }

    public function edit($id)
    {
        $meja = Meja::findOrFail($id);
        return view('admin.meja.edit', compact('meja'));
    }

    public function update(Request $request, $id)
    {
        // Validasi untuk update
        $request->validate([
            'nomor_meja' => 'required|unique:meja,nomor_meja,' . $id,
            'kapasitas' => 'required|integer',
            'area' => 'required|string', // Validasi untuk field area
        ]);

        // Update data meja
        $meja = Meja::findOrFail($id);
        $meja->update([
            'nomor_meja' => $request->nomor_meja,
            'kapasitas' => $request->kapasitas,
            'area' => $request->area,
        ]);

        return redirect()->route('admin.meja.index')->with('success', 'Table successfully updated.');
    }

    public function destroy($id)
    {
        $meja = Meja::findOrFail($id);
        $meja->delete();

        return redirect()->route('admin.meja.index')->with('success', 'Table successfully deleted.');
    }
}
