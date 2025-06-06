<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reservasi';

    protected $casts = [
        'combined_tables'          => 'array',
        'payment_amount'           => 'float',
        'total_bill'               => 'float',
        'amount_paid'              => 'float',
        'change_given'             => 'float',
        'sisa_tagihan_reservasi'   => 'float',
        'waktu_kedatangan'         => 'datetime',
        'waktu_selesai'            => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        // 'meja_id',            // tidak lagi secara langsung diisi, karena multi-meja → gunakan pivot
        'combined_tables',
        'nama_pelanggan',
        'staff_id',
        'waktu_kedatangan',
        'jumlah_tamu',
        'kehadiran_status',
        'status',
        'payment_token',
        'payment_amount',
        'payment_status',
        'source',
        'kode_reservasi',
        'catatan',
        'created_by_pelayan_id',
        'total_bill',
        'payment_method',
        'sisa_tagihan_reservasi',
        'amount_paid',
        'change_given',
        'waktu_selesai',
    ];

    /**
     * Relasi many-to-many ke Meja (pivot meja_reservasi).
     */
    public function meja()
    {
        return $this->belongsToMany(Meja::class, 'meja_reservasi', 'reservasi_id', 'meja_id')
                    ->withTimestamps();
    }

    // Jika Anda juga membutuhkan relasi individual (sebelum diubah ke pivot), bisa dihapus atau dikomentari:
    /*
    public function mejaSingle()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }
    */

    // Relasi ke Pengguna (Pelanggan)
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    // Relasi ke Pengguna (Staf/Pelayan yang membuat)
    public function staffYangMembuat()
    {
        return $this->belongsTo(Pengguna::class, 'staff_id');
    }

    // Relasi ke Order items jika ada
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
