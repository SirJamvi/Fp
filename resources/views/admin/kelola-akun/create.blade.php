<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  
  <div class="container mx-auto p-4">
    <div class="flex items-center mb-6">
      <a href="{{ route('admin.kelola-akun.index') }}" class="text-blue-500 hover:text-blue-700 mr-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
      </a>
      <h1 class="text-2xl font-bold">Tambah Akun Baru</h1>
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
      <form action="{{ route('admin.kelola-akun.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">
            Nama
          </label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nama') border-red-500 @enderror" 
            id="nama" 
            name="nama" 
            type="text" 
            value="{{ old('nama') }}" 
            placeholder="Masukkan nama" 
            required>
          @error('nama')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
          @enderror
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
            Email
          </label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" 
            id="email" 
            name="email" 
            type="email" 
            value="{{ old('email') }}" 
            placeholder="Masukkan email" 
            required>
          @error('email')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
          @enderror
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="nomor_hp">
            Nomor HP
          </label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nomor_hp') border-red-500 @enderror" 
            id="nomor_hp" 
            name="nomor_hp" 
            type="text" 
            value="{{ old('nomor_hp') }}" 
            placeholder="Masukkan nomor HP" 
            required>
          @error('nomor_hp')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
          @enderror
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
            Password
          </label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" 
            id="password" 
            name="password" 
            type="password" 
            placeholder="********" 
            required>
          @error('password')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
          @enderror
        </div>
        
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirmation">
            Konfirmasi Password
          </label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
            id="password_confirmation" 
            name="password_confirmation" 
            type="password" 
            placeholder="********" 
            required>
        </div>
        
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="peran">
            Peran
          </label>
          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('peran') border-red-500 @enderror" 
            id="peran" 
            name="peran" 
            required>
            <option value="">Pilih Peran</option>
            <option value="pelayan" {{ old('peran') == 'pelayan' ? 'selected' : '' }}>Pelayan/Kasir</option>
            <option value="koki" {{ old('peran') == 'koki' ? 'selected' : '' }}>Koki</option>
          </select>
          @error('peran')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
          @enderror
        </div>
        
        <div class="flex items-center justify-between">
          <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
            Simpan
          </button>
          <a href="{{ route('admin.kelola-akun.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</x-layout>