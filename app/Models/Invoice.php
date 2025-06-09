<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'qr_code',
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
                'qr_code' => $this->qr_code
            ],
            'reservasi' => [
                'id' => $reservasi->id,
                'kode_reservasi' => $reservasi->kode_reservasi,
                'waktu_kedatangan' => $reservasi->waktu_kedatangan,
                'jumlah_tamu' => $reservasi->jumlah_tamu,
                'nama_pelanggan' => $reservasi->nama_pelanggan,
                'catatan' => $reservasi->catatan,
                'status' => $reservasi->status,
                'kehadiran_status' => $reservasi->kehadiran_status,
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
            })
        ];
    }
}