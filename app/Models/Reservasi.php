<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pengguna; // Asumsi model user/pelayan Anda bernama Pengguna
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservasi extends Model
{

    use SoftDeletes;

    protected $table = 'reservasi';

    protected $casts = [
        'combined_tables' => 'array',
        'payment_amount' => 'float',
        'total_bill' => 'float',
        'amount_paid' => 'float',
        'change_given' => 'float',
        'sisa_tagihan_reservasi' => 'float',
        'waktu_kedatangan' => 'datetime',
        'waktu_selesai' => 'datetime',
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


    // Relasi ke Pengguna (Pelanggan) - Asumsi user_id merujuk ke pelanggan terdaftar
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    // Relasi ke Meja
    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    // Relasi ke Order items
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Relasi ke Pengguna (Staf/Pelayan yang membuat reservasi) - Asumsi staff_id merujuk ke user pelayan
   public function staffYangMembuat()
{
    return $this->belongsTo(Pengguna::class, 'staff_id');
}
    

    // Jika Anda memiliki relasi lain, biarkan tetap
}
