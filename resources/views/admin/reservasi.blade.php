<!-- reservasi.blade.php -->
<x-layout>
    <x-slot:title>{{ $title ?? 'reservasi' }}</x-slot:title>

    <div class="p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 rounded-full flex items-center justify-center w-12 h-12 bg-[#107672]">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-0">Kelola Data Total Orders</h1>
            </div>
        </div>

        @php
            $currentFilter = request('filter');
            $baseUrl = route('admin.reservasi');
            $searchQuery = request('search') ? '&search=' . request('search') : '';
        @endphp

        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ $baseUrl }}?filter=all{{ $searchQuery }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ $currentFilter == 'all' || !$currentFilter ? 'bg-[#107672] text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                All
            </a>
            <a href="{{ $baseUrl }}?filter=week{{ $searchQuery }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ $currentFilter == 'week' ? 'bg-[#107672] text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                This Week
            </a>
            <a href="{{ $baseUrl }}?filter=month{{ $searchQuery }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ $currentFilter == 'month' ? 'bg-[#107672] text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                This Month
            </a>
            <a href="{{ $baseUrl }}?filter=year{{ $searchQuery }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ $currentFilter == 'year' ? 'bg-[#107672] text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                This Year
            </a>
        </div>

        <div class="flex justify-between mb-6">
            <form method="GET" class="flex gap-2 w-full">
                <input type="text" name="search" placeholder="Search by name or code" 
                       value="{{ request('search') }}"
                       class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/2 focus:ring-[#107672] focus:border-[#107672]" />

                @if(request('search') || request('filter'))
                    <a href="{{ route('admin.reservasi') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 hover:bg-gray-200 transition flex items-center">
                        Reset
                    </a>
                @endif

                <button type="submit" class="px-4 py-2 border rounded-lg bg-[#107672] text-white hover:bg-[#0d625f] transition flex items-center">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search
                </button>
            </form>
        </div>

        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('admin.reservasi.export.excel', request()->all()) }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </a>
            <a href="{{ route('admin.reservasi.export.pdf', request()->all()) }}"
               class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export PDF
            </a>
            <a href="{{ route('admin.reservasi.export.word', request()->all()) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Word
            </a>
        </div>

        @if($reservasis->count() > 0)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-[#107672] text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">Kode Reservasi</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Nama</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Catatan</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Waktu Kedatangan</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Nomor Meja</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($reservasis as $r)
                            <tr class="hover:bg-teal-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $r->kode_reservasi }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $r->nama_pelanggan ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $r->catatan ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @php
                                        $mejaList = $r->meja ?? collect();

                                        if ($mejaList instanceof \Illuminate\Database\Eloquent\Collection && $mejaList->isEmpty() && $r->combined_tables) {
                                            $decoded = json_decode($r->combined_tables, true) ?: [];
                                            $mejaList = \App\Models\Meja::whereIn('id', $decoded)->get();
                                        }
                                    @endphp

                                    @if($mejaList->isNotEmpty())
                                        @foreach($mejaList as $mejaObj)
                                            <span class="inline-block bg-teal-100 text-teal-800 text-xs font-medium px-2.5 py-0.5 rounded mr-1 mb-1">
                                                {{ $mejaObj->nomor_meja }} ({{ $mejaObj->area }})
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    @switch($r->status)
                                        @case('selesai')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Selesai</span>
                                            @break
                                        @case('dipesan')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Dipesan</span>
                                            @break
                                        @case('dibatalkan')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Dibatalkan</span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($r->status) }}</span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $reservasis->withQueryString()->links() }}
            </div>
        @else
            <div class="p-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-lg flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tidak ada data reservasi tersedia.
            </div>
        @endif
    </div>
</x-layout>