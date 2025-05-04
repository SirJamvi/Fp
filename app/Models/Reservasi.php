<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservasi extends Model
{
    use HasFactory;

    // Tambahkan ini untuk menentukan nama tabel yang benar
    protected $table = 'reservasi';

    protected $fillable = [
        'pengguna_id',
        'meja_id',
        'waktu_kedatangan',
        'jumlah_tamu',
        'status',
        'kode_reservasi',
        'catatan'
    ];

    // Casting untuk tipe data khusus
    protected $casts = [
        'waktu_kedatangan' => 'datetime',
        'jumlah_tamu' => 'integer'
    ];

    // Relasi ke model Pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class);
    }

    // Relasi ke model Meja
    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    // Scope untuk query reservasi hari ini
    public function scopeHariIni($query)
    {
        return $query->whereDate('waktu_kedatangan', Carbon::today());
    }
}