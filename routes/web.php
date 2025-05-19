<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ReservasiController as AdminReservasiController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Menu;
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Pelayan\PelayanMejaController;
use App\Http\Controllers\Koki\KokiController;
use App\Http\Controllers\User\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $menus = Menu::where('is_available', true)->get();
    return view('welcome', compact('menus'));
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public Routes (tanpa auth)
Route::get('/user/bukti-pembayaran/{kodeReservasi}', [UserController::class, 'buktiPembayaran'])
    ->name('user.bukti.pembayaran');

// Debug Routes (bisa dihapus di production)
Route::get('/debug/scanqr', [PelayanController::class, 'scanQr']);
Route::get('/debug/scanqr/proses/{kode}', [PelayanController::class, 'prosesScanQr']);

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
    Route::get('/dashboard', [PelayanController::class, 'index'])->name('dashboard');
    Route::post('/order/store', [PelayanController::class, 'storeOrder'])->name('order.store');
    Route::post('/order/{reservasi_id}/pay', [PelayanController::class, 'processPayment'])->name('order.pay');
    Route::get('/order/summary/{reservasi_id}', [PelayanController::class, 'showOrderSummary'])->name('order.summary');
    Route::get('/reservasi', [PelayanController::class, 'reservasi'])->name('reservasi');
    Route::get('/reservasi/{id}/detail', [PelayanController::class, 'detailReservasi'])->name('reservasi.detail');
    Route::get('/meja', [PelayanMejaController::class, 'index'])->name('meja');
    Route::post('/meja/{id}/toggle', [PelayanMejaController::class, 'toggleStatus'])->name('meja.toggle');

    // Scan QR Code
    Route::get('/scanqr', [PelayanController::class, 'scanQr'])->name('scanqr');
    Route::get('/scanqr/proses/{kodeReservasi}', [PelayanController::class, 'prosesScanQr'])
        ->name('scanqr.proses');
});

// Koki Routes
Route::prefix('koki')->name('koki.')->middleware(['auth', 'koki'])->group(function () {
    Route::get('/dashboard', [KokiController::class, 'index'])->name('dashboard');
    Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
    Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');
});