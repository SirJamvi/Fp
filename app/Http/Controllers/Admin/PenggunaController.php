<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = Pengguna::all();
        return view('admin.kelola-akun.index', [
            'title' => 'Kelola Akun',
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.kelola-akun.create', [
            'title' => 'Tambah Akun Baru'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna',
            'nomor_hp' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'peran' => 'required|in:pelayan,koki',
        ]);

        Pengguna::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_hp' => $validated['nomor_hp'],
            'password' => Hash::make($validated['password']),
            'peran' => $validated['peran'],
        ]);

        return redirect()->route('admin.kelola-akun.index')
            ->with('success', 'Akun berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pengguna $kelolaAkun)
    {
        return view('admin.kelola-akun.edit', [
            'title' => 'Edit Akun',
            'user' => $kelolaAkun
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengguna $kelolaAkun)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('pengguna')->ignore($kelolaAkun->id),
            ],
            'nomor_hp' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'peran' => 'required|in:pelayan,koki',
        ]);

        $updateData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_hp' => $validated['nomor_hp'],
            'peran' => $validated['peran'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $kelolaAkun->update($updateData);

        return redirect()->route('admin.kelola-akun.index')
            ->with('success', 'Akun berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pengguna $kelolaAkun)
    {
        // Ensure admin accounts cannot be deleted
        if ($kelolaAkun->peran === 'admin') {
            return redirect()->route('admin.kelola-akun.index')
                ->with('error', 'Akun admin tidak dapat dihapus!');
        }

        $kelolaAkun->delete();

        return redirect()->route('admin.kelola-akun.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}