<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nama tabel yang terkait dengan model
     *
     * @var string
     */
    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi secara massal
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'nomor_hp',
        'password',
        'peran',
    ];

    /**
     * Atribut yang harus disembunyikan untuk serialisasi
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus dicast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Cek apakah pengguna adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    /**
     * Cek apakah pengguna adalah pelayan
     */
    public function isPelayan(): bool
    {
        return $this->peran === 'pelayan';
    }
    
    /**
     * Cek apakah pengguna adalah koki
     */
    public function isKoki(): bool
    {
        return $this->peran === 'koki';
    }
}