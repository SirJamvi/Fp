<?php

use Illuminate\Support\Facades\Route;
use App\Models\Menu;

// Auth
use App\Http\Controllers\Auth\LoginController;

// Public User
use App\Http\Controllers\User\UserController;

// Transaksi
use App\Http\Controllers\TransaksiController;

// Admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ReservasiController as AdminReservasiController;

// Pelayan
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Pelayan\PelayanMejaController;

// Koki
use App\Http\Controllers\Koki\KokiController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $menus = Menu::where('is_available', true)->get();
    return view('welcome', compact('menus'));
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Transaksi (Testing / Public)
Route::get('/bayar', [TransaksiController::class, 'bayar']);

// Bukti Pembayaran untuk User
Route::get('/user/bukti-pembayaran/{kodeReservasi}', [UserController::class, 'buktiPembayaran'])->name('user.bukti.pembayaran');

// Debug QR Scan
Route::get('/debug/scanqr', [PelayanController::class, 'scanQr']);
Route::get('/debug/scanqr/proses/{kode}', [PelayanController::class, 'prosesScanQr']);


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Resource Management
    Route::resource('menu', MenuController::class);
    Route::resource('meja', MejaController::class);
    Route::resource('kelola-akun', PenggunaController::class);

    // Reservasi
    Route::get('/reservasi', [AdminReservasiController::class, 'index'])->name('reservasi');
    Route::get('/reservasi/export/excel', [AdminReservasiController::class, 'exportExcel'])->name('reservasi.export.excel');
    Route::get('/reservasi/export/pdf', [AdminReservasiController::class, 'exportPdf'])->name('reservasi.export.pdf');
    Route::get('/reservasi/export/word', [AdminReservasiController::class, 'exportWord'])->name('reservasi.export.word');

    // Laporan & Info
    Route::view('/laporan', 'admin.laporan', ['title' => 'Laporan'])->name('laporan');
    Route::view('/info-cust', 'admin.info-cust', ['title' => 'Info Pelanggan'])->name('info-cust');
});


/*
|--------------------------------------------------------------------------
| Pelayan Routes
|--------------------------------------------------------------------------
*/
Route::prefix('pelayan')->name('pelayan.')->middleware(['auth', 'pelayan'])->group(function () {
    Route::get('/dashboard', [PelayanController::class, 'index'])->name('dashboard');

    // Reservasi dan Order
    Route::get('/dinein', [PelayanController::class, 'dinein'])->name('dinein');
    Route::get('/reservasi', [PelayanController::class, 'reservasi'])->name('reservasi');
    Route::get('/reservasi/{id}/detail', [PelayanController::class, 'showDetailReservasi'])->name('reservasi.detail');

    // Konfirmasi & Delete Reservasi
    Route::post('/reservasi/{id}/complete', [PelayanController::class, 'completeReservation'])->name('reservasi.complete');
    Route::post('/reservasi/{id}/cancel',   [PelayanController::class, 'cancelReservation'])->name('reservasi.cancel');
    Route::delete('/reservasi/{id}/destroy', [PelayanController::class, 'destroy'])->name('reservasi.destroy');


    // Pembayaran Sisa (Tunai & QRIS)
    Route::get('/reservasi/{id}/bayar-sisa', [PelayanController::class, 'bayarSisa'])->name('reservasi.bayarSisa');
    Route::post('/reservasi/{id}/bayar-sisa', [PelayanController::class, 'bayarSisaPost'])->name('reservasi.bayarSisa.post');
    Route::get('/reservasi/{id}/bayar-sisa/qris', [PelayanController::class, 'showQrisPayment'])->name('reservasi.bayarSisa.qris');
    Route::post('/reservasi/{id}/bayar-sisa/callback', [PelayanController::class, 'handleQrisCallback'])->name('reservasi.bayarSisa.callback');


    // Order Handling (sudah ada)
    Route::post('/order/store', [PelayanController::class, 'storeOrder'])->name('order.store');
    Route::post('/order/{reservasi_id}/pay', [PelayanController::class, 'processPayment'])->name('order.pay');
    Route::get('/order/summary/{reservasi_id}', [PelayanController::class, 'showOrderSummary'])->name('order.summary');
    Route::post('/order/{reservasi_id}/add-items', [PelayanController::class, 'addItemsToOrder'])->name('order.addItems');

    // Manajemen Meja
    Route::get('/meja', [PelayanMejaController::class, 'index'])->name('meja');
    Route::post('/meja/{id}/toggle', [PelayanMejaController::class, 'toggle'])->name('meja.toggle');
    Route::post('/meja/{id}/set-tersedia', [PelayanMejaController::class, 'setTersedia'])->name('meja.setTersedia');
    Route::get('/get-meja-by-area/{area}', [PelayanController::class, 'getMejaByArea']);

    // QR Code Scan
    Route::get('/scanqr', [PelayanController::class, 'scanQr'])->name('scanqr');
    Route::get('/scanqr/proses/{kodeReservasi}', [PelayanController::class, 'prosesScanQr'])->name('scanqr.proses');

    // Konfirmasi Reservasi
    Route::post('/reservasi/{id}/complete', [PelayanController::class, 'completeReservation'])->name('reservasi.complete');
    Route::post('/reservasi/{id}/cancel', [PelayanController::class, 'cancelReservation'])->name('reservasi.cancel');
});


/*
|--------------------------------------------------------------------------
| Koki Routes
|--------------------------------------------------------------------------
*/
Route::prefix('koki')->name('koki.')->middleware(['auth', 'koki'])->group(function () {
    Route::get('/dashboard', [KokiController::class, 'index'])->name('dashboard');
    Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
    Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');

    // API Order
    Route::get('/orders/get', [KokiController::class, 'getOrders'])->name('orders.get');
    Route::post('/orders/{reservasi}/update-status', [KokiController::class, 'updateOrderStatus'])->name('orders.updateStatus');
});
