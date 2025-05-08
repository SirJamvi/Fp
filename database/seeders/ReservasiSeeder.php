<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservasi;

class ReservasiSeeder extends Seeder
{
    public function run(): void
    {
        Reservasi::create([
            'order_id' => 'ORD001',
            'nama' => 'Andi Saputra',
            'detail' => '2 orang, dekat jendela',
            'tanggal' => now()->addDays(2),
            'meja' => 'A1',
            'status' => 'Complete',
        ]);

        Reservasi::create([
            'order_id' => 'ORD002',
            'nama' => 'Rina Marlina',
            'detail' => '4 orang, ulang tahun',
            'tanggal' => now()->addDays(5),
            'meja' => 'B2',
            'status' => 'Cancelled',
        ]);

        // Tambahkan lebih banyak jika perlu
    }
}
