<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceService
{
    /**
     * Buat atau perbarui invoice untuk reservasi.
     */
    public function generateInvoice(int $reservasiId): array
    {
        try {
            // Ambil reservasi + orders untuk perhitungan
            $reservasi = Reservasi::with('orders')->findOrFail($reservasiId);

            // Hitung subtotal dari order
            $subtotal    = $reservasi->orders->sum(fn($o) => $o->price * $o->quantity);
            $serviceFee  = round($subtotal * 0.10);        // 10% service fee
            $totalAmount = $subtotal + $serviceFee;
            $amountPaid  = 0;
            $remaining   = $totalAmount - $amountPaid;

            // Generate nomor invoice
            $invoiceNumber = 'INV-'
                . now()->format('Ymd')
                . '-'
                . Str::upper(Str::random(6));

            // Generate QR code sebagai Data URI dengan penanganan error yang lebih baik
            $qrUrl  = URL::route('pelayan.scanqr.proses', $reservasi->kode_reservasi);
            $qrData = $this->generateQRCodeSafely($qrUrl);

            // Simpan atau update invoice
            $invoice = Invoice::updateOrCreate(
                ['reservasi_id' => $reservasiId],
                [
                    'invoice_number'   => $invoiceNumber,
                    'subtotal'         => $subtotal,
                    'service_fee'      => $serviceFee,
                    'total_amount'     => $totalAmount,
                    'amount_paid'      => $amountPaid,
                    'remaining_amount' => $remaining,
                    'payment_method'   => null,
                    'payment_status'   => 'pending',
                    'qr_code'          => $qrData,
                    'generated_at'     => now(),
                ]
            );

            return [
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data'    => $invoice,
            ];
        } catch (\Exception $e) {
            Log::error('Error generateInvoice', [
                'reservasi_id' => $reservasiId,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update status pembayaran: set amount_paid, remaining_amount & payment_status.
     */
    public function updatePaymentStatus(int $reservasiId, float $amountPaid, ?string $paymentMethod): array
    {
        try {
            $invoice = Invoice::where('reservasi_id', $reservasiId)->firstOrFail();

            $remaining = $invoice->total_amount - $amountPaid;
            $status    = $remaining <= 0 ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid'      => $amountPaid,
                'remaining_amount' => $remaining,
                'payment_method'   => $paymentMethod,
                'payment_status'   => $status,
            ]);

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil diperbarui',
                'data'    => $invoice,
            ];
        } catch (\Exception $e) {
            Log::error('Error updatePaymentStatus', [
                'reservasi_id' => $reservasiId,
                'error'        => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Gagal memperbarui pembayaran',
            ];
        }
    }

    /**
     * Verifikasi kehadiran berdasarkan kode_reservasi.
     */
    public function verifyAttendance(string $kodeReservasi): array
    {
        try {
            $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->firstOrFail();
            $reservasi->update(['status_kehadiran' => 'hadir']);

            return [
                'success' => true,
                'message' => 'Kehadiran diverifikasi',
                'data'    => $reservasi,
            ];
        } catch (\Exception $e) {
            Log::error('Error verifyAttendance', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Gagal verifikasi kehadiran',
            ];
        }
    }

    /**
     * Generate QR code dengan penanganan error yang lebih baik
     */
    public function generateQRCode(Reservasi $reservasi): string
    {
        $url = URL::route('pelayan.scanqr.proses', $reservasi->kode_reservasi);
        return $this->generateQRCodeSafely($url);
    }

    /**
     * Generate QR code dengan multiple fallback methods
     */
    private function generateQRCodeSafely(string $content): string
    {
        try {
            // Method 1: Gunakan GD driver secara eksplisit
            $png = QrCode::format('png')
                         ->size(150)
                         ->margin(1)
                         ->errorCorrection('M')
                         ->generate($content);

            return 'data:image/png;base64,' . base64_encode($png);

        } catch (\Exception $e1) {
            Log::warning('QR Code generation failed with default method', [
                'error' => $e1->getMessage(),
                'content' => $content
            ]);

            try {
                // Method 2: Force GD driver melalui konfigurasi runtime
                $originalDriver = config('simple-qrcode.driver');
                config(['simple-qrcode.driver' => 'gd']);

                $png = QrCode::format('png')
                             ->size(150)
                             ->generate($content);

                // Restore original config
                config(['simple-qrcode.driver' => $originalDriver]);

                return 'data:image/png;base64,' . base64_encode($png);

            } catch (\Exception $e2) {
                Log::warning('QR Code generation failed with GD override', [
                    'error' => $e2->getMessage()
                ]);

                try {
                    // Method 3: Gunakan SVG sebagai fallback
                    $svg = QrCode::format('svg')
                                 ->size(150)
                                 ->generate($content);

                    return 'data:image/svg+xml;base64,' . base64_encode($svg);

                } catch (\Exception $e3) {
                    Log::error('All QR Code generation methods failed', [
                        'gd_error' => $e1->getMessage(),
                        'override_error' => $e2->getMessage(),
                        'svg_error' => $e3->getMessage(),
                        'content' => $content
                    ]);

                    // Return placeholder QR code URL jika semua gagal
                    return $this->generatePlaceholderQR($content);
                }
            }
        }
    }

    /**
     * Generate placeholder QR code menggunakan service eksternal
     */
    private function generatePlaceholderQR(string $content): string
    {
        // Gunakan QR Server API sebagai fallback
        $encodedContent = urlencode($content);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$encodedContent}";
        
        try {
            $qrImage = file_get_contents($qrUrl);
            if ($qrImage !== false) {
                return 'data:image/png;base64,' . base64_encode($qrImage);
            }
        } catch (\Exception $e) {
            Log::error('Placeholder QR generation failed', ['error' => $e->getMessage()]);
        }

        // Jika semua gagal, return empty data URI
        return 'data:image/png;base64,';
    }
}