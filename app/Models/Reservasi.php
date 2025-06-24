<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pengguna;    
use App\Models\Meja;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Rating;

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
     */
    public function staffYangMembuat()
    {
        return $this->belongsTo(Pengguna::class, 'staff_id');
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

    /**
     * Relasi ke rating
     */
    public function rating()
    {
        return $this->hasOne(Rating::class, 'reservasi_id');
    }

    /**
     * Relasi ke semua ratings (jika bisa lebih dari satu)
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'reservasi_id');
    }

    public function mejaReservasi()
    {
        return $this->hasMany(MejaReservasi::class);
    }

    /**
     * Relasi ke 1 meja utama (kolom meja_id)
     */
    public function mejaUtama()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    /**
     * Check apakah reservasi sudah diberi rating
     */
    public function hasRating()
    {
        return $this->rating()->exists();
    }
}