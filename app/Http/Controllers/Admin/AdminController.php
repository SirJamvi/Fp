<?php

// app/Http/Controllers/Admin/AdminController.php
// app/Http/Controllers/Admin/AdminController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Pengguna;
use App\Models\Reservasi;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
{
    $title = 'Dashboard Admin'; // <-- TAMBAHKAN INI
    
    $totalPelanggan = Pengguna::where('peran', 'pelanggan')->count();
    $totalMenu = Menu::count();
    $totalMeja = Meja::count();
    $reservasiHariIni = Reservasi::whereDate('waktu_kedatangan', today())->count();

    return view('admin.dashboard', compact(
        'title', // <-- MASUKKAN KE DALAM COMPACT()
        'totalPelanggan',
        'totalMenu',
        'totalMeja',
        'reservasiHariIni'
    ));
}
}