<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ReservasiController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\KelolaAkunController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Koki\KokiController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ADMIN ROUTES
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('menu', MenuController::class);
    Route::resource('meja', MejaController::class);
    Route::resource('kelola-akun', KelolaAkunController::class);
    Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi');
    Route::view('/laporan', 'admin.laporan', ['title' => 'Laporan'])->name('laporan');
    Route::view('/info-cust', 'admin.info-cust', ['title' => 'Info Pelanggan'])->name('info-cust');
});

// PELAYAN ROUTES
Route::prefix('pelayan')->name('pelayan.')->middleware(['auth', 'pelayan'])->group(function () {
    Route::get('/', [PelayanController::class, 'dashboard']);
    Route::get('/dashboard', [PelayanController::class, 'dashboard'])->name('dashboard');
    Route::get('/pesanan', [PelayanController::class, 'pesanan'])->name('pesanan');
    Route::get('/meja', [PelayanController::class, 'meja'])->name('meja');
});

// KOKI ROUTES
Route::prefix('koki')->name('koki.')->middleware(['auth', 'koki'])->group(function () {
    Route::get('/', [KokiController::class, 'dashboard']);
    Route::get('/dashboard', [KokiController::class, 'dashboard'])->name('dashboard');
    Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
    Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');
});
