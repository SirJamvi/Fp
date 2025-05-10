<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StaffPerformanceSeeder extends Seeder
{
    public function run(): void
    {
        // Tambahkan 5 staff
        for ($i = 1; $i <= 5; $i++) {
            $staffId = DB::table('staff')->insertGetId([
                'nama' => "Staff $i",
                'jabatan' => 'pelayan',
                'rating' => rand(30, 50) / 10, // rating antara 3.0 - 5.0
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tambahkan 3-6 reservasi untuk tiap staff
            for ($j = 1; $j <= rand(3, 6); $j++) {
                $pengguna = DB::table('pengguna')->inRandomOrder()->first();
                $userId = $pengguna ? $pengguna->id : 1;

                $meja = DB::table('meja')->inRandomOrder()->first();
                $mejaId = $meja ? $meja->id : 1;

                $staff = DB::table('pengguna')->whereIn('peran', ['pelayan', 'koki'])->inRandomOrder()->first();
                $staffId = $staff ? $staff->id : null;



                DB::table('reservasi')->insert([
                    'user_id' => $userId,
                    'meja_id' => $mejaId,
                    'staff_id' => $staffId, // boleh null jika staff belum ada
                    'waktu_kedatangan' => now()->addDays(2),
                    'jumlah_tamu' => 4,
                    'status' => 'dipesan',
                    'kode_reservasi' => strtoupper(Str::random(6)),
                    'catatan' => 'Minta dekat jendela',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tambahkan rating 1-3 untuk tiap staff
            for ($k = 1; $k <= rand(1, 3); $k++) {
                DB::table('ratings')->insert([
                    'user_id' => $userId,
                    'rating' => rand(3, 5),
                    'komentar' => 'Pelayanan baik.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
