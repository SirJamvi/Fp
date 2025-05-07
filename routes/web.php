<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ReservasiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\MejaController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes Group
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Menu
    Route::resource('menu', MenuController::class); // -> admin.menu.index, etc.

    // Meja
    Route::resource('meja', MejaController::class); // -> admin.meja.index, etc.

    // Reservasi
    Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi');

    // View statis
    Route::view('/laporan', 'admin.laporan', ['title' => 'Laporan'])->name('laporan');
    Route::view('/info-cust', 'admin.info-cust', ['title' => 'Info Pelanggan'])->name('info-cust');
    Route::view('/kelola-akun', 'admin.kelola-akun', ['title' => 'Kelola Akun'])->name('kelola-akun');
});
