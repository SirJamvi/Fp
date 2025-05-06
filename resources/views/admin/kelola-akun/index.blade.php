<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  
  <div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Users</h1>
      <a href="{{ route('admin.kelola-akun.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        + Add New
      </a>
    </div>

    @if(session('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
      </div>
    @endif

    <div class="bg-white shadow-md rounded overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">NAME</th>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">ROLE</th>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">CREATE DATE</th>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">ACTION</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($users as $user)
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->nama }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                @if($user->peran === 'admin')
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                @elseif($user->peran === 'pelayan')
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Pelayan/Kasir</span>
                @elseif($user->peran === 'koki')
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Koki</span>
                @else
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pelanggan</span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $user->created_at->format('d/m/Y') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="{{ route('admin.kelola-akun.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                @if($user->peran !== 'admin')
                  <form action="{{ route('admin.kelola-akun.destroy', $user->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">Delete</button>
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