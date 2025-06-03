<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API endpoint berhasil diakses',
            'data' => [
                'server_time' => now(),
                'app_name' => config('app.name')
            ]
        ]);
    }
}