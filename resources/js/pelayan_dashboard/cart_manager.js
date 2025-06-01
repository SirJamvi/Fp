// resources/js/pelayan_dashboard/cart_manager.js

// Import dependencies
import { checkSubmitButtonStatus } from './form_submit';
import { formatRupiah } from './payment_modal';
import { showCustomAlert } from '../utils'; // Import custom alert utility

// --- State (Keranjang) ---
// Objek untuk menyimpan item yang dipilih. Key adalah menu ID.
let cart = {};
const localStorageKey = 'pelayanCart'; // Key for localStorage

// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let cartItemsContainer;
let emptyCartMessage;
let totalItemsSpan;
let grandTotalSpan;
let hiddenInputsContainer;
let submitOrderBtn; // Diperlukan untuk memanggil checkSubmitButtonStatus

// Export fungsi inisialisasi untuk menerima elemen DOM
export function initCart(elements) {
    console.log('Initializing Cart Manager module...');
    cartItemsContainer = elements.cartItemsContainer;
    emptyCartMessage = elements.emptyCartMessage;
    totalItemsSpan = elements.totalItemsSpan;
    grandTotalSpan = elements.grandTotalSpan;
    hiddenInputsContainer = elements.hiddenInputsContainer;
    submitOrderBtn = elements.submitOrderBtn; // Dapatkan elemen tombol submit

    console.log('Cart Manager initialized.');

    // Muat keranjang dari localStorage saat inisialisasi
    loadCartFromLocalStorage();
    // Render awal keranjang
    renderCart();

    // Lampirkan event listener untuk interaksi item keranjang (delegasi)
    attachCartEventListeners();
}

// Muat data keranjang dari localStorage
function loadCartFromLocalStorage() {
    console.log('Attempting to load cart from localStorage...');
    const savedCart = localStorage.getItem(localStorageKey);
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
            console.log('Cart loaded from localStorage:', JSON.stringify(cart));
        } catch (e) {
            console.error('Error parsing cart data from localStorage:', e);
            cart = {}; // Reset keranjang jika parsing gagal
            showCustomAlert('Terjadi kesalahan saat memuat data keranjang dari penyimpanan lokal.', 'danger', 'Error');
        }
    } else {
        cart = {}; // Inisialisasi keranjang kosong jika tidak ada data di localStorage
        console.log('No cart data found in localStorage. Initialized empty cart.');
    }
}

// Simpan data keranjang ke localStorage
function saveCartToLocalStorage() {
    console.log('Saving cart to localStorage:', JSON.stringify(cart));
    localStorage.setItem(localStorageKey, JSON.stringify(cart));
}

// --- Fungsi Render Keranjang (Ini adalah fungsi utama untuk menampilkan isi cart) ---
function renderCart() {
    console.log('Rendering cart. Current cart state:', JSON.stringify(cart));

    // Periksa apakah elemen penting ada sebelum melanjutkan
    if (!cartItemsContainer || !hiddenInputsContainer || !totalItemsSpan || !grandTotalSpan || !emptyCartMessage || !submitOrderBtn) {
        console.error('Cannot render cart: One or more essential DOM elements are missing.');
        return;
    }

    // Kosongkan tampilan keranjang dan input hidden sebelum menggambar ulang
    cartItemsContainer.innerHTML = '';
    hiddenInputsContainer.innerHTML = '';
    let totalItems = 0;
    let grandTotal = 0;

    const cartKeys = Object.keys(cart);

    if (cartKeys.length === 0) {
        console.log('Cart is empty. Showing empty message.');
        emptyCartMessage.style.display = 'block';
    } else {
        console.log(`Cart has ${cartKeys.length} unique items. Proceeding to render.`);
        emptyCartMessage.style.display = 'none';

        // Urutkan kunci untuk mempertahankan urutan yang konsisten
        const sortedCartKeys = cartKeys.sort();

        // Loop melalui setiap item di objek cart
        // Perubahan: gunakan idx (0, 1, 2, ...) untuk indexing hidden input
        sortedCartKeys.forEach((menuId, idx) => {
            const item = cart[menuId];
            const subtotal = item.price * item.quantity;

            console.log('Rendering item:', item);

            // Buat elemen HTML untuk representasi satu item di keranjang
            const itemElement = `
                <div class="cart-item mb-3 p-2 border rounded d-flex align-items-center" data-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-img me-3 rounded" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="cart-item-details flex-grow-1">
                        <h6 class="mb-0">${item.name}</h6>
                        <p class="text-muted small mb-1">${formatRupiah(item.price)}</p>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary decrease-quantity me-1" data-id="${item.id}"><i class="bi bi-dash-lg"></i></button>
                            <input type="number" class="form-control form-control-sm quantity-input text-center" data-id="${item.id}" value="${item.quantity}" min="1" style="width: 60px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary increase-quantity ms-1" data-id="${item.id}"><i class="bi bi-plus-lg"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item ms-auto" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="mt-2">
                            <textarea class="form-control form-control-sm item-notes" data-id="${item.id}" placeholder="Catatan (Opsional)">${item.notes || ''}</textarea>
                        </div>
                    </div>
                </div>
            `;
            // Tambahkan HTML item ke dalam kontainer keranjang
            cartItemsContainer.insertAdjacentHTML('beforeend', itemElement);

            // === BEGIN PERBAIKAN HIDDEN INPUTS ===
            // Menggunakan idx alih‐alih item.id agar Laravel mem-parse sebagai numerical array [0], [1], dst.
            const hiddenInputMenuId   = `<input type="hidden" name="items[${idx}][menu_id]"  value="${item.id}">`;
            const hiddenInputQuantity = `<input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">`;
            const hiddenInputNotes    = `<input type="hidden" name="items[${idx}][notes]"    value="${item.notes || ''}">`;

            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputMenuId);
            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputQuantity);
            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputNotes);
            // === END PERBAIKAN HIDDEN INPUTS ===

            // Perbarui total jumlah item dan total harga
            totalItems += item.quantity;
            grandTotal += subtotal;
        });
    }

    // Perbarui tampilan total
    totalItemsSpan.textContent = totalItems;
    grandTotalSpan.textContent = formatRupiah(grandTotal);

    // Simpan status keranjang ke localStorage setelah rendering
    saveCartToLocalStorage();

    // Perbarui status tombol submit setelah render
    checkSubmitButtonStatus();
    console.log('Cart rendering complete.');
}

// --- Fungsi Tambah Item ke Keranjang ---
export function addItemToCart(menuItem) {
    console.log('Attempting to add item to cart:', menuItem);
    // Validasi dasar input
    if (!menuItem || !menuItem.id || !menuItem.name || isNaN(menuItem.price) || !menuItem.image) {
        console.error('Invalid menu item data received:', menuItem);
        showCustomAlert('Data menu tidak valid. Tidak dapat menambahkan item ke keranjang.', 'danger', 'Error Internal');
        return;
    }

    const menuId = menuItem.id;

    // Jika item sudah ada di keranjang, tambahkan kuantitasnya
    if (cart[menuId]) {
        cart[menuId].quantity++;
        console.log(`Item ${menuId} already in cart. Increased quantity to ${cart[menuId].quantity}.`);
    } else {
        // Jika item belum ada, tambahkan item baru dengan kuantitas 1
        cart[menuId] = {
            id: menuItem.id,
            name: menuItem.name,
            price: menuItem.price,
            image: menuItem.image,
            quantity: 1,
            notes: '' // Tambahkan properti notes
        };
        console.log(`Item ${menuId} added to cart for the first time.`);
    }

    // Render ulang tampilan keranjang setelah ada perubahan
    renderCart();
    console.log('Item added and cart re-rendered.');
}

// --- Fungsi Hapus Item dari Keranjang ---
function removeItemFromCart(menuId) {
    console.log('Attempting to remove item from cart with ID:', menuId);
    if (cart[menuId]) {
        delete cart[menuId];
        console.log(`Item ${menuId} removed from cart.`);
        // Render ulang tampilan keranjang setelah item dihapus
        renderCart();
        console.log('Item removed and cart re-rendered.');
    } else {
        console.warn(`Item with ID ${menuId} not found in cart.`);
    }
}

// --- Fungsi Perbarui Kuantitas Item di Keranjang ---
function updateItemQuantity(menuId, newQuantity) {
    console.log(`Attempting to update quantity for item ${menuId} to ${newQuantity}.`);
    const quantity = parseInt(newQuantity, 10);

    // Validasi kuantitas
    if (isNaN(quantity) || quantity < 1) {
        console.warn(`Invalid quantity ${newQuantity} for item ${menuId}. Removing item.`);
        removeItemFromCart(menuId); // Hapus item jika kuantitas tidak valid (misal < 1)
        return;
    }

    if (cart[menuId]) {
        cart[menuId].quantity = quantity;
        console.log(`Quantity for item ${menuId} updated to ${quantity}.`);
        // Render ulang tampilan keranjang setelah kuantitas diperbarui
        renderCart();
        console.log('Quantity updated and cart re-rendered.');
    } else {
        console.warn(`Item with ID ${menuId} not found in cart. Cannot update quantity.`);
    }
}

// --- Fungsi Perbarui Catatan Item di Keranjang ---
function updateItemNotes(menuId, notes) {
    console.log(`Attempting to update notes for item ${menuId}: "${notes}".`);
    if (cart[menuId]) {
        cart[menuId].notes = notes;
        console.log(`Notes for item ${menuId} updated.`);
        // Render ulang keranjang (hanya untuk mengupdate input hidden)
        renderCart(); // Re-render untuk memperbarui input hidden
        console.log('Notes updated and cart re-rendered (for hidden input).');
    } else {
        console.warn(`Item with ID ${menuId} not found in cart. Cannot update notes.`);
    }
}

// --- Event Listeners untuk Interaksi Keranjang (Menggunakan Event Delegation) ---
function attachCartEventListeners() {
    console.log('Attaching cart event listeners...');
    // Menggunakan event delegation pada kontainer keranjang
    if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', function(event) {
            const target = event.target;
            const button = target.closest('button'); // Cari elemen button terdekat dari target klik

            if (button) {
                const menuId = button.dataset.id;
                console.log(`Button clicked in cart. Button class: ${button.className}, Item ID: ${menuId}`);

                if (button.classList.contains('increase-quantity')) {
                    // Tombol tambah kuantitas
                    if (cart[menuId]) {
                        updateItemQuantity(menuId, cart[menuId].quantity + 1);
                    }
                } else if (button.classList.contains('decrease-quantity')) {
                    // Tombol kurang kuantitas
                    if (cart[menuId] && cart[menuId].quantity > 1) {
                        updateItemQuantity(menuId, cart[menuId].quantity - 1);
                    } else if (cart[menuId] && cart[menuId].quantity === 1) {
                        // Hapus item jika kuantitas menjadi 0
                        removeItemFromCart(menuId);
                    }
                } else if (button.classList.contains('remove-item')) {
                    // Tombol hapus item
                    removeItemFromCart(menuId);
                }
            }
        });

        // Listener untuk perubahan input kuantitas
        cartItemsContainer.addEventListener('input', function(event) {
            const target = event.target;
            if (target.classList.contains('quantity-input')) {
                const menuId = target.dataset.id;
                const newQuantity = parseInt(target.value, 10);
                console.log(`Quantity input changed for item ${menuId}. New value: ${target.value}`);
                updateItemQuantity(menuId, newQuantity);
            } else if (target.classList.contains('item-notes')) {
                // Listener untuk perubahan textarea catatan
                const menuId = target.dataset.id;
                const newNotes = target.value;
                console.log(`Notes input changed for item ${menuId}. New value: "${newNotes}"`);
                updateItemNotes(menuId, newNotes);
            }
        });
        console.log('Cart click and input listeners attached.');
    } else {
        console.warn('cartItemsContainer not found. Cart event listeners not attached.');
    }
}

// Export fungsi getCartItems agar bisa diakses oleh modul lain (misal form_submit)
export function getCartItems() {
    console.log('getCartItems called. Returning current cart state.');
    return cart;
}

// Export fungsi clearCart agar bisa diakses oleh modul lain (misal form_submit setelah sukses pembayaran)
export function clearCart() {
    console.log('Clearing cart...');
    cart = {}; // Reset objek keranjang
    saveCartToLocalStorage(); // Bersihkan localStorage
    renderCart(); // Perbarui UI
    console.log('Cart cleared.');
}
