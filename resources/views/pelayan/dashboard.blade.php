@extends('pelayan.layout.app')

@section('title', 'Dashboard Pelayan')

@section('content')
<div class="space-y-6">
    <!-- Kartu Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Pesanan Aktif -->
        <div class="bg-blue-600 text-white rounded-lg shadow p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm">Pesanan Aktif</p>
                    <p class="text-2xl font-bold">6</p>
                </div>
                <i class="fas fa-clipboard-list fa-2x text-white/70"></i>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <a href="{{ route('pelayan.pesanan') }}" class="underline">Lihat Detail</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>

        <!-- Meja Tersedia -->
        <div class="bg-green-600 text-white rounded-lg shadow p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm">Meja Tersedia</p>
                    <p class="text-2xl font-bold">8</p>
                </div>
                <i class="fas fa-chair fa-2x text-white/70"></i>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <a href="{{ route('pelayan.meja') }}" class="underline">Lihat Detail</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>

        <!-- Pesanan Siap -->
        <div class="bg-yellow-500 text-white rounded-lg shadow p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm">Pesanan Siap</p>
                    <p class="text-2xl font-bold">3</p>
                </div>
                <i class="fas fa-utensils fa-2x text-white/70"></i>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <a href="{{ route('pelayan.pesanan') }}" class="underline">Antar Pesanan</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Pesanan Terbaru -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h5 class="text-lg font-semibold">Pesanan Terbaru</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-gray-100 uppercase text-gray-600 text-xs">
                    <tr>
                        <th class="px-6 py-3">No. Pesanan</th>
                        <th class="px-6 py-3">Meja</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">P-20250507-001</td>
                        <td class="px-6 py-4">Meja 05</td>
                        <td class="px-6 py-4">10:15</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-blue-200 text-blue-800 text-xs rounded">Baru</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs">Detail</a>
                        </td>
                    </tr>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">P-20250507-002</td>
                        <td class="px-6 py-4">Meja 03</td>
                        <td class="px-6 py-4">10:08</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-green-200 text-green-800 text-xs rounded">Siap</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs">Detail</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
