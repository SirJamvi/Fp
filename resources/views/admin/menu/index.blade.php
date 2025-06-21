{{-- resources/views/admin/menu/index.blade.php --}}
<x-layout>
    <x-slot:title></x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-teal-800">Manajemen Menu</h1>
        <a href="{{ route('admin.menu.create') }}" class="btn bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md flex items-center transition">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Item
        </a>
    </div>

    @if($menus->isEmpty())
        <div class="p-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-lg flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            No menu items found. Start by adding a new one.
        </div>
    @else
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-teal-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">Image</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Category</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Original Price</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Discount</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Final Price</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Prep Time</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-teal-50 transition">
                                {{-- Image --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($menu->image)
                                        <img src="{{ asset('storage/' . $menu->image) }}" class="h-12 w-12 rounded-lg object-cover border border-gray-200" alt="{{ $menu->name }}">
                                    @else
                                        <div class="h-12 w-12 bg-teal-100 flex items-center justify-center rounded-lg border border-teal-200">
                                            <svg class="h-6 w-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>

                                {{-- Name --}}
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $menu->name }}</td>

                                {{-- Category --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        @if($menu->category == 'food') bg-green-100 text-green-800
                                        @elseif($menu->category == 'beverage') bg-blue-100 text-blue-800
                                        @elseif($menu->category == 'dessert') bg-yellow-100 text-yellow-800
                                        @elseif($menu->category == 'appetizer') bg-pink-100 text-pink-800
                                        @elseif($menu->category == 'other') bg-gray-100 text-gray-800
                                        @else bg-teal-100 text-teal-800
                                        @endif">
                                        {{ ucfirst($menu->category) }}
                                    </span>
                                </td>

                                {{-- Original Price --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    Rp {{ number_format($menu->price, 0, ',', '.') }}
                                </td>

                                {{-- Discount Percentage --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($menu->discount_percentage && $menu->discount_percentage > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $menu->discount_percentage }}%
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Final Price --}}
                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                    @if($menu->discounted_price && $menu->discounted_price < $menu->price)
                                        <span class="text-gray-500 line-through mr-1 text-sm">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                        <span class="text-teal-700">Rp {{ number_format($menu->discounted_price, 0, ',', '.') }}</span>
                                    @else
                                        Rp {{ number_format($menu->price, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($menu->is_available)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Available
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Not Available
                                        </span>
                                    @endif
                                </td>

                                {{-- Preparation Time --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $menu->preparation_time ?? '—' }} <span class="text-gray-500 text-sm">min</span>
                                </td>

                                {{-- Actions - Using SVG icons --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.menu.edit', $menu) }}" 
                                           class="flex items-center justify-center w-9 h-9 bg-teal-50 hover:bg-teal-100 text-teal-600 rounded-lg border border-teal-200 transition-all hover:shadow-sm"
                                           title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        
                                        <form action="{{ route('admin.menu.destroy', $menu) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this item?');">
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
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $menus->links() }}
            </div>
        </div>
    @endif
</x-layout>