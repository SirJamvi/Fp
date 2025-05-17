<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ReservasiController as AdminReservasiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Pelayan\PelayanMejaController;
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
    Route::get('/reservasi', [AdminReservasiController::class, 'index'])->name('reservasi');
    Route::get('/laporan', fn () => view('admin.laporan', ['title' => 'Laporan']))->name('laporan');
    Route::get('/info-cust', fn () => view('admin.info-cust', ['title' => 'Info Pelanggan']))->name('info-cust');
});

// Pelayan Routes
Route::prefix('pelayan')->name('pelayan.')->middleware(['auth', 'pelayan'])->group(function () {
    // Dashboard Pelayan sekarang akan menjadi halaman utama untuk order
    Route::get('/dashboard', [PelayanController::class, 'index'])->name('dashboard'); // Ini akan menampilkan form order

    // Rute untuk memproses pesanan awal (membuat reservasi dan order items) via AJAX
    // Method ini akan mengembalikan JSON dengan data reservasi yang dibuat
    Route::post('/order/store', [PelayanController::class, 'storeOrder'])->name('order.store');

    // Rute untuk memproses pembayaran (Cash/QRIS) via AJAX
    Route::post('/order/{reservasi_id}/pay', [PelayanController::class, 'processPayment'])->name('order.pay'); // <<< ROUTE BARU UNTUK PEMBAYARAN

    // Halaman untuk menampilkan ringkasan pesanan setelah berhasil dibuat DAN dibayar
    Route::get('/order/summary/{reservasi_id}', [PelayanController::class, 'showOrderSummary'])->name('order.summary');


    Route::get('/reservasi', [PelayanController::class, 'reservasi'])->name('reservasi');
    Route::get('/reservasi/{id}/detail', [PelayanController::class, 'detailReservasi'])->name('reservasi.detail');

    Route::get('/meja', [PelayanMejaController::class, 'index'])->name('meja');
    Route::post('/meja/{id}/toggle', [PelayanMejaController::class, 'toggleStatus'])->name('meja.toggle');
});

// Koki Routes
Route::prefix('koki')->name('koki.')->middleware(['auth', 'koki'])->group(function () {
    Route::get('/dashboard', [KokiController::class, 'index'])->name('dashboard');
    Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
    Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');
});