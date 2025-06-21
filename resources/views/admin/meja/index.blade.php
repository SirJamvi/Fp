{{-- resources/views/admin/meja/index.blade.php --}}
<x-layout>
    <x-slot:title></x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-teal-800">Manajemen Meja</h1>
        <a href="{{ route('admin.meja.create') }}" class="px-4 py-2 bg-[#107672] hover:bg-[#0d625f] text-white rounded-lg transition duration-300 font-medium flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Tambah Meja
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-[#d1f0ee] text-[#107672] rounded-lg mb-6 flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($meja->isEmpty())
        <div class="p-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-lg flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Belum ada data meja.
        </div>
    @else
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-[#107672] text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">Nomor Meja</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Area</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Kapasitas</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($meja as $m)
                            <tr class="hover:bg-teal-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $m->nomor_meja }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $m->area }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $m->kapasitas }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($m->status === 'tersedia')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Tersedia
                                        </span>
                                    @elseif($m->status === 'terpakai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Terpakai
                                        </span>
                                    @elseif($m->status === 'dipesan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Dipesan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($m->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.meja.edit', $m->id) }}" 
                                           class="flex items-center justify-center w-9 h-9 bg-teal-50 hover:bg-teal-100 text-teal-600 rounded-lg border border-teal-200 transition-all hover:shadow-sm"
                                           title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        
                                        <form action="{{ route('admin.meja.destroy', $m->id) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus meja ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="flex items-center justify-center w-9 h-9 bg-red-50 hover:bg-red-100 text-red-500 rounded-lg border border-red-200 transition-all hover:shadow-sm"
                                                    title="Hapus">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layout>