<?php

// database/seeders/AdminSeeder.php
namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Pengguna::create([
            'nama' => 'Admin Restoran',
            'email' => 'admin@restoran.com',
            'nomor_hp' => '081234567890',
            'password' => Hash::make('password123'), // pastikan password di-hash
            'peran' => 'admin', // pastikan peran admin
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
