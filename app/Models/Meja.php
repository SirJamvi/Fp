<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    // Tambahkan ini untuk menentukan nama tabel yang benar
    protected $table = 'meja';

    protected $fillable = [
        'nomor_meja',
        'area',
        'kapasitas',
        'status'
    ];

    // Jika ingin meng-cast status sebagai enum
    protected $casts = [
        'status' => 'string'
    ];

    // Contoh relasi jika diperlukan nanti
    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }
}