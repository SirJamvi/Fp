<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\LoginController;

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

    // Menu Management (Resource Controller)
    Route::resource('menu', MenuController::class);

    // User Management (Resource Controller)
    Route::resource('kelola-akun', PenggunaController::class);

    // Additional Admin Pages
    Route::get('/manajemen-meja', function () {
        return view('admin.manajemen-meja', ['title' => 'Manajemen Meja']);
    })->name('manajemen-meja');

    Route::get('/reservasi', function () {
        return view('admin.reservasi', ['title' => 'Reservasi']);
    })->name('reservasi');

    Route::get('/laporan', function () {
        return view('admin.laporan', ['title' => 'Laporan']);
    })->name('laporan');

    Route::get('/info-cust', function () {
        return view('admin.info-cust', ['title' => 'Info Pelanggan']);
    })->name('info-cust');
});