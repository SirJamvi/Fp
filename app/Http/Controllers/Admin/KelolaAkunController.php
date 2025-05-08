<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class KelolaAkunController extends Controller
{
    public function index()
    {
        $users = Pengguna::all();
        return view('admin.kelola-akun.index', [
            'title' => 'Kelola Akun',
            'users' => $users
        ]);
    }

    public function create()
    {
        return view('admin.kelola-akun.create', [
            'title' => 'Tambah Akun Baru'
        ]);
    }

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

    public function edit(Pengguna $kelola_akun)
    {
        return view('admin.kelola-akun.edit', [
            'title' => 'Edit Akun',
            'user' => $kelola_akun
        ]);
    }

    public function update(Request $request, Pengguna $kelola_akun)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('pengguna')->ignore($kelola_akun->id),
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

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $kelola_akun->update($updateData);

        return redirect()->route('admin.kelola-akun.index')
            ->with('success', 'Akun berhasil diperbarui!');
    }

    public function destroy(Pengguna $kelola_akun)
    {
        if ($kelola_akun->peran === 'admin') {
            return redirect()->route('admin.kelola-akun.index')
                ->with('error', 'Akun admin tidak dapat dihapus!');
        }

        $kelola_akun->delete();

        return redirect()->route('admin.kelola-akun.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}
