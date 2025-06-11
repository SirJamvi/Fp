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

            // Simpan atau update invoice (QR code akan di-generate dinamis)
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
            
            // Cek apakah sudah hadir
            if ($reservasi->status_kehadiran === 'hadir') {
                return [
                    'success' => false,
                    'message' => 'Kehadiran sudah diverifikasi sebelumnya',
                    'data'    => $reservasi,
                ];
            }

            // Cek apakah waktu kedatangan sudah lewat atau belum saatnya
            $now = now();
            $waktuKedatangan = \Carbon\Carbon::parse($reservasi->waktu_kedatangan);
            $batasWaktu = $waktuKedatangan->copy()->addHours(2); // Toleransi 2 jam setelah waktu kedatangan

            if ($now->lt($waktuKedatangan->subMinutes(30))) { // 30 menit sebelum waktu kedatangan
                return [
                    'success' => false,
                    'message' => 'Belum saatnya untuk check-in. Silakan datang 30 menit sebelum waktu reservasi.',
                    'data'    => $reservasi,
                ];
            }

            if ($now->gt($batasWaktu)) {
                return [
                    'success' => false,
                    'message' => 'Waktu check-in telah berakhir.',
                    'data'    => $reservasi,
                ];
            }

            $reservasi->update([
                'status_kehadiran' => 'hadir',
                'waktu_checkin' => $now
            ]);

            return [
                'success' => true,
                'message' => 'Kehadiran berhasil diverifikasi',
                'data'    => $reservasi,
            ];
        } catch (\Exception $e) {
            Log::error('Error verifyAttendance', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Gagal verifikasi kehadiran: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate QR code dinamis untuk presensi
     */
    public function generateAttendanceQRCode(Reservasi $reservasi): array
    {
        try {
            // Generate token unik untuk keamanan
            $token = $this->generateAttendanceToken($reservasi);
            
            // Data yang akan di-encode dalam QR code
            $qrData = [
                'type' => 'attendance',
                'kode_reservasi' => $reservasi->kode_reservasi,
                'token' => $token,
                'timestamp' => now()->timestamp,
                'expires_at' => now()->addMinutes(30)->timestamp, // QR code berlaku 30 menit
            ];

            // URL untuk verifikasi kehadiran
            $verificationUrl = URL::route('pelayan.scanqr.proses', [
                'kode_reservasi' => $reservasi->kode_reservasi,
                'token' => $token
            ]);

            // Generate QR code
            $qrCodeBase64 = $this->generateQRCodeSafely($verificationUrl);

            return [
                'success' => true,
                'message' => 'QR Code presensi berhasil dibuat',
                'data' => [
                    'qr_code_base64' => $qrCodeBase64,
                    'verification_url' => $verificationUrl,
                    'token' => $token,
                    'expires_at' => now()->addMinutes(30)->toISOString(),
                    'qr_data' => $qrData
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error generating attendance QR code', [
                'reservasi_id' => $reservasi->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal membuat QR code presensi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate token keamanan untuk QR code presensi
     */
    private function generateAttendanceToken(Reservasi $reservasi): string
    {
        $data = [
            'reservasi_id' => $reservasi->id,
            'kode_reservasi' => $reservasi->kode_reservasi,
            'user_id' => $reservasi->user_id,
            'timestamp' => now()->timestamp,
            'random' => Str::random(16)
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Validasi token QR code presensi
     */
    public function validateAttendanceToken(string $kodeReservasi, string $token): bool
    {
        try {
            $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();
            if (!$reservasi) {
                return false;
            }

            // Untuk validasi sederhana, kita bisa menyimpan token di cache atau database
            // Untuk saat ini, kita akan validasi berdasarkan format token
            return strlen($token) === 64 && ctype_xdigit($token);
        } catch (\Exception $e) {
            Log::error('Error validating attendance token', [
                'kode_reservasi' => $kodeReservasi,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate QR code dengan multiple fallback methods
     */
    private function generateQRCodeSafely(string $content): string
    {
        try {
            // Method 1: Gunakan GD driver secara eksplisit
            $png = QrCode::format('png')
                         ->size(200)
                         ->margin(2)
                         ->errorCorrection('H') // High error correction untuk presensi
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
                             ->size(200)
                             ->margin(2)
                             ->errorCorrection('H')
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
                                 ->size(200)
                                 ->margin(2)
                                 ->errorCorrection('H')
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
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=H&data={$encodedContent}";
        
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

    /**
     * Get status presensi reservasi
     */
    public function getAttendanceStatus(int $reservasiId): array
    {
        try {
            $reservasi = Reservasi::findOrFail($reservasiId);
            
            $now = now();
            $waktuKedatangan = \Carbon\Carbon::parse($reservasi->waktu_kedatangan);
            $batasCheckin = $waktuKedatangan->copy()->subMinutes(30);
            $batasExpired = $waktuKedatangan->copy()->addHours(2);

            $status = 'not_available';
            $message = '';

            if ($reservasi->status_kehadiran === 'hadir') {
                $status = 'checked_in';
                $message = 'Sudah melakukan check-in';
            } elseif ($now->lt($batasCheckin)) {
                $status = 'too_early';
                $message = 'Belum saatnya check-in';
            } elseif ($now->gt($batasExpired)) {
                $status = 'expired';
                $message = 'Waktu check-in telah berakhir';
            } else {
                $status = 'available';
                $message = 'Dapat melakukan check-in';
            }

            return [
                'success' => true,
                'data' => [
                    'status' => $status,
                    'message' => $message,
                    'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                    'waktu_checkin' => $reservasi->waktu_checkin,
                    'batas_checkin_mulai' => $batasCheckin->toISOString(),
                    'batas_checkin_berakhir' => $batasExpired->toISOString(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error getting attendance status', [
                'reservasi_id' => $reservasiId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal mendapatkan status presensi',
            ];
        }
    }
}