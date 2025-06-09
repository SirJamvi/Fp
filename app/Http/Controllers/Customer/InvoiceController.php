<?php

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
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Get invoice data for a specific reservation.
     * Di-backend kita gunakan generateInvoice untuk on-the-fly draft atau final invoice.
     */
    public function getInvoiceData($reservasiId)
    {
        try {
            $userId = Auth::id();

            // Verifikasi milik user
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->with(['meja', 'orders']) // eager load yang diperlukan
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses'
                ], 404);
            }

            // Panggil service untuk generate atau fetch invoice
            $result = $this->invoiceService->generateInvoice($reservasiId);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error getting invoice data', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data invoice'
            ], 500);
        }
    }

    /**
     * Generate or regenerate invoice permanently.
     */
    public function generateInvoice($reservasiId)
    {
        try {
            $userId = Auth::id();

            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses'
                ], 404);
            }

            $result = $this->invoiceService->generateInvoice($reservasiId);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            Log::error('Error generating invoice', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat invoice'
            ], 500);
        }
    }

    /**
     * Get QR code for reservation attendance.
     */
    public function getQRCode($reservasiId)
    {
        try {
            $userId = Auth::id();

            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses'
                ], 404);
            }

            $qrCodeBase64 = $this->invoiceService->generateQRCode($reservasi);

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat',
                'data'    => [
                    'qr_code_base64'   => $qrCodeBase64,
                    'kode_reservasi'   => $reservasi->kode_reservasi,
                    'verification_url' => config('app.url') . '/api/customer/verify-attendance/' . $reservasi->kode_reservasi
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error generating QR code', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat QR code'
            ], 500);
        }
    }

    /**
     * Get all invoices for the authenticated user (with pagination).
     */
    public function getUserInvoices(Request $request)
    {
        try {
            $userId  = Auth::id();
            $perPage = $request->get('per_page', 10);

            $invoices = Invoice::whereHas('reservasi', function ($q) use ($userId) {
                                $q->where('user_id', $userId);
                            })
                            ->with(['reservasi.meja'])
                            ->orderBy('generated_at', 'desc')
                            ->paginate($perPage);

            $formatted = $invoices->map(function ($inv) {
                return method_exists($inv, 'getFormattedData')
                    ? $inv->getFormattedData()
                    : $inv;
            });

            return response()->json([
                'success'    => true,
                'message'    => 'Daftar invoice berhasil diambil',
                'data'       => $formatted,
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page'    => $invoices->lastPage(),
                    'per_page'     => $invoices->perPage(),
                    'total'        => $invoices->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user invoices', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar invoice'
            ], 500);
        }
    }

    /**
     * Update payment status for an invoice.
     */
    public function updatePaymentStatus(Request $request, $reservasiId)
    {
        try {
            $request->validate([
                'amount_paid'    => 'required|numeric|min:0',
                'payment_method' => 'nullable|string'
            ]);

            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses'
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
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat update status pembayaran'
            ], 500);
        }
    }

    /**
     * Verify attendance using QR code.
     */
    public function verifyAttendance($kodeReservasi)
    {
        try {
            $result = $this->invoiceService->verifyAttendance($kodeReservasi);
            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error verifying attendance', [
                'kode_reservasi' => $kodeReservasi,
                'error'          => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat verifikasi kehadiran'
            ], 500);
        }
    }

    /**
     * Get invoice summary for user dashboard.
     * Saat ini belum diimplementasikan di InvoiceService.
     */
    public function getInvoiceSummary()
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur ringkasan invoice belum diimplementasikan di backend'
        ], 404);
    }

    /**
     * Resend (regenerate) invoice.
     */
    public function resendInvoice($reservasiId)
    {
        try {
            $userId = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau Anda tidak memiliki akses'
                ], 404);
            }

            $result = $this->invoiceService->generateInvoice($reservasiId);

            if ($result['success']) {
                Log::info('Invoice resent successfully', [
                    'reservasi_id' => $reservasiId,
                    'user_id'      => $userId
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error resending invoice', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim ulang invoice'
            ], 500);
        }
    }
}
