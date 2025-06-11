<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'meja';

    protected $fillable = [
        'nomor_meja',
        'area',
        'kapasitas',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Relasi many-to-many ke Reservasi (pivot meja_reservasi).
     */
    public function reservasi()
    {
        return $this->belongsToMany(Reservasi::class, 'meja_reservasi', 'meja_id', 'reservasi_id')
                    ->withTimestamps();
    }
    public function reservasiAktif()
{
    return $this->hasOne(Reservasi::class, 'id', 'current_reservasi_id')
                ->where('status', '!=', 'selesai'); // atau sesuaikan kondisi status aktif
}

}
