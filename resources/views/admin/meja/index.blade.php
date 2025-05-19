{{-- resources/views/admin/meja/index.blade.php --}}
<x-layout>
    <x-slot:title>Manajemen Meja</x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Daftar Meja</h1>
        <a href="{{ route('admin.meja.create') }}" class="btn btn-primary">+ Tambah Meja</a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-100 text-green-800 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($meja->isEmpty())
        <div class="p-4 bg-blue-100 text-blue-700 rounded">Belum ada data meja.</div>
    @else
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-2">Nomor Meja</th>
                        <th class="px-4 py-2">Area</th> {{-- Kolom Area --}}
                        <th class="px-4 py-2">Kapasitas</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($meja as $meja)
                        <tr>
                            <td class="px-4 py-2">{{ $meja->nomor_meja }}</td>
                            <td class="px-4 py-2">{{ $meja->area }}</td> {{-- Tampilkan area --}}
                            <td class="px-4 py-2">{{ $meja->kapasitas }}</td>
                            <td class="px-4 py-2">{{ ucfirst($meja->status) }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.meja.edit', $meja->id) }}" class="text-blue-500 hover:underline mr-2">Edit</a>
                                <form action="{{ route('admin.meja.destroy', $meja->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus meja ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:underline" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
