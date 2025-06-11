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
     * Tampilkan halaman bukti pembayaran (blade view).
     */
    public function showPaymentReceipt(int $reservasiId)
    {
        try {
            $userId = Auth::id();

            $reservasi = Reservasi::with(['meja', 'pengguna', 'orders'])
                                  ->where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->firstOrFail();

            // Generate atau ambil invoice
            $invoiceResult = $this->invoiceService->generateInvoice($reservasiId);
            if (! $invoiceResult['success']) {
                abort(500, 'Gagal membuat invoice');
            }
            $invoice = $invoiceResult['data'];

            // QR Code untuk blade
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
     * API: Ambil data invoice + reservasi + customer + items sebagai JSON
     */
    public function getInvoiceData(int $reservasiId)
    {
        try {
            $userId = Auth::id();

            $reservasi = Reservasi::with(['meja', 'orders', 'pengguna'])
                                  ->where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->firstOrFail();

            // Hasil generateInvoice sudah mengisi subtotal, service_fee, dll
            $invoiceResult = $this->invoiceService->generateInvoice($reservasiId);
            if (! $invoiceResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $invoiceResult['message'] ?? 'Gagal membuat invoice',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'invoice'   => $invoiceResult['data'],
                    'reservasi' => $reservasi,
                    'customer'  => $reservasi->pengguna,
                    'items'     => $reservasi->orders,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak ditemukan atau akses ditolak',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error getInvoiceData', [
                'reservasi_id' => $reservasiId,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Generate atau regenerate invoice (POST).
     */
    public function generateInvoice(int $reservasiId)
    {
        try {
            $userId    = Auth::id();
            $reservasi = Reservasi::where('id', $reservasiId)
                                  ->where('user_id', $userId)
                                  ->firstOrFail();

            $result = $this->invoiceService->generateInvoice($reservasiId);
            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak ditemukan atau akses ditolak',
            ], 404);

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
     * API: Get QR code payload untuk Ionic (GET).
     */
    public function getQRCode(int $reservasiId)
    {
        try {
            $userId = Auth::id();
            Reservasi::where('id', $reservasiId)
                     ->where('user_id', $userId)
                     ->firstOrFail();

            $payload = $this->invoiceService->getQrPayload($reservasiId);
            return response()->json([
                'success' => true,
                'data'    => $payload,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak ditemukan atau akses ditolak',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error getQRCode', [
                'reservasi_id' => $reservasiId,
                'error'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil QR code',
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
     public function verifyAttendance(Request $request, $kodeReservasi)
{
    // Cari reservasi berdasarkan kode
    $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();

    if (! $reservasi) {
        return response()->json([
            'success' => false,
            'message' => 'Kode reservasi tidak ditemukan'
        ], 404);
    }

    if ($reservasi->kehadiran_status === 'hadir') {
        return response()->json([
            'success' => false,
            'message' => 'Kehadiran sudah terverifikasi'
        ], 400);
    }

    // Tandai kehadiran
    $reservasi->kehadiran_status = 'hadir';
    $reservasi->save();

    return response()->json([
        'success' => true,
        'message' => 'Kehadiran berhasil diverifikasi'
    ]);
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
