<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    use HasFactory;

    protected $table = 'customer_notifications'; // Nama tabel kustom untuk notifikasi pelanggan

    protected $fillable = [
        'user_id',
        'type', // Misalnya: 'reservation_confirmation', 'order_status_update', 'promo'
        'data', // Data notifikasi dalam bentuk JSON
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Relasi ke pengguna
    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }
}