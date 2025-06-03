<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Menu; // Menggunakan model Menu yang ada
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of available menu items for customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Menu::where('is_available', true)
                     ->orderBy('category')
                     ->orderBy('name');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $menus = $query->paginate(10); // Paginate results

        return response()->json([
            'message' => 'Daftar menu berhasil diambil.',
            'menus' => $menus,
        ], 200);
    }

    /**
     * Display the specified menu item.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Menu $menu)
    {
        if (!$menu->is_available) {
            return response()->json([
                'message' => 'Menu ini tidak tersedia saat ini.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail menu berhasil diambil.',
            'menu' => $menu,
        ], 200);
    }
}