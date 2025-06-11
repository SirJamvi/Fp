<?php

use Illuminate\Support\Facades\Route;
use App\Models\Menu;

// Auth
use App\Http\Controllers\Auth\LoginController;

// Public User
use App\Http\Controllers\User\UserController;

// Admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\InfoCustController;
use App\Http\Controllers\Admin\ReservasiController as AdminReservasiController;

// Pelayan
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Pelayan\PelayanMejaController;

// Koki
use App\Http\Controllers\Koki\KokiController;

use App\Http\Controllers\Customer\InvoiceController;

// =======================
// Public Routes
// =======================
Route::get('/', function () {
    $menus = Menu::where('is_available', true)->get();
    return view('welcome', compact('menus'));
});

// =======================
// Authentication Routes
// =======================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// routes/web.php
Route::post('customer/verify-attendance/{kodeReservasi}', [InvoiceController::class, 'verifyAttendance'])
     ->name('verify.attendance');


// =======================
// Public User Routes
// =======================
Route::get('/user/bukti-pembayaran/{kodeReservasi}', [UserController::class, 'buktiPembayaran'])
    ->name('user.bukti.pembayaran');

// =======================
// Debug Tools (Remove in production)
// =======================
Route::get('/debug/scanqr', [PelayanController::class, 'scanQr']);
Route::get('/debug/scanqr/proses/{kode}', [PelayanController::class, 'prosesScanQr']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Resource Management
    Route::resource('menu', MenuController::class);
    Route::resource('meja', MejaController::class);
    Route::resource('kelola-akun', PenggunaController::class);

    Route::get('/ratings/export-pdf', [AdminController::class, 'exportPdf'])->name('ratings.exportPdf');

    // Reservasi Management
    Route::get('/reservasi', [AdminReservasiController::class, 'index'])->name('reservasi');
    Route::get('/reservasi/export/excel', [AdminReservasiController::class, 'exportExcel'])->name('reservasi.export.excel');
    Route::get('/reservasi/export/pdf', [AdminReservasiController::class, 'exportPdf'])->name('reservasi.export.pdf');
    Route::get('/reservasi/export/word', [AdminReservasiController::class, 'exportWord'])->name('reservasi.export.word');

    // Info Costumer
    Route::get('/info-cust', [InfoCustController::class, 'index'])->name('info-cust');

    Route::get('/info-cust/export/excel', [InfoCustController::class, 'exportExcel'])->name('info-cust.export.excel');
    Route::get('/info-cust/export/pdf', [InfoCustController::class, 'exportPdf'])->name('info-cust.export.pdf');
    Route::get('/info-cust/export/word', [InfoCustController::class, 'exportWord'])->name('info-cust.export.word');
});

/*
|--------------------------------------------------------------------------
| Pelayan Routes
|--------------------------------------------------------------------------
*/
Route::prefix('pelayan')->name('pelayan.')->middleware(['auth', 'pelayan'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [PelayanController::class, 'index'])->name('dashboard');

    // Dine-in & Reservasi Management
    Route::get('/dinein', [PelayanController::class, 'dinein'])->name('dinein');
    Route::get('/reservasi', [PelayanController::class, 'reservasi'])->name('reservasi');
    Route::get('/reservasi/{id}/detail', [PelayanController::class, 'showDetailReservasi'])->name('reservasi.detail');

    // Order Management
    Route::post('/order/store', [PelayanController::class, 'storeOrder'])->name('order.store');
    Route::post('/order/{reservasi_id}/pay', [PelayanController::class, 'processPayment'])->name('order.pay');
    Route::get('/order/summary/{reservasi_id}', [PelayanController::class, 'showOrderSummary'])->name('order.summary');
    Route::post('/order/{reservasi_id}/add-items', [PelayanController::class, 'addItemsToOrder'])->name('order.addItems');
    
    // Menangani AJAX marking payment as settled
    Route::post('reservasi/{id}/settle', [PelayanController::class, 'settlePayment'])->name('reservasi.settle');

    // Reservation Status Management
    Route::post('/reservasi/{id}/complete', [PelayanController::class, 'completeReservation'])->name('reservasi.complete');
    Route::post('/reservasi/{id}/cancel', [PelayanController::class, 'cancelReservation'])->name('reservasi.cancel');
    Route::delete('/reservasi/{id}/destroy', [PelayanController::class, 'destroy'])->name('reservasi.destroy');

    // Payment Management (Partial Payment)
    Route::get('/reservasi/{id}/bayar-sisa', [PelayanController::class, 'bayarSisa'])->name('reservasi.bayarSisa');
    Route::post('/reservasi/{id}/bayar-sisa', [PelayanController::class, 'bayarSisaPost'])->name('reservasi.bayarSisa.post');
    Route::get('/reservasi/{id}/bayar-sisa/qris', [PelayanController::class, 'showQrisPayment'])->name('reservasi.bayarSisa.qris');
    Route::post('/reservasi/{id}/bayar-sisa/callback', [PelayanController::class, 'handleQrisCallback'])->name('reservasi.bayarSisa.callback');
    // HARUS diletakkan **setelah** dua route di atas
    Route::get('/reservasi/{id}/bayar-sisa/{status}', [PelayanController::class,'bayarSisa'])
        ->where('status', 'f|u|e')   // f = finish, u = unfinished, e = error
        ->name('reservasi.bayarSisa.status');



    // Table Management
    Route::get('/meja', [PelayanMejaController::class, 'index'])->name('meja');
    Route::post('/meja/{id}/toggle', [PelayanMejaController::class, 'toggle'])->name('meja.toggle');
    Route::post('/meja/{id}/set-tersedia', [PelayanMejaController::class, 'setTersedia'])->name('meja.setTersedia');
    Route::get('/get-meja-by-area/{area}', [PelayanController::class, 'getMejaByArea']);

    // QR Code Scanner
    Route::get('/scanqr', [PelayanController::class, 'scanQr'])->name('scanqr');
    Route::get('/scanqr/proses/{kodeReservasi}', [PelayanController::class, 'prosesScanQr'])->name('scanqr.proses');
});

/*
|--------------------------------------------------------------------------
| Koki Routes
|--------------------------------------------------------------------------
*/
Route::prefix('koki')->name('koki.')->middleware(['auth', 'koki'])->group(function () {
    // Dashboard & Management
    Route::get('/dashboard', [KokiController::class, 'index'])->name('dashboard');
    Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
    Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');

    // API Routes for Orders
    Route::get('/orders/get', [KokiController::class, 'getOrders'])->name('orders.get');
    Route::post('/orders/{reservasi}/update-status', [KokiController::class, 'updateOrderStatus'])->name('orders.updateStatus');
});