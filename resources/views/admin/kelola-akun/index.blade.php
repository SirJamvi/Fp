<x-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Kelola Akun</h2>
            <a href="{{ route('admin.kelola-akun.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tambah Akun</a>
        </div>

        @if(session('success'))
            <div class="mb-4 text-green-600">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $user->nama }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @switch($user->peran)
                                    @case('admin')
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Admin</span>
                                        @break
                                    @case('pelayan')
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Pelayan/Kasir</span>
                                        @break
                                    @case('koki')
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Koki</span>
                                        @break
                                    @default
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Unknown</span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <a href="{{ route('admin.kelola-akun.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                @if ($user->peran !== 'admin')
                                    <form action="{{ route('admin.kelola-akun.destroy', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus akun ini?')" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
