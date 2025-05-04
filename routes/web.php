<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
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
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/manajemen-menu', function () {
        return view('admin.manajemen-menu', ['title' => 'Manajemen Menu']);
    })->name('manajemen-menu'); 

    Route::get('/manajemen-meja', function () {
        return view('admin.manajemen-meja', ['title' => 'Manajemen Meja']);
    })->name('manajemen-meja');

    Route::get('/reservasi', function () {
        return view('admin.reservasi', ['title' => 'Reservasi']);
    })->name('reservasi');

    Route::get('/laporan', function () {
        return view('admin.laporan', ['title' => 'laporan']);
    })->name('laporan');

    Route::get('/info-cust', function () {
        return view('admin.info-cust', ['title' => 'info-cust']);
    })->name('info-cust');

    Route::get('/kelola-akun', function () {
        return view('admin.kelola-akun', ['title' => 'kelola-akun']);
    })->name('kelola-akun');
});
