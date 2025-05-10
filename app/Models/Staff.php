<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'staff';

    // Field yang dapat diisi
    protected $fillable = [
        'nama',
        'jabatan',
        'rating',
    ];

    // Relasi ke model Reservasi (Staf melayani reservasi)
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'staff_id');
    }
}
