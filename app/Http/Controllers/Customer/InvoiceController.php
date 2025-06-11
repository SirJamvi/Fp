<?php
// app/Http/Controllers/Customer/InvoiceController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Models\Reservasi;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Tampilkan halaman bukti pembayaran (invoice + dinamis QR)
     */
    public function showPaymentReceipt(int $reservasiId)
    {
        try {
            $userId = Auth::id();

            $reservasi = Reservasi::with(['meja', 'pengguna'])
                                  ->where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->firstOrFail();

            // Generate invoice
            $invoiceResult = $this->invoiceService->generateInvoice($reservasiId);
            $invoice = $invoiceResult['data'] ?? null;

            // Generate QR code dinamis untuk presensi
            $qrResult = $this->invoiceService->generateAttendanceQRCode($reservasi);
            $qrCode = $qrResult['success'] ? $qrResult['data'] : null;

            // Get attendance status
            $attendanceStatus = $this->invoiceService->getAttendanceStatus($reservasiId);

            return view('user.bukti-pembayaran', compact('reservasi', 'invoice', 'qrCode', 'attendanceStatus'));
        } catch (\Exception $e) {
            Log::error('Error showing payment receipt', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);
            abort(404, 'Halaman tidak ditemukan');
        }
    }

    /**
     * API: Get invoice data (draft or final) as JSON
     */
    public function getInvoiceData(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->with(['meja', 'orders'])
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            // Get invoice data
            $invoiceResult = $this->invoiceService->generateInvoice($reservasiId);
            
            if (!$invoiceResult['success']) {
                return response()->json($invoiceResult, 400);
            }

            // Get formatted invoice data if available
            $invoice = Invoice::where('reservasi_id', $reservasiId)->first();
            $formattedData = $invoice ? $invoice->getFormattedData() : null;

            return response()->json([
                'success' => true,
                'message' => 'Data invoice berhasil diambil',
                'data' => [
                    'invoice_raw' => $invoiceResult['data'],
                    'invoice_formatted' => $formattedData
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting invoice data', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data invoice',
            ], 500);
        }
    }

    /**
     * API: Permanently generate or regenerate invoice
     */
    public function generateInvoice(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $result = $this->invoiceService->generateInvoice($reservasiId);
            return response()->json($result, $result['success'] ? 201 : 400);
        } catch (\Exception $e) {
            Log::error('Error generating invoice', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice',
            ], 500);
        }
    }

    /**
     * API: Get QR code dinamis untuk presensi as JSON
     */
    public function getQRCode(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            // Generate QR code dinamis untuk presensi
            $qrResult = $this->invoiceService->generateAttendanceQRCode($reservasi);

            if (!$qrResult['success']) {
                return response()->json($qrResult, 400);
            }

            // Get attendance status
            $attendanceStatus = $this->invoiceService->getAttendanceStatus($reservasiId);

            return response()->json([
                'success' => true,
                'message' => 'QR Code presensi berhasil dibuat',
                'data' => [
                    'qr_code' => $qrResult['data'],
                    'attendance_status' => $attendanceStatus['data'] ?? null,
                    'kode_reservasi' => $reservasi->kode_reservasi,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error generating attendance QR code', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR code presensi',
            ], 500);
        }
    }

    /**
     * API: Refresh QR code dinamis (generate ulang token baru)
     */
    public function refreshQRCode(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            // Check if reservation can still check-in
            $attendanceStatus = $this->invoiceService->getAttendanceStatus($reservasiId);
            
            if (!$attendanceStatus['success'] || $attendanceStatus['data']['status'] !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code tidak dapat di-refresh: ' . ($attendanceStatus['data']['message'] ?? 'Status tidak tersedia'),
                    'data' => $attendanceStatus['data'] ?? null
                ], 400);
            }

            // Generate new QR code
            $qrResult = $this->invoiceService->generateAttendanceQRCode($reservasi);

            return response()->json([
                'success' => $qrResult['success'],
                'message' => $qrResult['success'] ? 'QR Code berhasil di-refresh' : $qrResult['message'],
                'data' => $qrResult['data'] ?? null
            ], $qrResult['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error refreshing QR code', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh QR code',
            ], 500);
        }
    }

    /**
     * API: Get attendance status untuk reservasi
     */
    public function getAttendanceStatus(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $result = $this->invoiceService->getAttendanceStatus($reservasiId);
            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error getting attendance status', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status presensi',
            ], 500);
        }
    }

    /**
     * API: List all user invoices with pagination
     */
    public function getUserInvoices(Request $request)
    {
        try {
            $userId = Auth::id();
            $perPage = $request->get('per_page', 10);

            $invoices = Invoice::whereHas('reservasi', fn($q) => $q->where('user_id', $userId))
                                ->with(['reservasi.meja'])
                                ->orderBy('generated_at', 'desc')
                                ->paginate($perPage);

            $formatted = $invoices->getCollection()->map(function($invoice) {
                return method_exists($invoice, 'getFormattedData') ? $invoice->getFormattedData() : $invoice;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar invoice berhasil diambil',
                'data' => $formatted,
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting user invoices', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar invoice',
            ], 500);
        }
    }

    /**
     * API: Update payment status
     */
    public function updatePaymentStatus(Request $request, int $reservasiId)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $result = $this->invoiceService->updatePaymentStatus(
                $reservasiId,
                $request->amount_paid,
                $request->payment_method
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error updating payment status', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pembayaran',
            ], 500);
        }
    }

    /**
     * API: Verify attendance (untuk pelayan/admin)
     */
    public function verifyAttendance(string $kodeReservasi)
    {
        try {
            $result = $this->invoiceService->verifyAttendance($kodeReservasi);
            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error verifying attendance', [
                'kode_reservasi' => $kodeReservasi,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi kehadiran',
            ], 500);
        }
    }

    /**
     * API: Verify attendance dengan token (untuk QR code dinamis)
     */
    public function verifyAttendanceWithToken(Request $request, string $kodeReservasi)
    {
        $request->validate([
            'token' => 'required|string|size:64',
        ]);

        try {
            // Validasi token terlebih dahulu
            $isValidToken = $this->invoiceService->validateAttendanceToken($kodeReservasi, $request->token);
            
            if (!$isValidToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token QR code tidak valid atau sudah kedaluwarsa',
                ], 400);
            }

            // Jika token valid, lakukan verifikasi kehadiran
            $result = $this->invoiceService->verifyAttendance($kodeReservasi);
            
            if ($result['success']) {
                Log::info('Attendance verified with QR token', [
                    'kode_reservasi' => $kodeReservasi,
                    'token' => substr($request->token, 0, 10) . '...' // Log partial token for security
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error verifying attendance with token', [
                'kode_reservasi' => $kodeReservasi,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi kehadiran dengan token',
            ], 500);
        }
    }

    /**
     * API: Get complete invoice with QR code and attendance status
     */
    public function getCompleteInvoiceData(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            // Get invoice data
            $invoiceResult = $this->invoiceService->generateInvoice($reservasiId);
            
            if (!$invoiceResult['success']) {
                return response()->json($invoiceResult, 400);
            }

            // Get formatted invoice data
            $invoice = Invoice::where('reservasi_id', $reservasiId)->first();
            $formattedInvoice = $invoice ? $invoice->getFormattedData() : null;

            // Get QR code for attendance
            $qrResult = $this->invoiceService->generateAttendanceQRCode($reservasi);

            // Get attendance status
            $attendanceStatus = $this->invoiceService->getAttendanceStatus($reservasiId);

            return response()->json([
                'success' => true,
                'message' => 'Data lengkap invoice berhasil diambil',
                'data' => [
                    'invoice' => $formattedInvoice,
                    'qr_code' => $qrResult['success'] ? $qrResult['data'] : null,
                    'attendance_status' => $attendanceStatus['success'] ? $attendanceStatus['data'] : null,
                    'reservasi' => [
                        'kode_reservasi' => $reservasi->kode_reservasi,
                        'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                        'status_kehadiran' => $reservasi->status_kehadiran,
                        'waktu_checkin' => $reservasi->waktu_checkin,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting complete invoice data', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lengkap invoice',
            ], 500);
        }
    }

    /**
     * API: Resend invoice
     */
    public function resendInvoice(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $result = $this->invoiceService->generateInvoice($reservasiId);
            if ($result['success']) {
                Log::info('Invoice resent successfully', [
                    'reservasi_id' => $reservasiId,
                    'user_id' => $userId,
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error resending invoice', [
                'reservasi_id' => $reservasiId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ulang invoice',
            ], 500);
        }
    }

    /**
     * API: Placeholder ringkasan invoice (belum diimplementasi)
     */
    public function getInvoiceSummary()
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur ringkasan invoice belum diimplementasikan',
        ], 501); // 501 Not Implemented
    }
}