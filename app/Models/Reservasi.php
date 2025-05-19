<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pengguna; // Asumsi model user/pelayan Anda bernama Pengguna

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi'; // Pastikan nama tabelnya benar

    protected $fillable = [
    'kode_reservasi',
    'user_id',
    'meja_id',
    'staff_id',
    'nama_pelanggan',
    'jumlah_tamu',
    'kehadiran_status',
    'waktu_kedatangan',
    'waktu_selesai',
    'status',
    'catatan_khusus',
    'total_bill',
    'payment_method',
    'amount_paid',
    'change_given',
    'source',
    'combined_tables', // ✅ Tambahan baru
    ];

    protected $casts = [
        'waktu_kedatangan' => 'datetime',
        'waktu_selesai' => 'datetime',
        'total_bill' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'source' => 'string',
        'kehadiran_status' => 'string',
        'combined_tables' => 'array', // ✅ Cast field JSON ke array
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
