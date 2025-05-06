<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed admin user
        DB::table('pengguna')->insert([
            'nama' => 'Admin Restoran',
            'email' => '    ',
            'nomor_hp' => '081234567890',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Seed sample menu items
        DB::table('menu')->insert([
            [
                'nama' => 'Nasi Goreng Spesial',
                'deskripsi' => 'Nasi goreng dengan telur, ayam, dan sayuran',
                'harga' => 35000,
                'kategori' => 'Makanan',
                'tersedia' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Es Teh Manis',
                'deskripsi' => 'Es teh dengan gula',
                'harga' => 10000,
                'kategori' => 'Minuman',
                'tersedia' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Seed sample tables
        DB::table('meja')->insert([
            [
                'nomor_meja' => 'A1',
                'area' => 'Indoor',
                'kapasitas' => 4,
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nomor_meja' => 'B2',
                'area' => 'Outdoor',
                'kapasitas' => 6,
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}