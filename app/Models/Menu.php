<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    // Tambahkan ini untuk menentukan nama tabel yang benar
    protected $table = 'menu';

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'kategori',
        'tersedia',
        'gambar'
    ];

    protected $casts = [
        'tersedia' => 'boolean',
        'harga' => 'decimal:2'
    ];

    // Jika Anda ingin menambahkan relasi atau method lain
    // Contoh relasi jika menu memiliki kategori terpisah:
    // public function kategori()
    // {
    //     return $this->belongsTo(Kategori::class);
    // }
}