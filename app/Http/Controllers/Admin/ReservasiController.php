<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;

class ReservasiController extends Controller
{
    public function index()
    {
        $reservasis = Reservasi::with(['pengguna', 'meja'])->get();

        return view('admin.reservasi', [ // Pastikan file: resources/views/admin/reservasi.blade.php
            'title' => 'Data Reservasi',
            'reservasis' => $reservasis,
        ]);
    }
}
