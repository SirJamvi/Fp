<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Koki\KokiController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('menu', MenuController::class);
    Route::resource('meja', MejaController::class);
    Route::resource('kelola-akun', PenggunaController::class);

    Route::get('/reservasi', fn () => view('admin.reservasi', ['title' => 'Reservasi']))->name('reservasi');
    Route::get('/laporan', fn () => view('admin.laporan', ['title' => 'Laporan']))->name('laporan');
    Route::get('/info-cust', fn () => view('admin.info-cust', ['title' => 'Info Pelanggan']))->name('info-cust');
});

// Pelayan Routes
Route::prefix('pelayan')->name('pelayan.')->middleware(['auth', 'pelayan'])->group(function () {
    Route::get('/dashboard', [PelayanController::class, 'index'])->name('dashboard');
    Route::get('/pesanan', [PelayanController::class, 'pesanan'])->name('pesanan');
    Route::get('/meja', [PelayanController::class, 'meja'])->name('meja');
});

// Koki Routes
Route::prefix('koki')->name('koki.')->middleware(['auth', 'koki'])->group(function () {
    Route::get('/dashboard', [KokiController::class, 'index'])->name('dashboard');
    Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
    Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');
});
