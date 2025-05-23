{{-- resources/views/admin/menu/index.blade.php --}}
<x-layout>
    <x-slot:title>Menu Management</x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Menu Items</h1>
        <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">
            + Add New Item
        </a>
    </div>

    @if($menus->isEmpty())
        <div class="p-4 bg-blue-100 text-blue-700 rounded">
            No menu items found. Start by adding a new one.
        </div>
    @else
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-2">Image</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2">Original Price</th>
                        <th class="px-4 py-2">Discount (%)</th>
                        <th class="px-4 py-2">Final Price</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Prep Time</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($menus as $menu)
                        <tr>
                            {{-- Image --}}
                            <td class="px-4 py-2">
                                @if($menu->image)
                                    <img src="{{ asset('storage/' . $menu->image) }}" class="h-12 w-12 rounded object-cover" alt="{{ $menu->name }}">
                                @else
                                    <div class="h-12 w-12 bg-gray-100 flex items-center justify-center rounded">
                                        <i class="fas fa-utensils text-gray-400"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- Name --}}
                            <td class="px-4 py-2">{{ $menu->name }}</td>

                            {{-- Category --}}
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-white text-sm
                                    @if($menu->category == 'food') bg-green-500
                                    @elseif($menu->category == 'beverage') bg-blue-500
                                    @elseif($menu->category == 'dessert') bg-yellow-500
                                    @elseif($menu->category == 'appetizer') bg-pink-500
                                    @elseif($menu->category == 'other') bg-gray-700
                                    @else bg-gray-500
                                    @endif">
                                    {{ ucfirst($menu->category) }}
                                </span>
                            </td>

                            {{-- Original Price --}}
                            <td class="px-4 py-2">
                                Rp {{ number_format($menu->price, 0, ',', '.') }}
                            </td>

                            {{-- Discount Percentage --}}
                            <td class="px-4 py-2">
                                @if($menu->discount_percentage && $menu->discount_percentage > 0)
                                    <span class="text-purple-600 font-semibold">{{ $menu->discount_percentage }}%</span>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Final Price --}}
                            <td class="px-4 py-2">
                                {{-- Cek apakah ada discounted_price dan apakah lebih kecil dari price asli --}}
                                @if($menu->discounted_price && $menu->discounted_price < $menu->price)
                                    <span class="text-red-500 line-through mr-1">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                    <span class="font-bold text-green-700">Rp {{ number_format($menu->discounted_price, 0, ',', '.') }}</span>
                                @else
                                    {{-- Jika tidak ada diskon atau discounted_price tidak valid, tampilkan harga asli --}}
                                    Rp {{ number_format($menu->price, 0, ',', '.') }}
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-2">
                                @if($menu->is_available)
                                    <span class="text-green-600 font-semibold">Available</span>
                                @else
                                    <span class="text-red-600 font-semibold">Not Available</span>
                                @endif
                            </td>

                            {{-- Preparation Time --}}
                            <td class="px-4 py-2">
                                {{ $menu->preparation_time ?? '—' }} menit
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.menu.edit', $menu) }}" class="text-blue-500 hover:underline mr-2">Edit</a>
                                <form action="{{ route('admin.menu.destroy', $menu) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $menus->links() }}
            </div>
        </div>
    @endif
</x-layout>