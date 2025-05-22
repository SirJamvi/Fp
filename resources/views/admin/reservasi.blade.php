<x-layout>
  <x-slot:title>{{ $title ?? 'Reservasi' }}</x-slot:title>

  <div class="p-6">
    <h3 class="text-2xl font-semibold mb-4">Reservation</h3>

    @php
      $currentFilter = request('filter');
      $baseUrl = route('admin.reservasi');
      $searchQuery = request('search') ? '&search=' . request('search') : '';
    @endphp

    <div class="flex gap-2 mb-4">
      <a href="{{ $baseUrl }}?filter=all{{ $searchQuery }}"
         class="px-4 py-2 rounded {{ $currentFilter == 'all' || !$currentFilter ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
        All
      </a>
      <a href="{{ $baseUrl }}?filter=week{{ $searchQuery }}"
         class="px-4 py-2 rounded {{ $currentFilter == 'week' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
        This Week
      </a>
      <a href="{{ $baseUrl }}?filter=month{{ $searchQuery }}"
         class="px-4 py-2 rounded {{ $currentFilter == 'month' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
        This Month
      </a>
      <a href="{{ $baseUrl }}?filter=year{{ $searchQuery }}"
         class="px-4 py-2 rounded {{ $currentFilter == 'year' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
        This Year
      </a>
    </div>

    <div class="flex justify-between mb-4">
      <form method="GET" class="flex gap-2 w-full">
        <input type="text" name="search" placeholder="Search by name or code" 
               value="{{ request('search') }}"
               class="border rounded px-3 py-2 w-1/3" />

        @if(request('search') || request('filter'))
          <a href="{{ route('admin.reservasi') }}"
             class="px-3 py-2 border rounded bg-gray-100">Reset</a>
        @endif

        <button type="submit" class="border px-3 py-2 rounded bg-blue-500 text-white">Search</button>
      </form>
    </div>

      <div class="flex gap-2 mt-2">
  <a href="{{ route('admin.reservasi.export.excel', request()->all()) }}"
     class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Export Excel</a>

  <a href="{{ route('admin.reservasi.export.pdf', request()->all()) }}"
     class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Export PDF</a>

  <a href="{{ route('admin.reservasi.export.word', request()->all()) }}"
     class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Export Word</a>
</div>


    @if($reservasis->count() > 0)
      <div class="overflow-auto">
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
                <td class="p-2">{{ $r->nama_pelanggan ?? '—' }}</td>
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
      </div>

      <div class="mt-4">
        {{ $reservasis->withQueryString()->links() }}
      </div>
    @else
      <p class="text-gray-500">Tidak ada data reservasi tersedia.</p>
    @endif
  </div>
</x-layout>
