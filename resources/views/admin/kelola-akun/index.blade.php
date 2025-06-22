<!-- kelola-akun/index.blade.php -->
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-full flex items-center justify-center w-12 h-12 bg-[#107672]">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-0">Kelola Data Users</h1>
                </div>
            </div>
            <a href="{{ route('admin.kelola-akun.create') }}" class="px-4 py-2 bg-[#107672] hover:bg-[#0d625f] text-white rounded-lg transition duration-300 font-medium flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg mb-6 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($users->count() > 0)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-[#107672] text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">NAME</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">ROLE</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">CREATE DATE</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-teal-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->nama }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if($user->peran === 'admin')
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Admin</span>
                                    @elseif($user->peran === 'pelayan')
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Pelayan/Kasir</span>
                                    @elseif($user->peran === 'koki')
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Koki</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pelanggan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.kelola-akun.edit', $user->id) }}" 
                                           class="flex items-center justify-center w-9 h-9 bg-teal-50 hover:bg-teal-100 text-teal-600 rounded-lg border border-teal-200 transition-all hover:shadow-sm"
                                           title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        
                                        @if($user->peran !== 'admin')
                                            <form action="{{ route('admin.kelola-akun.destroy', $user->id) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="flex items-center justify-center w-9 h-9 bg-red-50 hover:bg-red-100 text-red-500 rounded-lg border border-red-200 transition-all hover:shadow-sm"
                                                        title="Delete">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-lg flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Belum ada data pengguna.
            </div>
        @endif
    </div>
</x-layout>