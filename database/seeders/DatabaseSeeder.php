<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Admin
        DB::table('pengguna')->insert([
            [
                'nama' => 'Admin Restoran',
                'email' => 'admin@restoran.com',
                'nomor_hp' => '081234567899',
                'password' => Hash::make('password123'),
                'peran' => 'admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Buat Menu (jika tabel menu ada)
        // DB::table('menus')->insert([
        //     [
        //         'nama_menu' => 'Nasi Goreng Special',
        //         'harga' => 35000,
        //         'kategori' => 'makanan',
        //         'deskripsi' => 'Nasi goreng dengan telur, ayam, dan seafood',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'nama_menu' => 'Es Teh Manis',
        //         'harga' => 8000,
        //         'kategori' => 'minuman',
        //         'deskripsi' => 'Es teh dengan gula aren',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]
        // ]);

        // Buat Pelanggan
        DB::table('pengguna')->insert([
            [
                'nama' => 'Pelanggan 1',
                'email' => 'pelanggan1@example.com',
                'nomor_hp' => '081234567800',
                'password' => Hash::make('password'),
                'peran' => 'pelanggan',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Buat Staff: Pelayan & Koki
        DB::table('pengguna')->insert([
            [
                'nama' => 'Pelayan 1',
                'email' => 'pelayan1@example.com',
                'nomor_hp' => '081234567801',
                'password' => Hash::make('password'),
                'peran' => 'pelayan',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Koki 1',
                'email' => 'koki1@example.com',
                'nomor_hp' => '081234567802',
                'password' => Hash::make('password'),
                'peran' => 'koki',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Buat Meja
        DB::table('meja')->insert([
            [
                'nomor_meja' => 'A1',
                'kapasitas' => 4,
                'status' => 'tersedia',
                'area' => 'indoor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nomor_meja' => 'B2',
                'kapasitas' => 6,
                'status' => 'tersedia',
                'area' => 'outdoor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Ambil data untuk relasi
        $user = DB::table('pengguna')->where('peran', 'pelanggan')->first();
        $userId = $user ? $user->id : 1;
        
        $meja = DB::table('meja')->first();
        $mejaId = $meja ? $meja->id : 1;
        
        $staff = DB::table('pengguna')->whereIn('peran', ['pelayan', 'koki'])->inRandomOrder()->first();
        $staffId = $staff ? $staff->id : null;

        // Buat Reservasi
        DB::table('reservasi')->insert([
            [
                'user_id' => $userId,
                'meja_id' => $mejaId,
                'staff_id' => $staffId,
                'waktu_kedatangan' => now()->addDays(2),
                'jumlah_tamu' => 4,
                'status' => 'dipesan',
                'kode_reservasi' => strtoupper(Str::random(6)),
                'catatan' => 'Minta meja dekat jendela',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}