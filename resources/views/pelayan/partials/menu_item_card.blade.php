{{-- resources/views/pelayan/partials/menu_item_card.blade.php --}}
<div class="col menu-item-col" data-category="{{ Str::slug($menu->category) }}" data-name="{{ strtolower($menu->name) }}">
    <div class="card card-menu-item h-100">
        <img src="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/img/default-food.png') }}" class="card-img-top" alt="{{ $menu->name }}">
        <div class="card-body">
            <div>
                <h5 class="card-title fs-6 fw-bold mb-1">{{ $menu->name }}</h5>
                <p class="card-text small text-muted mb-2">{{ Str::limit($menu->description, 50) }}</p>
            </div>
            <div class="mt-auto">
                <p class="price mb-2">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                <button type="button" class="btn btn-sm btn-primary w-100 add-to-cart-btn"
                        data-id="{{ $menu->id }}"
                        data-name="{{ $menu->name }}"
                        data-price="{{ $menu->price }}"
                        data-image="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/img/default-food.png') }}">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah
                </button>
            </div>
        </div>
    </div>
</div>