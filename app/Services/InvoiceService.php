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
            $reservasi = Reservasi::with(['meja', 'orders.menu'])->findOrFail($reservasiId);

            // Hitung subtotal dari order, menggunakan price_at_order jika ada, atau fallback ke menu.price/harga
            $subtotal = $reservasi->orders->sum(function ($order) use ($reservasiId) {
                $price = $order->price_at_order
                    ?? ($order->menu->price ?? $order->menu->harga ?? 0);
                $quantity = $order->quantity ?? 0;

                if (($price == 0 || $quantity == 0) && $order->id) {
                    Log::warning('Order item bermasalah saat generate invoice', [
                        'reservasi_id'   => $reservasiId,
                        'order_id'       => $order->id,
                        'price_at_order' => $order->price_at_order,
                        'menu_price'     => $order->menu->price ?? $order->menu->harga ?? null,
                        'quantity'       => $order->quantity,
                    ]);
                }
                return $price * $quantity;
            });

            $serviceFee  = round($subtotal * 0.10); // 10% biaya layanan
            $totalAmount = $subtotal + $serviceFee;
            $amountPaid  = 0;
            $remaining   = $totalAmount - $amountPaid;

            // Nomor invoice unik
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            // QR Code untuk keperluan invoice atau referensi (jika diperlukan)
            // Misal route pelayan.scanqr.proses menerima parameter kode_reservasi
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
                    // Pastikan kolom 'qr_code' ada di tabel invoices; jika tidak, sesuaikan atau hapus baris ini
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
                'trace'        => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update status pembayaran pada invoice.
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
     * Verifikasi kehadiran berdasarkan kode reservasi.
     */
    public function verifyAttendance(string $kodeReservasi): array
    {
        try {
            $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->firstOrFail();

            // Cek apakah sudah hadir
            if (($reservasi->status_kehadiran ?? null) === 'hadir') {
                return [
                    'success' => false,
                    'message' => 'Kehadiran sudah diverifikasi sebelumnya',
                    'data'    => $reservasi,
                ];
            }

            // Cek waktu kedatangan: misal 30 menit sebelum sampai 2 jam setelah
            $now = now();
            $waktuKedatangan = \Carbon\Carbon::parse($reservasi->waktu_kedatangan);
            $batasAwal = $waktuKedatangan->copy()->subMinutes(30);
            $batasAkhir = $waktuKedatangan->copy()->addHours(2);

            if ($now->lt($batasAwal)) {
                return [
                    'success' => false,
                    'message' => 'Belum saatnya untuk check-in. Silakan datang 30 menit sebelum waktu reservasi.',
                    'data'    => $reservasi,
                ];
            }
            if ($now->gt($batasAkhir)) {
                return [
                    'success' => false,
                    'message' => 'Waktu check-in telah berakhir.',
                    'data'    => $reservasi,
                ];
            }

            // Tandai kehadiran
            $reservasi->update([
                'status_kehadiran' => 'hadir',
                'waktu_checkin'    => $now,
            ]);

            return [
                'success' => true,
                'message' => 'Kehadiran berhasil diverifikasi',
                'data'    => $reservasi,
            ];
        } catch (\Exception $e) {
            Log::error('Error verifyAttendance', [
                'kode_reservasi' => $kodeReservasi,
                'error'          => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Gagal verifikasi kehadiran: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate QR code sederhana untuk keperluan tampilan/invoice.
     * Dipanggil di generateInvoice atau saat dibutuhkan: generateQRCode($reservasi).
     */
    public function generateQRCode(Reservasi $reservasi): string
    {
        $data = [
            'reservasi_id'    => $reservasi->id,
            'kode_reservasi'  => $reservasi->kode_reservasi,
            'user_id'         => $reservasi->user_id,
            'timestamp'       => now()->timestamp,
            'random'          => Str::random(16),
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Validasi token QR code presensi (jika digunakan mekanisme token).
     */
    public function validateAttendanceToken(string $kodeReservasi, string $token): bool
    {
        try {
            $reservasi = Reservasi::where('kode_reservasi', $kodeReservasi)->first();
            if (!$reservasi) {
                return false;
            }
            // Validasi sederhana: panjang 64 dan hex
            return strlen($token) === 64 && ctype_xdigit($token);
        } catch (\Exception $e) {
            Log::error('Error validating attendance token', [
                'kode_reservasi' => $kodeReservasi,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate QR Code image base64 dengan beberapa fallback.
     */
    private function generateQRCodeSafely(string $content): string
    {
        try {
            $png = QrCode::format('png')
                         ->size(200)
                         ->margin(2)
                         ->errorCorrection('H')
                         ->generate($content);

            return 'data:image/png;base64,' . base64_encode($png);
        } catch (\Exception $e1) {
            Log::warning('QR Code generation failed with default method', [
                'error'   => $e1->getMessage(),
                'content' => $content,
            ]);
            // Fallback ke GD driver
            try {
                $originalDriver = config('simple-qrcode.driver');
                config(['simple-qrcode.driver' => 'gd']);
                $png = QrCode::format('png')
                             ->size(200)
                             ->margin(2)
                             ->errorCorrection('H')
                             ->generate($content);
                config(['simple-qrcode.driver' => $originalDriver]);
                return 'data:image/png;base64,' . base64_encode($png);
            } catch (\Exception $e2) {
                Log::warning('QR Code generation failed with GD override', [
                    'error' => $e2->getMessage(),
                ]);
                // Fallback ke SVG
                try {
                    $svg = QrCode::format('svg')
                                 ->size(200)
                                 ->margin(2)
                                 ->errorCorrection('H')
                                 ->generate($content);
                    return 'data:image/svg+xml;base64,' . base64_encode($svg);
                } catch (\Exception $e3) {
                    Log::error('All QR Code generation methods failed', [
                        'error_default'  => $e1->getMessage(),
                        'error_gd'       => $e2->getMessage(),
                        'error_svg'      => $e3->getMessage(),
                        'content'        => $content,
                    ]);
                    return $this->generatePlaceholderQR($content);
                }
            }
        }
    }

    /**
     * Jika semua metode QR gagal, generate placeholder via API eksternal.
     */
    private function generatePlaceholderQR(string $content): string
    {
        $encodedContent = urlencode($content);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=H&data={$encodedContent}";
        try {
            $qrImage = @file_get_contents($qrUrl);
            if ($qrImage !== false) {
                return 'data:image/png;base64,' . base64_encode($qrImage);
            }
        } catch (\Exception $e) {
            Log::error('Placeholder QR generation failed', ['error' => $e->getMessage()]);
        }
        // Kembalikan string kosong atau placeholder minimal
        return 'data:image/png;base64,';
    }

    /**
     * Dapatkan payload QR untuk Ionic atau endpoint lain.
     */
    public function getQrPayload(int $reservasiId): array
    {
        $reservasi = Reservasi::findOrFail($reservasiId);
        return [
            'kode_reservasi' => $reservasi->kode_reservasi,
        ];
    }
}
