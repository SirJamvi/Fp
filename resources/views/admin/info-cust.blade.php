<x-layout>
  {{-- Slot untuk <title> --}}
  <x-slot:title>{{ $title ?? 'Info Customer' }}</x-slot:title>

  <div class="p-6">
    <h3 class="text-2xl font-semibold mb-4">Info Customer</h3>

    @php
      // Ambil nilai filter, baseUrl, dan search query dari request
      $currentFilter = request('filter');
      $baseUrl = route('admin.info-cust');
      $searchQuery = request('search') ? '&search=' . request('search') : '';
    @endphp

    {{-- 1) Deretan tombol filter --}}
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

    {{-- 2) Form search + Reset --}}
    <div class="flex justify-between mb-4">
      <form method="GET" class="flex gap-2 w-full">
        <input type="text"
               name="search"
               placeholder="Search by name, email, or phone"
               value="{{ request('search') }}"
               class="border rounded px-3 py-2 w-1/3" />

        @if(request('search') || request('filter'))
          <a href="{{ route('admin.info-cust') }}"
             class="px-3 py-2 border rounded bg-gray-100">Reset</a>
        @endif

        <button type="submit"
                class="border px-3 py-2 rounded bg-blue-500 text-white">Search</button>
      </form>
    </div>

    {{-- 3) Tombol Export --}}
    <div class="flex gap-2 mb-4">
      <a href="{{ route('admin.info-cust.export.excel', request()->all()) }}"
         class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        Export Excel
      </a>
      <a href="{{ route('admin.info-cust.export.pdf', request()->all()) }}"
         class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
        Export PDF
      </a>
      <a href="{{ route('admin.info-cust.export.word', request()->all()) }}"
         class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Export Word
      </a>
    </div>

    {{-- 4) Tabel Daftar Pelanggan --}}
    @if($customers->count() > 0)
      <div class="overflow-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <input type="checkbox" />
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Phone
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Registered At
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($customers as $cust)
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input type="checkbox" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap flex items-center space-x-3">
                  {{-- Avatar inisial --}}
                  <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                    <span class="text-gray-600 font-semibold">
                      {{ strtoupper(substr($cust->nama, 0, 1)) }}
                    </span>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ $cust->nama }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ $cust->email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ $cust->nomor_hp }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ $cust->created_at->format('d M Y H:i') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- 5) Pagination --}}
      <div class="mt-4">
        {{ $customers->withQueryString()->links() }}
      </div>
    @else
      <p class="text-gray-500">Tidak ada data pelanggan tersedia.</p>
    @endif
  </div>
</x-layout>
