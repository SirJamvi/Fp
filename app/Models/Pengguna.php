<?php

// app/Models/Pengguna.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'email',
        'nomor_hp',
        'password',
        'peran',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Cek apakah pengguna adalah admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->peran === 'admin';
    }

    /**
     * Relasi ke tabel reservasi.
     * Seorang pengguna bisa memiliki banyak reservasi.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservasi::class, 'staff_id', 'id');
    }
    
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'user_id', 'id');
    }        
    public function reservasiDilayani()
    {
        return $this->hasMany(Reservasi::class, 'staff_id', 'id');
    }

}
