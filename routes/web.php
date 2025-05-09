<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ReservasiController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\KelolaAkunController;
use App\Http\Controllers\Auth\LoginController;

// Public Route
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::resource('menu', MenuController::class);
    Route::resource('meja', MejaController::class);
    Route::resource('kelola-akun', KelolaAkunController::class); // disesuaikan di sini

    Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi');

    Route::view('/laporan', 'admin.laporan')->name('laporan');
    Route::view('/info-cust', 'admin.info-cust')->name('info-cust');
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
