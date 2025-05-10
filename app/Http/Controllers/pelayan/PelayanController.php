<?php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

class PelayanController extends Controller
{
    /**
     * Tampilkan daftar menu untuk pelayan
     */
    public function index()
    {
        // Ambil semua menu dari database
        $menus = Menu::all();

        return view('pelayan.dashboard', [
            'title' => 'Dashboard Pelayan',
            'menus' => $menus
        ]);
    }

    /**
     * Tampilkan halaman daftar pesanan
     */
    public function pesanan()
    {
        return view('pelayan.pesanan', [
            'title' => 'Daftar Pesanan'
        ]);
    }

    /**
     * Tampilkan halaman daftar meja
     */
    public function meja()
    {
        return view('pelayan.meja', [
            'title' => 'Daftar Meja'
        ]);
    }
}
