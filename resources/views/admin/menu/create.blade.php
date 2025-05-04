{{-- resources/views/admin/menu/create.blade.php --}}
<x-layout>
    <x-slot:title>Add Menu Item</x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Add New Menu Item</h1>
        <a href="{{ route('admin.menu.index') }}" class="text-blue-500 hover:underline">
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('name') border-red-500 @enderror"
                        value="{{ old('name') }}">
                    @error('name')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select id="category" name="category" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('category') border-red-500 @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Rp</span>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                            class="block w-full rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('price') border-red-500 @enderror"
                            value="{{ old('price') }}">
                    </div>
                    @error('price')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('preparation_time') border-red-500 @enderror"
                        value="{{ old('preparation_time') }}">
                    @error('preparation_time')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Provide any additional details about the menu item (ingredients, allergens, etc.)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                    <input type="file" id="image" name="image" accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer focus:outline-none @error('image') border-red-500 @enderror">
                    @error('image')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Upload an image (JPG, PNG, GIF - max 2MB)</p>
                </div>

                <div class="flex items-start pt-6">
                    <div class="flex items-center h-5">
                        <input id="is_available" name="is_available" type="checkbox" value="1"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            {{ old('is_available', '1') ? 'checked' : '' }}>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_available" class="font-medium text-gray-700">Available for ordering</label>
                        <p class="text-gray-500">Uncheck if this item is out of stock or unavailable.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between pt-6">
                <a href="{{ route('admin.menu.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md text-sm font-medium">
                    ← Back to List
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-sm font-medium">
                    💾 Save Menu Item
                </button>
            </div>
        </form>
    </div>
</x-layout>