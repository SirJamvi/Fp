<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Reservasi;
use App\Models\Order;
// Removed Payment import since table doesn't exist
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceService
{
    public function generateInvoice($reservasiId)
    {
        try {
            // Cek invoice sudah ada
            $existing = Invoice::where('reservasi_id', $reservasiId)->first();
            if ($existing) {
                return [
                    'success' => true,
                    'message' => 'Invoice sudah ada',
                    'data' => $existing->getFormattedData()
                ];
            }

            // Ambil reservasi dengan relasi
            $reservasi = Reservasi::with(['meja', 'orders.menu'])->findOrFail($reservasiId);

            // Hitung subtotal dari orders
            $orders = $reservasi->orders;
            $subtotal = $orders->sum('total_price');

            // Biaya layanan
            $serviceFee = $reservasi->service_fee ?? 9000;
            $totalAmount = $subtotal + $serviceFee;

            // Gunakan nilai dari field reservasi (misal amount_paid)
            $totalPaid = $reservasi->amount_paid ?? 0;

            // Method dan status pembayaran dari reservasi
            $paymentMethod = $reservasi->payment_method ?? 'cash';
            $paymentStatus = $this->determinePaymentStatus($totalAmount, $totalPaid);

            // Buat invoice
            $invoice = Invoice::create([
                'reservasi_id'     => $reservasiId,
                'invoice_number'   => Invoice::generateInvoiceNumber(),
                'subtotal'         => $subtotal,
                'service_fee'      => $serviceFee,
                'total_amount'     => $totalAmount,
                'amount_paid'      => $totalPaid,
                'remaining_amount' => max(0, $totalAmount - $totalPaid),
                'payment_method'   => $paymentMethod,
                'payment_status'   => $paymentStatus,
                'qr_code'          => $this->generateQRCode($reservasi),
                'generated_at'     => Carbon::now()
            ]);

            Log::info('Invoice generated', ['id' => $invoice->id, 'reservasi' => $reservasiId]);

            return [
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data'    => $invoice->getFormattedData()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate invoice', [
                'reservasi_id' => $reservasiId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage(),
                'data'    => null
            ];
        }
    }

    public function generateQRCode($reservasi)
    {
        try {
            $qrData = [
                'type'             => 'reservation_attendance',
                'kode_reservasi'   => $reservasi->kode_reservasi,
                'reservasi_id'     => $reservasi->id,
                'nama_pelanggan'   => $reservasi->nama_pelanggan,
                'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                'timestamp'        => now()->timestamp,
                'verification_url' => config('app.url') . '/api/customer/verify-attendance/' . $reservasi->kode_reservasi,
                'hash'             => hash('sha256', $reservasi->kode_reservasi . $reservasi->id . config('app.key'))
            ];

            $png = QrCode::format('png')
                         ->size(200)
                         ->margin(1)
                         ->generate(json_encode($qrData));

            return 'data:image/png;base64,' . base64_encode($png);

        } catch (\Exception $e) {
            Log::error('QR generation failed', ['reservasi_id' => $reservasi->id, 'error' => $e->getMessage()]);
            return '';
        }
    }

    private function determinePaymentStatus($total, $paid)
    {
        if ($paid >= $total) return 'paid';
        if ($paid > 0) return 'partial';
        return 'unpaid';
    }
}
