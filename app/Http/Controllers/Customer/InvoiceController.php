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
     * Tampilkan halaman bukti pembayaran (invoice + semi-dinamis QR)
     */
    public function showPaymentReceipt(int $reservasiId)
    {
        try {
            $userId = Auth::id();

            $reservasi = Reservasi::with(['meja', 'pengguna'])
                                  ->where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->firstOrFail();

            $invoiceResult = $this->invoiceService->generateInvoice($reservasiId);
            $invoice       = $invoiceResult['data'] ?? null;

            $qrCode = $this->invoiceService->generateQRCode($reservasi);

            return view('user.bukti-pembayaran', compact('reservasi', 'invoice', 'qrCode'));
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
            $userId    = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->with(['meja', 'orders'])
                                  ->first();

            if (! $reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $result = $this->invoiceService->generateInvoice($reservasiId);
            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error getting invoice data', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
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
            $userId    = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (! $reservasi) {
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
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice',
            ], 500);
        }
    }

    /**
     * API: Get QR code for reservation attendance as JSON
     */
    public function getQRCode(int $reservasiId)
    {
        try {
            $userId    = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (! $reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $qrCodeBase64 = $this->invoiceService->generateQRCode($reservasi);

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat',
                'data'    => [
                    'qr_code_base64'   => $qrCodeBase64,
                    'kode_reservasi'   => $reservasi->kode_reservasi,
                    'verification_url' => config('app.url')
                        . "/api/customer/verify-attendance/{$reservasi->kode_reservasi}",
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generating QR code', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR code',
            ], 500);
        }
    }

    /**
     * API: List all user invoices with pagination
     */
    public function getUserInvoices(Request $request)
    {
        try {
            $userId  = Auth::id();
            $perPage = $request->get('per_page', 10);

            $invoices = Invoice::whereHas('reservasi', fn($q) => $q->where('user_id', $userId))
                                ->with(['reservasi.meja'])
                                ->orderBy('generated_at', 'desc')
                                ->paginate($perPage);

            $formatted = $invoices->getCollection()->map(fn($inv) =>
                method_exists($inv, 'getFormattedData') ? $inv->getFormattedData() : $inv
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Daftar invoice berhasil diambil',
                'data'       => $formatted,
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page'    => $invoices->lastPage(),
                    'per_page'     => $invoices->perPage(),
                    'total'        => $invoices->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting user invoices', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
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
            'amount_paid'    => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        try {
            $userId    = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (! $reservasi) {
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
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pembayaran',
            ], 500);
        }
    }

    /**
     * API: Verify attendance
     */
    public function verifyAttendance(string $kodeReservasi)
    {
        try {
            $result = $this->invoiceService->verifyAttendance($kodeReservasi);
            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error verifying attendance', [
                'kode_reservasi' => $kodeReservasi,
                'error'          => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi kehadiran',
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
        ], 404);
    }

    /**
     * API: Resend invoice
     */
    public function resendInvoice(int $reservasiId)
    {
        try {
            $userId    = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->first();

            if (! $reservasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi tidak ditemukan atau akses ditolak',
                ], 404);
            }

            $result = $this->invoiceService->generateInvoice($reservasiId);
            if ($result['success']) {
                Log::info('Invoice resent successfully', [
                    'reservasi_id' => $reservasiId,
                    'user_id'      => $userId,
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error resending invoice', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ulang invoice',
            ], 500);
        }
    }
}
