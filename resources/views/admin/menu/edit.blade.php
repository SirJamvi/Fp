{{-- resources/views/admin/menu/edit.blade.php --}}
<x-layout>
    <x-slot:title></x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-teal-800">Edit Menu Item: {{ $menu->name }}</h1>
        <a href="{{ route('admin.menu.index') }}" class="text-teal-600 hover:text-teal-800 flex items-center transition">
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border border-teal-100">
        <form action="{{ route('admin.menu.update', $menu) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                        value="{{ old('name', $menu->name) }}">
                    @error('name')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select id="category" name="category" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 @error('category') border-red-500 @enderror">
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
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Original Price <span class="text-red-500">*</span></label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm h-10">Rp</span>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                            class="block w-full rounded-r-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 h-10 @error('price') border-red-500 @enderror"
                            value="{{ old('price', $menu->price) }}">
                    </div>
                    @error('price')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage (%)</label>
                    <div class="flex items-center mt-1">
                        <input type="number" id="discount_percentage" name="discount_percentage" step="1" min="0" max="100"
                            class="block w-full rounded-l-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 h-10 @error('discount_percentage') border-red-500 @enderror"
                            value="{{ old('discount_percentage', $menu->discount_percentage) }}">
                        <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm h-10">%</span>
                    </div>
                    @error('discount_percentage')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="discounted_price_display" class="block text-sm font-medium text-gray-700 mb-1">Discounted Price (Rp)</label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm h-10">Rp</span>
                        <input type="text" id="discounted_price_display" readonly
                            class="block w-full rounded-r-lg border-gray-300 bg-gray-100 shadow-sm h-10"
                            value="{{ old('discounted_price', $menu->discounted_price ?? $menu->price) }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Calculated automatically</p>
                </div>
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" min="1"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 h-10 @error('preparation_time') border-red-500 @enderror"
                        value="{{ old('preparation_time', $menu->preparation_time) }}">
                    @error('preparation_time')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description', $menu->description) }}</textarea>
                @error('description')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-lg">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-teal-600 hover:text-teal-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="image" name="image" type="file" accept="image/*" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                    @error('image')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    
                    @if($menu->image)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                            <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="mt-2 h-32 w-auto rounded-lg shadow-sm border border-gray-200">
                        </div>
                    @endif
                </div>

                <div class="flex items-start pt-6">
                    <div class="flex items-center h-5">
                        <input id="is_available" name="is_available" type="checkbox" value="1"
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                            {{ old('is_available', $menu->is_available) ? 'checked' : '' }}>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_available" class="font-medium text-gray-700">Available for ordering</label>
                        <p class="text-gray-500 text-xs">Uncheck if unavailable</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between pt-6 border-t border-gray-100">
                <a href="{{ route('admin.menu.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to List
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-teal-600 text-white hover:bg-teal-700 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-save mr-2"></i> Update Menu Item
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                discountedPriceDisplay.value = discountedPrice.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            priceInput.addEventListener('input', calculateDiscountedPrice);
            discountPercentageInput.addEventListener('input', calculateDiscountedPrice);

            // Initial calculation when the page loads
            calculateDiscountedPrice();
        });
    </script>
    @endpush
</x-layout>