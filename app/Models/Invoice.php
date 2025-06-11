<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservasi_id',
        'invoice_number',
        'subtotal',
        'service_fee',
        'total_amount',
        'amount_paid',
        'remaining_amount',
        'payment_method',
        'payment_status',
        'generated_at'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2'
    ];

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
                          ->orderBy('id', 'desc')
                          ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted invoice data for frontend
     */
    public function getFormattedData()
    {
        $reservasi = $this->reservasi()->with(['meja', 'user'])->first();
        $orders = Order::where('reservasi_id', $this->reservasi_id)->with('menu')->get();

        return [
            'invoice' => [
                'id' => $this->id,
                'invoice_number' => $this->invoice_number,
                'generated_at' => $this->generated_at->format('Y-m-d H:i:s'),
                'subtotal' => $this->subtotal,
                'service_fee' => $this->service_fee,
                'total_amount' => $this->total_amount,
                'amount_paid' => $this->amount_paid,
                'remaining_amount' => $this->remaining_amount,
                'payment_method' => $this->payment_method,
                'payment_status' => $this->payment_status,
            ],
            'reservasi' => [
                'id' => $reservasi->id,
                'kode_reservasi' => $reservasi->kode_reservasi,
                'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                'jumlah_tamu' => $reservasi->jumlah_tamu,
                'nama_pelanggan' => $reservasi->nama_pelanggan,
                'catatan' => $reservasi->catatan,
                'status' => $reservasi->status,
                'status_kehadiran' => $reservasi->status_kehadiran,
                'waktu_checkin' => $reservasi->waktu_checkin,
                'meja' => $reservasi->meja->map(function($meja) {
                    return [
                        'id' => $meja->id,
                        'nomor_meja' => $meja->nomor_meja,
                        'area' => $meja->area
                    ];
                })
            ],
            'customer' => [
                'nama' => $reservasi->user->nama ?? $reservasi->nama_pelanggan,
                'email' => $reservasi->user->email ?? '',
                'telp' => $reservasi->user->nomor_hp ?? ''
            ],
            'items' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'nama' => $order->menu->name ?? 'Menu tidak tersedia',
                    'quantity' => $order->quantity,
                    'harga' => $order->price_at_order,
                    'total' => $order->total_price,
                    'note' => $order->notes ?? ''
                ];
            }),
            'attendance' => [
                'status' => $reservasi->status_kehadiran,
                'waktu_checkin' => $reservasi->waktu_checkin,
                'can_checkin' => $this->canCheckIn($reservasi),
            ]
        ];
    }

    /**
     * Check if reservation can do check-in
     */
    private function canCheckIn($reservasi): bool
    {
        if ($reservasi->status_kehadiran === 'hadir') {
            return false;
        }

        $now = now();
        $waktuKedatangan = \Carbon\Carbon::parse($reservasi->waktu_kedatangan);
        $batasCheckin = $waktuKedatangan->copy()->subMinutes(30);
        $batasExpired = $waktuKedatangan->copy()->addHours(2);

        return $now->gte($batasCheckin) && $now->lte($batasExpired);
    }

    /**
     * Get payment status with color coding
     */
    public function getPaymentStatusAttribute()
    {
        return [
            'status' => $this->attributes['payment_status'],
            'label' => $this->getPaymentStatusLabel(),
            'color' => $this->getPaymentStatusColor(),
        ];
    }

    /**
     * Get payment status label
     */
    private function getPaymentStatusLabel(): string
    {
        return match($this->attributes['payment_status']) {
            'pending' => 'Menunggu Pembayaran',
            'partial' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get payment status color
     */
    private function getPaymentStatusColor(): string
    {
        return match($this->attributes['payment_status']) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            default => 'secondary'
        };
    }
}