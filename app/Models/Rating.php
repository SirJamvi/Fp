<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings'; // Nama tabel

    protected $fillable = [
        'user_id', // Foreign key ke tabel pengguna
        'rating',      // Nilai rating
        'komentar',    // Komentar rating (opsional)
    ];

    /**
     * Relasi ke tabel pengguna.
     * Rating ini dimiliki oleh satu pengguna.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id', 'id');
    }
}
