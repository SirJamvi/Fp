<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pengguna;    // Import model Pengguna
use App\Models\Meja;
use App\Models\Order;
use App\Models\Invoice;

class Reservasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reservasi';

    protected $casts = [
        'combined_tables'        => 'array',
        'payment_amount'         => 'float',
        'total_bill'             => 'float',
        'amount_paid'            => 'float',
        'change_given'           => 'float',
        'sisa_tagihan_reservasi' => 'float',
        'waktu_kedatangan'       => 'datetime',
        'waktu_selesai'          => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'meja_id',
        'combined_tables',
        'nama_pelanggan',
        'staff_id', // Ini adalah kolom foreign key yang akan digunakan oleh staffYangMembuat
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
        'created_by_pelayan_id', // Perhatikan apakah ini juga merujuk ke staff/pengguna yang membuat reservasi
        'total_bill',
        'payment_method',
        'sisa_tagihan_reservasi',
        'amount_paid',
        'change_given',
        'waktu_selesai',
    ];

    /**
     * Relasi ke pengguna yang membuat reservasi (customer)
     */
    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    /**
     * Alias: supaya eager-load('pengguna') bekerja
     */
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    /**
     * Alias untuk konteks invoice: customer
     */
    public function customer()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    /**
     * Relasi ke staf yang membuat (pelayan)
     * Mengganti nama method 'staff' menjadi 'staffYangMembuat'
     * agar sesuai dengan pemanggilan di PelayanController.php
     */
    public function staffYangMembuat() // NAMA INI HARUS SAMA DENGAN YANG DIPANGGIL DI CONTROLLER
    {
        // Asumsi 'staff_id' adalah foreign key yang merujuk ke ID pengguna/staff yang membuat reservasi
        return $this->belongsTo(Pengguna::class, 'staff_id');
        // Jika Anda juga memiliki 'created_by_pelayan_id' yang merujuk ke staff,
        // dan itu yang dimaksud 'staffYangMembuat', maka ganti 'staff_id' menjadi 'created_by_pelayan_id'
        // return $this->belongsTo(Pengguna::class, 'created_by_pelayan_id');
    }

    /**
     * Relasi many-to-many ke meja
     */
    public function meja()
    {
        return $this->belongsToMany(Meja::class, 'meja_reservasi', 'reservasi_id', 'meja_id')
                     ->withTimestamps();
    }

    /**
     * Relasi ke order items
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relasi one-to-one ke invoice
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'reservasi_id');
    }
}