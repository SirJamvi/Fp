<?php

namespace App\Http\Controllers\Koki;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KokiController extends Controller
{
    public function dashboard()
    {
        return view('koki.dashboard', [
            'title' => 'Dashboard Koki'
        ]);
    }
    
    public function daftarPesanan()
    {
        return view('koki.daftar-pesanan', [
            'title' => 'Daftar Pesanan'
        ]);
    }
    
    public function stokBahan()
    {
        return view('koki.stok-bahan', [
            'title' => 'Stok Bahan'
        ]);
    }
}