<!-- info-cust.blade.php -->
<x-layout>
    <x-slot:title>{{ $title ?? 'Info Customer' }}</x-slot:title>

    <div class="p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 rounded-full flex items-center justify-center w-12 h-12 bg-[#107672]">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-0">Kelola Data Costumer</h1>
            </div>
        </div>

        @php
            $currentFilter = request('filter');
            $baseUrl = route('admin.info-cust');
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
                <input type="text"
                       name="search"
                       placeholder="Search by name, email, or phone"
                       value="{{ request('search') }}"
                       class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/2 focus:ring-[#107672] focus:border-[#107672]" />

                @if(request('search') || request('filter'))
                    <a href="{{ route('admin.info-cust') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 hover:bg-gray-200 transition flex items-center">
                        Reset
                    </a>
                @endif

                <button type="submit"
                        class="px-4 py-2 border rounded-lg bg-[#107672] text-white hover:bg-[#0d625f] transition flex items-center">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search
                </button>
            </form>
        </div>

        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('admin.info-cust.export.excel', request()->all()) }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </a>
            <a href="{{ route('admin.info-cust.export.pdf', request()->all()) }}"
               class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export PDF
            </a>
            <a href="{{ route('admin.info-cust.export.word', request()->all()) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Word
            </a>
        </div>

        @if($customers->count() > 0)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-[#107672] text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Registered At</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach ($customers as $cust)
                            <tr class="hover:bg-teal-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                                            <span class="text-teal-800 font-semibold">
                                                {{ strtoupper(substr($cust->nama, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $cust->nama }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $cust->email }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $cust->nomor_hp }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $cust->created_at->format('d M Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $customers->withQueryString()->links() }}
            </div>
        @else
            <div class="p-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-lg flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tidak ada data pelanggan tersedia.
            </div>
        @endif
    </div>
</x-layout>