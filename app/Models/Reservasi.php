<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    protected $fillable = [
        'user_id',
        'meja_id',
        'waktu_kedatangan',
        'jumlah_tamu',
        'status',
        'kode_reservasi',
        'catatan'
    ];

    public function scopeHariIni($query)
    {
        return $query->whereDate('waktu_kedatangan', Carbon::today());
    }

    public function staff()
    {
    return $this->belongsTo(Staff::class);
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id', 'id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id', 'id');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaction::class, 'reservasi_id');
    }
}

