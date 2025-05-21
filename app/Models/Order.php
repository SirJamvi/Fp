<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'reservasi_id',
        'menu_id',
        'user_id',
        'quantity',
        'price_at_order',
        'total_price',
        'notes',
        'status',
    ];

    /**
     * Event yang akan dijalankan setelah order berhasil dibuat.
     * Digunakan untuk menyimpan data ke tabel transactions secara otomatis.
     */
    protected static function booted()
    {
        static::created(function ($order) {
            // Pastikan relasi menu tersedia
            $menu = $order->menu()->first();

            Transaction::create([
                'reservasi_id' => $order->reservasi_id,
                'menu_id'      => $order->menu_id,
                'item_name'    => $menu ? $menu->name : 'Unknown',
                'quantity'     => $order->quantity,
                'total_price'  => $order->total_price,
                'status'       => 'belum dibayar',
            ]);
        });
    }

    // Relasi ke Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    // Relasi ke User (pelanggan atau pelayan)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relasi ke Reservasi
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }
}
