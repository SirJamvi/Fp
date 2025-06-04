<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\Customer\MejaController;
use App\Http\Controllers\Customer\ReservationController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\RatingController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\TestController;

// Rute publik (tanpa autentikasi)
Route::post('customer/register', [AuthController::class, 'register']);
Route::post('customer/login', [AuthController::class, 'login']);

// Menu bisa dilihat tanpa login
Route::get('customer/menus', [MenuController::class, 'index']);
Route::get('customer/menus/{menu}', [MenuController::class, 'show']);

// Meja bisa dilihat tanpa login untuk reservasi
Route::get('customer/tables', [MejaController::class, 'index']);
Route::get('customer/tables/areas', [MejaController::class, 'getAreas']);
Route::post('customer/tables/check-availability', [MejaController::class, 'checkAvailability']);

// Route Test API (untuk debugging awal)
Route::get('/test', [TestController::class, 'index']);

// Rute yang memerlukan autentikasi (menggunakan Sanctum)
Route::middleware(['auth:sanctum', 'customer'])->prefix('customer')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);

    // Reservations
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations', [ReservationController::class, 'index']);
    Route::get('reservations/{reservasi}', [ReservationController::class, 'show']);
    Route::post('reservations/{reservasi}/cancel', [ReservationController::class, 'cancel']);
    Route::post('reservations/{reservasi}/process-payment', [ReservationController::class, 'processPayment']);

    // Orders (Pre-order dari customer)
    Route::post('orders/pre-order', [OrderController::class, 'storePreOrder']);
    Route::post('reservations/{reservasi}/add-items', [OrderController::class, 'addItemsToReservation']);
    Route::get('orders', [OrderController::class, 'index']);

    // Ratings
    Route::post('ratings', [RatingController::class, 'store']);
    Route::get('ratings', [RatingController::class, 'index']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});