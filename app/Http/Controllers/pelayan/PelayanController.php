<?php
// app/Http/Controllers/Pelayan/PelayanController.php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PelayanController extends Controller
{
    public function dashboard()
    {
        return view('pelayan.dashboard', [
            'title' => 'Dashboard Pelayan'
        ]);
    }
    
    public function pesanan()
    {
        return view('pelayan.pesanan', [
            'title' => 'Kelola Pesanan'
        ]);
    }
    
    public function meja()
    {
        return view('pelayan.meja', [
            'title' => 'Status Meja'
        ]);
    }
}