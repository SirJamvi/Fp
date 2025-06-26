<x-layout>
    <x-slot:title></x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-teal-800">Manajemen Menu</h1>
        <a href="{{ route('admin.menu.create') }}" class="btn bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md flex items-center transition">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Item
        </a>
    </div>

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('admin.menu.index') }}" class="mb-6 flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kategori menu..."
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 w-full max-w-sm">

        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md">
            Cari
        </button>

        @if(request('search'))
            <a href="{{ route('admin.menu.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md">
                Reset
            </a>
        @endif
    </form>

    @if($menus->isEmpty())
        <div class="p-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-lg flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Tidak ada menu ditemukan.
        </div>
    @else
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-teal-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">Image</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Category</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Original Price</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Discount</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Final Price</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Prep Time</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-teal-50 transition">
                                {{-- Kolom lainnya tetap sama seperti sebelumnya --}}
                                <td class="px-6 py-4">
                                    @if($menu->image)
                                        <img src="{{ asset('storage/' . $menu->image) }}" class="h-12 w-12 rounded-lg object-cover border border-gray-200" alt="{{ $menu->name }}">
                                    @else
                                        <div class="h-12 w-12 bg-teal-100 flex items-center justify-center rounded-lg border border-teal-200">
                                            <svg class="h-6 w-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $menu->name }}</td>
                                <td class="px-6 py-4">{{ ucfirst($menu->category) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    @if($menu->discount_percentage)
                                        <span class="text-purple-800 bg-purple-100 px-2 py-1 text-xs rounded">{{ $menu->discount_percentage }}%</span>
                                    @else — @endif
                                </td>
                                <td class="px-6 py-4 font-semibold text-teal-700">
                                    @if($menu->discounted_price)
                                        Rp {{ number_format($menu->discounted_price, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($menu->price, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($menu->is_available)
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Available</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Not Available</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $menu->preparation_time ?? '—' }} <span class="text-gray-500 text-sm">min</span></td>
                                <td class="px-6 py-4">
                                    {{-- Tombol Edit & Delete --}}
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.menu.edit', $menu) }}" class="text-teal-600 hover:text-teal-800">Edit</a>
                                        <form action="{{ route('admin.menu.destroy', $menu) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $menus->withQueryString()->links() }}
            </div>
        </div>
    @endif
</x-layout>
