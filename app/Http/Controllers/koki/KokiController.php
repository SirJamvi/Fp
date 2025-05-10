<?php

namespace App\Http\Controllers\Koki;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KokiController extends Controller
{
    public function __construct()
    {
        // Laravel 10+ doesn't use middleware method in controllers anymore
        // Middleware is applied in routes instead
    }

    public function index()
    {
        return view('koki.dashboard', [
            'title' => 'Dashboard Koki'
        ]);
    }

    public function stokBahan()
{
    return view('koki.stok-bahan', [
        'title' => 'Stok Bahan'
    ]);
}
}