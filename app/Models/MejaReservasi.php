<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MejaReservasi extends Model
{
    protected $table = 'meja_reservasi'; // pastikan nama tabelnya sesuai

    protected $fillable = [
        'meja_id',
        'reservasi_id',
    ];

    public $timestamps = false;

    /**
     * Relasi ke meja (many-to-one)
     */
    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    /**
     * Relasi ke reservasi (many-to-one)
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }
}
