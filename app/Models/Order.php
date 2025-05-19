<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservasi_id', // Tambahkan ini jika belum ada
        'menu_id',
        'user_id', // Bisa user pelanggan atau pelayan yang input
        'quantity',
        'price_at_order', // Harga menu saat order
        'total_price',    // Total harga untuk item ini (quantity * price_at_order)
        'notes',          // Catatan untuk item
        'status',         // Status pesanan (mis., pending, confirmed, preparing, ready, served, paid)
    ];

    /**
     * Relasi ke model Menu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    /**
     * Relasi ke model User.
     * Ini bisa merujuk ke pelanggan yang memesan atau pelayan yang menginput.
     * Anda mungkin perlu menamai relasi ini lebih spesifik jika ada keduanya.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke model Reservasi.
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }
}