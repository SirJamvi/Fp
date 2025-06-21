{{-- resources/views/admin/meja/edit.blade.php --}}
<x-layout>
    <x-slot:title></x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-teal-800">Edit Table: {{ $meja->nomor_meja }}</h1>
        <a href="{{ route('admin.meja.index') }}" class="text-teal-600 hover:text-teal-800 flex items-center transition">
            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border border-teal-100">
        <form action="{{ route('admin.meja.update', $meja->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nomor_meja" class="block text-sm font-medium text-gray-700 mb-1">Table Number <span class="text-red-500">*</span></label>
                    <input type="text" id="nomor_meja" name="nomor_meja" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 text-gray-700 @error('nomor_meja') border-red-500 @enderror"
                        value="{{ old('nomor_meja', $meja->nomor_meja) }}">
                    @error('nomor_meja')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kapasitas" class="block text-sm font-medium text-gray-700 mb-1">Capacity <span class="text-red-500">*</span></label>
                    <input type="number" id="kapasitas" name="kapasitas" min="1" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 text-gray-700 @error('kapasitas') border-red-500 @enderror"
                        value="{{ old('kapasitas', $meja->kapasitas) }}">
                    @error('kapasitas')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="area" class="block text-sm font-medium text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                <select id="area" name="area" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 text-gray-700 @error('area') border-red-500 @enderror">
                    <option value="">-- Pilih Area --</option>
                    <option value="indoor" {{ old('area', $meja->area) == 'indoor' ? 'selected' : '' }}>Indoor</option>
                    <option value="outdoor" {{ old('area', $meja->area) == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                    <option value="vvip" {{ old('area', $meja->area) == 'vvip' ? 'selected' : '' }}>VVIP</option>
                </select>
                @error('area')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between pt-6 border-t border-gray-100">
                <a href="{{ route('admin.meja.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-teal-600 text-white hover:bg-teal-700 rounded-lg text-sm font-medium transition">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Update Table
                </button>
            </div>
        </form>
    </div>
</x-layout>