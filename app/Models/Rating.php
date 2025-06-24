<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = [
        'user_id',
        'reservasi_id',
        'rating',
        'rating_makanan',
        'rating_pelayanan', 
        'rating_aplikasi',
        'komentar',
        'staff_id'
    ];

    protected $casts = [
        'rating' => 'float',
        'rating_makanan' => 'integer',
        'rating_pelayanan' => 'integer',
        'rating_aplikasi' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel pengguna (customer yang memberi rating).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id', 'id');
    }

    /**
     * Alias untuk user (sama dengan pengguna)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id', 'id');
    }

    /**
     * Relasi ke tabel reservasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    /**
     * Relasi ke staff (opsional)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(Pengguna::class, 'staff_id', 'id');
    }
}