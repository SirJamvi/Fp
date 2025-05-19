{{-- resources/views/admin/meja/create.blade.php --}}
<x-layout>
    <x-slot:title>Add Table</x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Add New Table</h1>
        <a href="{{ route('admin.meja.index') }}" class="text-blue-500 hover:underline">
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.meja.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nomor_meja" class="block text-sm font-medium text-gray-700">Table Number <span class="text-red-500">*</span></label>
                    <input type="text" id="nomor_meja" name="nomor_meja" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('nomor_meja') border-red-500 @enderror"
                        value="{{ old('nomor_meja') }}">
                    @error('nomor_meja')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kapasitas" class="block text-sm font-medium text-gray-700">Capacity <span class="text-red-500">*</span></label>
                    <input type="number" id="kapasitas" name="kapasitas" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('kapasitas') border-red-500 @enderror"
                        value="{{ old('kapasitas') }}">
                    @error('kapasitas')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="area" class="block text-sm font-medium text-gray-700">Area <span class="text-red-500">*</span></label>
                <select id="area" name="area" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('area') border-red-500 @enderror">
                    <option value="">-- Pilih Area --</option>
                    <option value="indoor" {{ old('area') == 'indoor' ? 'selected' : '' }}>Indoor</option>
                    <option value="outdoor" {{ old('area') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                    <option value="vvip" {{ old('area') == 'vvip' ? 'selected' : '' }}>VVIP</option>
                </select>
                @error('area')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between pt-6">
                <a href="{{ route('admin.meja.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md text-sm font-medium">
                    ← Back to List
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-sm font-medium">
                    💾 Save Table
                </button>
            </div>
        </form>
    </div>
</x-layout>
