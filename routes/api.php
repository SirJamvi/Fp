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
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Customer\MidtransController;

// Rute publik (tanpa autentikasi)
Route::post('customer/register', [AuthController::class, 'register']);
Route::post('customer/login',    [AuthController::class, 'login']);

// Menu bisa dilihat tanpa login
Route::get('customer/menus',       [MenuController::class, 'index']);
Route::get('customer/menus/{menu}',[MenuController::class, 'show']);

// Meja bisa dilihat tanpa login untuk reservasi
Route::get('customer/tables',                [MejaController::class, 'index']);
Route::get('customer/tables/areas',          [MejaController::class, 'getAreas']);
Route::post('customer/tables/check-availability', [MejaController::class, 'checkAvailability']);

// Route Test API (untuk debugging awal)
Route::get('/test', [TestController::class, 'index']);

// Rute publik untuk notifikasi Midtrans
Route::post('midtrans-notification', [MidtransController::class, 'handleNotification']);

// Verifikasi kehadiran (QR Code scan)
Route::post('customer/verify-attendance/{kodeReservasi}', [InvoiceController::class, 'verifyAttendance']);


// Rute yang memerlukan autentikasi (menggunakan Sanctum)
Route::middleware(['auth:sanctum', 'customer'])
     ->prefix('customer')
     ->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('profile',               [ProfileController::class, 'show']);
    Route::put('profile',               [ProfileController::class, 'update']);
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);

    // Reservations
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations',  [ReservationController::class, 'index']);

    // <-- Pindahkan booked-times sebelum wildcard {reservasi}
    Route::get('reservations/booked-times', [ReservationController::class, 'getBookedTimes']);

    // Show / Cancel / Payment (wildcard dengan constraint numeric)
    Route::get('reservations/{reservasi}', [
        ReservationController::class,
        'show'
    ])->where('reservasi', '[0-9]+');
    Route::post('reservations/{reservasi}/cancel', [
        ReservationController::class,
        'cancel'
    ])->where('reservasi', '[0-9]+');
    Route::post('reservations/{reservasi}/process-payment', [
        ReservationController::class,
        'processPayment'
    ])->where('reservasi', '[0-9]+');

    // Orders (Pre-order dari customer)
    Route::post('orders/pre-order', [OrderController::class, 'storePreOrder']);
    Route::post('reservations/{reservasi}/add-items', [
        OrderController::class,
        'addItemsToReservation'
    ])->where('reservasi', '[0-9]+');
    Route::get('orders', [OrderController::class, 'index']);

    // Ratings
    Route::post('ratings', [RatingController::class, 'store']);
    Route::get('ratings',  [RatingController::class, 'index']);

    // Notifications
    Route::get('notifications',              [NotificationController::class, 'index']);
    Route::get('notifications/latest',       [NotificationController::class, 'getLatestNotifications']);
    Route::post('notifications/{notification}/read',      [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-as-read',         [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{notification}',         [NotificationController::class, 'destroy']);

    // Notification testing
    Route::get('notifications/pending',      [NotificationController::class, 'getPendingNotifications']);
    Route::post('notifications/send-pending',[NotificationController::class, 'sendPendingNotifications']);

    // Invoices
    Route::get('invoices',                      [InvoiceController::class, 'getUserInvoices']);
    Route::get('invoices/{reservasiId}',       [InvoiceController::class, 'getInvoiceData'])
         ->where('reservasiId', '[0-9]+');
    Route::post('invoices/{reservasiId}/generate',  [InvoiceController::class, 'generateInvoice'])
         ->where('reservasiId', '[0-9]+');
    Route::get('invoices/{reservasiId}/qr-code',     [InvoiceController::class, 'getQRCode'])
         ->where('reservasiId', '[0-9]+');
    Route::post('invoices/{reservasiId}/update-payment', [InvoiceController::class, 'updatePaymentStatus'])
         ->where('reservasiId', '[0-9]+');
    Route::get('invoices/summary',              [InvoiceController::class, 'getInvoiceSummary']);
    Route::post('invoices/{reservasiId}/resend',[InvoiceController::class, 'resendInvoice'])
         ->where('reservasiId', '[0-9]+');

    // Checkout dari keranjang (Midtrans)
    Route::post('checkout', [MidtransController::class, 'checkoutFromCart']);
});
