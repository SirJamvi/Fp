<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\User\UserController;
use App\Models\Menu;

// Admin Controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ReservasiController as AdminReservasiController;

// Pelayan Controllers
use App\Http\Controllers\Pelayan\PelayanController;
use App\Http\Controllers\Pelayan\PelayanMejaController;

// Koki Controller
use App\Http\Controllers\Koki\KokiController;

// Public welcome page
Route::get('/', function () {
    $menus = Menu::where('is_available', true)->get();
    return view('welcome', compact('menus'));
});

// =======================
// Authentication
// =======================
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

// =======================
// Transaksi (opsional, bisa diganti atau dihapus jika memang tidak dipakai lagi)
// =======================
Route::get('/bayar', [TransaksiController::class, 'bayar']);

// =======================
// Public User Routes
// =======================
Route::get('/user/bukti-pembayaran/{kodeReservasi}', [UserController::class, 'buktiPembayaran'])
    ->name('user.bukti.pembayaran');

// =======================
// Debug Tools (hapus di production)
// =======================
Route::get('/debug/scanqr', [PelayanController::class, 'scanQr']);
Route::get('/debug/scanqr/proses/{kode}', [PelayanController::class, 'prosesScanQr']);

// =======================
// Admin Routes
// =======================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->name('dashboard');

        Route::resource('menu', MenuController::class);
        Route::resource('meja', MejaController::class);
        Route::resource('kelola-akun', PenggunaController::class);

        Route::get('/reservasi', [AdminReservasiController::class, 'index'])
            ->name('reservasi');
        Route::get('/reservasi/export/excel', [AdminReservasiController::class, 'exportExcel'])
            ->name('reservasi.export.excel');
        Route::get('/reservasi/export/pdf', [AdminReservasiController::class, 'exportPdf'])
            ->name('reservasi.export.pdf');
        Route::get('/reservasi/export/word', [AdminReservasiController::class, 'exportWord'])
            ->name('reservasi.export.word');

        Route::view('/laporan', 'admin.laporan', ['title' => 'Laporan'])
            ->name('laporan');
        Route::view('/info-cust', 'admin.info-cust', ['title' => 'Info Pelanggan'])
            ->name('info-cust');
    });

// =======================
// Pelayan Routes
// =======================
Route::prefix('pelayan')
    ->name('pelayan.')
    ->middleware(['auth', 'pelayan'])
    ->group(function () {
        // Dashboard utama Pelayan: menampilkan daftar menu & meja
        Route::get('/dashboard', [PelayanController::class, 'index'])
            ->name('dashboard');

        // Simpan order baru (reservasi + detail item)
        Route::post('/order/store', [PelayanController::class, 'storeOrder'])
            ->name('order.store');

        // Proses pembayaran (tunai atau QRIS) untuk reservasi tertentu
        Route::post('/order/{reservasi_id}/pay', [PelayanController::class, 'processPayment'])
            ->name('order.pay');

        // Tampilkan ringkasan pesanan (order summary)
        Route::get('/order/{reservasi_id}/summary', [PelayanController::class, 'showOrderSummary'])
            ->name('order.summary');

        // Tambah item ke order yang sudah aktif
        Route::post('/order/{reservasi_id}/add-items', [PelayanController::class, 'addItemsToOrder'])
            ->name('order.addItems');

        // Daftar semua reservasi (dengan filter & pagination)
        Route::get('/reservasi', [PelayanController::class, 'reservasi'])
            ->name('reservasi');

        // Tampilkan form detail reservasi (jika diperlukan di view; 
        // karena di refactor tidak ada method detailReservasi, hapus atau arahkan ke summary)
        // Jika tidak ada di controller, hapus baris ini atau arahkan ke order.summary:
        // Route::get('/reservasi/{id}/detail', [PelayanController::class, 'detailReservasi'])
        //     ->name('reservasi.detail');

        // Tampilkan form bayar sisa (partial payment)
        Route::get('/reservasi/{id}/bayar-sisa', [PelayanController::class, 'bayarSisa'])
            ->name('reservasi.bayarSisa');

        // Proses form bayar sisa (tunai atau QRIS)
        Route::post('/reservasi/{id}/bayar-sisa', [PelayanController::class, 'bayarSisaPost'])
            ->name('reservasi.bayarSisa.post');

        // Tampilkan halaman pembayaran QRIS untuk partial payment
        Route::get('/reservasi/{id}/bayar-sisa/qris', [PelayanController::class, 'showQrisPayment'])
            ->name('reservasi.bayarSisa.qris');

        // Callback Midtrans untuk partial payment
        Route::post('/reservasi/{id}/bayar-sisa/callback', [PelayanController::class, 'handleQrisCallback'])
            ->name('reservasi.bayarSisa.callback');

        // Fitur scan QR → konfirmasi kehadiran
        Route::get('/scanqr', [PelayanController::class, 'scanQr'])
            ->name('scanqr');
        Route::get('/scanqr/proses/{kodeReservasi}', [PelayanController::class, 'prosesScanQr'])
            ->name('scanqr.proses');

        // Tandai reservasi selesai (complete) → meja kembali tersedia
        Route::post('/reservasi/{reservasi_id}/complete', [PelayanController::class, 'completeReservation'])
            ->name('reservasi.complete');

        // Batalkan reservasi (cancel)
        Route::post('/reservasi/{reservasi_id}/cancel', [PelayanController::class, 'cancelReservation'])
            ->name('reservasi.cancel');

        // (Opsional) Daftar dan toggle status meja via PelayanMejaController
        Route::get('/meja', [PelayanMejaController::class, 'index'])
            ->name('meja');
        Route::post('/meja/{id}/toggle', [PelayanMejaController::class, 'toggle'])
            ->name('meja.toggle');
        Route::post('/meja/{id}/set-tersedia', [PelayanMejaController::class, 'setTersedia'])
            ->name('meja.setTersedia');
    });

// =======================
// Koki Routes
// =======================
Route::prefix('koki')
    ->name('koki.')
    ->middleware(['auth', 'koki'])
    ->group(function () {
        Route::get('/dashboard', [KokiController::class, 'index'])->name('dashboard');
        Route::get('/daftar-pesanan', [KokiController::class, 'daftarPesanan'])->name('daftar-pesanan');
        Route::get('/stok-bahan', [KokiController::class, 'stokBahan'])->name('stok-bahan');

        // API untuk mengambil pesanan (JSON)
        Route::get('/orders/get', [KokiController::class, 'getOrders'])->name('orders.get');

        // API untuk memperbarui status pesanan menggunakan reservasi_id
        Route::post('/orders/{reservasi}/update-status', [KokiController::class, 'updateOrderStatus'])
            ->name('orders.updateStatus');
    });
