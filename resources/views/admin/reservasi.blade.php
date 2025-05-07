
<x-layout>
  <x-slot:title>{{ $title ?? 'Reservasi' }}</x-slot:title>

  <div class="p-6">
    <h3 class="text-2xl font-semibold mb-4">Reservation</h3>

    <div class="flex gap-2 mb-4">
      <button class="bg-blue-500 text-white px-4 py-2 rounded">All</button>
      <button class="bg-gray-200 px-4 py-2 rounded">This Week</button>
      <button class="bg-gray-200 px-4 py-2 rounded">This Month</button>
      <button class="bg-gray-200 px-4 py-2 rounded">This Year</button>
    </div>

    <div class="flex justify-between mb-4">
      <input type="text" placeholder="Search" class="border rounded px-3 py-2 w-1/3">
      <div>
        <button class="border px-3 py-2 rounded mr-2">Filter</button>
        <button class="border px-3 py-2 rounded">Export</button>
      </div>
    </div>

    @if(isset($reservasis) && $reservasis->count() > 0)
      <table class="min-w-full border">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="p-2">Kode Reservasi</th>
            <th class="p-2">Nama</th>
            <th class="p-2">Catatan</th>
            <th class="p-2">Waktu Kedatangan</th>
            <th class="p-2">Nomor Meja</th>
            <th class="p-2">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($reservasis as $r)
            <tr class="border-t">
              <td class="p-2">{{ $r->kode_reservasi }}</td>
              <td class="p-2">{{ optional($r->pengguna)->nama ?? '—' }}</td>
              <td class="p-2">{{ $r->catatan ?? '-' }}</td>
              <td class="p-2">{{ \Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i') }}</td>
              <td class="p-2">{{ optional($r->meja)->nomor_meja ?? '-' }}</td>
              <td class="p-2">
                @switch($r->status)
                  @case('selesai')
                    <span class="text-green-600 font-semibold">Selesai</span>
                    @break
                  @case('dipesan')
                    <span class="text-yellow-600 font-semibold">Dipesan</span>
                    @break
                  @case('dibatalkan')
                    <span class="text-red-600 font-semibold">Dibatalkan</span>
                    @break
                  @default
                    <span class="text-gray-600 font-semibold">{{ ucfirst($r->status) }}</span>
                @endswitch
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="text-gray-500">Tidak ada data reservasi tersedia.</p>
    @endif
  </div>
</x-layout>
