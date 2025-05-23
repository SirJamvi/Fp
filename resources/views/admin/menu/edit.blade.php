{{-- resources/views/admin/menu/edit.blade.php --}}
<x-layout>
    <x-slot:title>Edit Menu Item</x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Menu Item: {{ $menu->name }}</h1>
        <a href="{{ route('admin.menu.index') }}" class="text-blue-500 hover:underline">
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.menu.update', $menu) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('name') border-red-500 @enderror"
                        value="{{ old('name', $menu->name) }}">
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
                            <option value="{{ $value }}" {{ old('category', $menu->category) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Original Price <span class="text-red-500">*</span></label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Rp</span>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                            class="block w-full rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('price') border-red-500 @enderror"
                            value="{{ old('price', $menu->price) }}">
                    </div>
                    @error('price')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount Percentage (%)</label>
                    <div class="flex items-center mt-1">
                        <input type="number" id="discount_percentage" name="discount_percentage" step="1" min="0" max="100"
                            class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('discount_percentage') border-red-500 @enderror"
                            value="{{ old('discount_percentage', $menu->discount_percentage) }}">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">%</span>
                    </div>
                    @error('discount_percentage')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="discounted_price_display" class="block text-sm font-medium text-gray-700">Discounted Price (Rp)</label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Rp</span>
                        <input type="text" id="discounted_price_display" readonly
                            class="block w-full rounded-r-md border-gray-300 bg-gray-100 shadow-sm"
                            value="{{ old('discounted_price', $menu->discounted_price ?? $menu->price) }}"> {{-- Init value with current discounted price or original price --}}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">This price is calculated automatically.</p>
                </div>
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('preparation_time') border-red-500 @enderror"
                        value="{{ old('preparation_time', $menu->preparation_time) }}">
                    @error('preparation_time')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('description') border-red-500 @enderror">{{ old('description', $menu->description) }}</textarea>
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
                    <p class="text-sm text-gray-500 mt-1">Upload a new image to replace the current one (JPG, PNG, GIF - max 2MB)</p>
                    
                    @if($menu->image)
                        <div class="mt-3">
                            <p class="text-sm font-medium text-gray-700">Current Image:</p>
                            <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="mt-2 h-32 w-auto rounded-md shadow-sm">
                        </div>
                    @endif
                </div>

                <div class="flex items-start pt-6">
                    <div class="flex items-center h-5">
                        <input id="is_available" name="is_available" type="checkbox" value="1"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            {{ old('is_available', $menu->is_available) ? 'checked' : '' }}>
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
                    💾 Update Menu Item
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () { // Tambahkan ini
            const priceInput = document.getElementById('price');
            const discountPercentageInput = document.getElementById('discount_percentage');
            const discountedPriceDisplay = document.getElementById('discounted_price_display');

            function calculateDiscountedPrice() {
                let price = parseFloat(priceInput.value) || 0;
                let discountPercentage = parseFloat(discountPercentageInput.value) || 0;

                // Clamp discount percentage between 0 and 100
                if (discountPercentage < 0) {
                    discountPercentage = 0;
                    discountPercentageInput.value = 0;
                }
                if (discountPercentage > 100) {
                    discountPercentage = 100;
                    discountPercentageInput.value = 100;
                }
                
                let discountedPrice = price * (1 - (discountPercentage / 100));
                discountedPriceDisplay.value = discountedPrice.toFixed(2);
            }

            priceInput.addEventListener('input', calculateDiscountedPrice);
            discountPercentageInput.addEventListener('input', calculateDiscountedPrice);

            // Initial calculation when the page loads
            calculateDiscountedPrice();
        });
    </script>
    @endpush
</x-layout>