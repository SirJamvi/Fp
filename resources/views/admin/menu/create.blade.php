{{-- resources/views/admin/menu/create.blade.php --}}
<x-layout>
    <x-slot:title></x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-teal-800">Add New Menu Item</h1>
        <a href="{{ route('admin.menu.index') }}" class="text-teal-600 hover:text-teal-800 flex items-center transition">
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border border-teal-100">
        <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                        value="{{ old('name') }}">
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
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Original Price <span class="text-red-500">*</span></label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm h-10">Rp</span>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                            class="block w-full rounded-r-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 h-10 @error('price') border-red-500 @enderror"
                            value="{{ old('price') }}">
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
                            value="{{ old('discount_percentage') }}">
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
                            value="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Calculated automatically</p>
                </div>
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" min="1"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 h-10 @error('preparation_time') border-red-500 @enderror"
                        value="{{ old('preparation_time') }}">
                    @error('preparation_time')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Ingredients, allergens, etc.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <div class="mt-1">
                        <input type="file" id="image" name="image" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 @error('image') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF up to 2MB</p>
                    </div>
                    @error('image')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    
                    <!-- Image preview area -->
                    <div id="image-preview" class="mt-3 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview:</label>
                        <div class="border border-gray-300 rounded-lg p-2 bg-gray-50">
                            <img id="preview-img" src="" alt="Image preview" class="max-w-full h-32 object-cover rounded">
                            <button type="button" id="remove-preview" class="mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
                        </div>
                    </div>
                </div>

                <div class="flex items-start pt-6">
                    <div class="flex items-center h-5">
                        <input id="is_available" name="is_available" type="checkbox" value="1"
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                            {{ old('is_available', '1') ? 'checked' : '' }}>
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
                    <i class="fas fa-save mr-2"></i> Save Menu Item
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

            // Image preview functionality
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            const removePreview = document.getElementById('remove-preview');

            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Check file size (2MB = 2 * 1024 * 1024 bytes)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size exceeds 2MB. Please choose a smaller file.');
                        imageInput.value = '';
                        return;
                    }

                    // Check file type
                    if (!file.type.startsWith('image/')) {
                        alert('Please select a valid image file.');
                        imageInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.classList.add('hidden');
                }
            });

            removePreview.addEventListener('click', function() {
                imageInput.value = '';
                imagePreview.classList.add('hidden');
                previewImg.src = '';
            });
        });
    </script>
    @endpush
</x-layout>