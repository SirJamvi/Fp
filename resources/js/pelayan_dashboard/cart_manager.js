// resources/js/pelayan_dashboard/cart_manager.js

// Import dependencies
import { checkSubmitButtonStatus } from './form_submit';
import { formatRupiah } from './payment_modal';
import { showCustomAlert } from '../utils';

// --- State (Keranjang) ---
let cart = {};
const localStorageKey = 'pelayanCart';

// --- Elemen DOM ---
let cartItemsContainer;
let emptyCartMessage;
let totalItemsSpan;
let grandTotalSpan;
let hiddenInputsContainer;
let submitOrderBtn;

// Export fungsi inisialisasi
export function initCart(elements) {
    console.log('Initializing Cart Manager module...');
    cartItemsContainer = elements.cartItemsContainer;
    emptyCartMessage = elements.emptyCartMessage;
    totalItemsSpan = elements.totalItemsSpan;
    grandTotalSpan = elements.grandTotalSpan;
    hiddenInputsContainer = elements.hiddenInputsContainer;
    submitOrderBtn = elements.submitOrderBtn;

    loadCartFromLocalStorage();
    renderCart();
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
            cart = {};
            showCustomAlert('Terjadi kesalahan saat memuat data keranjang dari penyimpanan lokal.', 'danger', 'Error');
        }
    } else {
        cart = {};
        console.log('No cart data found in localStorage. Initialized empty cart.');
    }
}

// Simpan data keranjang ke localStorage
function saveCartToLocalStorage() {
    console.log('Saving cart to localStorage:', JSON.stringify(cart));
    localStorage.setItem(localStorageKey, JSON.stringify(cart));
}

// Fungsi Render Keranjang yang Diperbaiki
function renderCart() {
    // Simpan state textarea yang aktif
    const activeElement = document.activeElement;
    let activeTextareaId = null;
    let activeTextareaValue = null;
    let cursorPosition = 0;
    
    if (activeElement && activeElement.classList.contains('item-notes')) {
        activeTextareaId = activeElement.dataset.id;
        activeTextareaValue = activeElement.value;
        cursorPosition = activeElement.selectionStart;
    }

    console.log('Rendering cart. Current cart state:', JSON.stringify(cart));

    if (!cartItemsContainer || !hiddenInputsContainer || !totalItemsSpan || !grandTotalSpan || !emptyCartMessage || !submitOrderBtn) {
        console.error('Cannot render cart: One or more essential DOM elements are missing.');
        return;
    }

    cartItemsContainer.innerHTML = '';
    hiddenInputsContainer.innerHTML = '';
    let totalItems = 0;
    let grandTotal = 0;

    const cartKeys = Object.keys(cart);

    if (cartKeys.length === 0) {
        console.log('Cart is empty. Showing empty message.');
        emptyCartMessage.style.display = 'block';
        submitOrderBtn.disabled = true;
    } else {
        console.log(`Cart has ${cartKeys.length} unique items. Proceeding to render.`);
        emptyCartMessage.style.display = 'none';
        submitOrderBtn.disabled = false;

        const sortedCartKeys = cartKeys.sort();

        sortedCartKeys.forEach((menuId, idx) => {
            const item = cart[menuId];
            const subtotal = item.price * item.quantity;

            console.log('Rendering item:', item);

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
            cartItemsContainer.insertAdjacentHTML('beforeend', itemElement);

            const hiddenInputMenuId = `<input type="hidden" name="items[${idx}][menu_id]" value="${item.id}">`;
            const hiddenInputQuantity = `<input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">`;
            const hiddenInputNotes = `<input type="hidden" name="items[${idx}][notes]" value="${item.notes || ''}">`;

            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputMenuId);
            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputQuantity);
            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputNotes);

            totalItems += item.quantity;
            grandTotal += subtotal;
        });

        // Kembalikan fokus ke textarea yang sebelumnya aktif
        if (activeTextareaId) {
            const textarea = document.querySelector(`.item-notes[data-id="${activeTextareaId}"]`);
            if (textarea) {
                textarea.value = activeTextareaValue;
                textarea.focus();
                textarea.selectionStart = cursorPosition;
                textarea.selectionEnd = cursorPosition;
            }
        }
    }

    totalItemsSpan.textContent = totalItems;
    grandTotalSpan.textContent = formatRupiah(grandTotal);

    saveCartToLocalStorage();
    checkSubmitButtonStatus();
    console.log('Cart rendering complete.');
}

// Fungsi Tambah Item ke Keranjang
export function addItemToCart(menuItem) {
    console.log('Attempting to add item to cart:', menuItem);
    if (!menuItem || !menuItem.id || !menuItem.name || isNaN(menuItem.price) || !menuItem.image) {
        console.error('Invalid menu item data received:', menuItem);
        showCustomAlert('Data menu tidak valid. Tidak dapat menambahkan item ke keranjang.', 'danger', 'Error Internal');
        return;
    }

    const menuId = menuItem.id;

    if (cart[menuId]) {
        cart[menuId].quantity++;
        console.log(`Item ${menuId} already in cart. Increased quantity to ${cart[menuId].quantity}.`);
    } else {
        cart[menuId] = {
            id: menuItem.id,
            name: menuItem.name,
            price: menuItem.price,
            image: menuItem.image,
            quantity: 1,
            notes: ''
        };
        console.log(`Item ${menuId} added to cart for the first time.`);
    }

    renderCart();
    console.log('Item added and cart re-rendered.');
}

// Fungsi Hapus Item dari Keranjang
function removeItemFromCart(menuId) {
    console.log('Attempting to remove item from cart with ID:', menuId);
    if (cart[menuId]) {
        delete cart[menuId];
        console.log(`Item ${menuId} removed from cart.`);
        renderCart();
        console.log('Item removed and cart re-rendered.');
    } else {
        console.warn(`Item with ID ${menuId} not found in cart.`);
    }
}

// Fungsi Perbarui Kuantitas Item di Keranjang
function updateItemQuantity(menuId, newQuantity) {
    console.log(`Attempting to update quantity for item ${menuId} to ${newQuantity}.`);
    const quantity = parseInt(newQuantity, 10);

    if (isNaN(quantity)) {
        console.warn(`Invalid quantity ${newQuantity} for item ${menuId}.`);
        return;
    }

    if (quantity < 1) {
        removeItemFromCart(menuId);
        return;
    }

    if (cart[menuId]) {
        cart[menuId].quantity = quantity;
        console.log(`Quantity for item ${menuId} updated to ${quantity}.`);
        renderCart();
        console.log('Quantity updated and cart re-rendered.');
    } else {
        console.warn(`Item with ID ${menuId} not found in cart. Cannot update quantity.`);
    }
}

// Fungsi Perbarui Catatan Item di Keranjang (Versi Diperbaiki)
function updateItemNotes(menuId, notes) {
    console.log(`Attempting to update notes for item ${menuId}: "${notes}".`);
    if (cart[menuId]) {
        cart[menuId].notes = notes;
        console.log(`Notes for item ${menuId} updated.`);
        
        // Perbarui input hidden terkait tanpa render ulang penuh
        const hiddenInputs = document.querySelectorAll(`input[name^="items"][name$="[notes]"]`);
        hiddenInputs.forEach(input => {
            // Cari index dari input notes
            const indexMatch = input.name.match(/items\[(\d+)\]\[notes\]/);
            if (indexMatch) {
                const index = indexMatch[1];
                // Cari input menu_id yang sesuai dengan index ini
                const menuIdInput = document.querySelector(`input[name="items[${index}][menu_id]"]`);
                if (menuIdInput && menuIdInput.value === menuId) {
                    input.value = notes;
                }
            }
        });
        
        saveCartToLocalStorage();
    } else {
        console.warn(`Item with ID ${menuId} not found in cart. Cannot update notes.`);
    }
}

// Event Listeners untuk Interaksi Keranjang (Versi Diperbaiki)
function attachCartEventListeners() {
    console.log('Attaching cart event listeners...');
    
    if (cartItemsContainer) {
        // Handle klik tombol
        cartItemsContainer.addEventListener('click', function(event) {
            const target = event.target;
            const button = target.closest('button');

            if (button) {
                const menuId = button.dataset.id;
                console.log(`Button clicked in cart. Button class: ${button.className}, Item ID: ${menuId}`);

                if (button.classList.contains('increase-quantity')) {
                    if (cart[menuId]) {
                        updateItemQuantity(menuId, cart[menuId].quantity + 1);
                    }
                } else if (button.classList.contains('decrease-quantity')) {
                    if (cart[menuId]) {
                        updateItemQuantity(menuId, cart[menuId].quantity - 1);
                    }
                } else if (button.classList.contains('remove-item')) {
                    removeItemFromCart(menuId);
                }
            }
        });

        // Handle perubahan input kuantitas
        cartItemsContainer.addEventListener('change', function(event) {
            const target = event.target;
            if (target.classList.contains('quantity-input')) {
                const menuId = target.dataset.id;
                const newQuantity = parseInt(target.value, 10);
                console.log(`Quantity input changed for item ${menuId}. New value: ${target.value}`);
                updateItemQuantity(menuId, newQuantity);
            }
        });

        // Handle input textarea catatan dengan debounce
        let notesTimeout;
        cartItemsContainer.addEventListener('input', function(event) {
            const target = event.target;
            if (target.classList.contains('item-notes')) {
                const menuId = target.dataset.id;
                const newNotes = target.value;
                
                // Clear timeout sebelumnya jika ada
                clearTimeout(notesTimeout);
                
                // Set timeout baru untuk debounce
                notesTimeout = setTimeout(() => {
                    console.log(`Notes input changed for item ${menuId}. New value: "${newNotes}"`);
                    updateItemNotes(menuId, newNotes);
                }, 500); // Debounce 500ms untuk memberi waktu lebih
            }
        });
        
        console.log('Cart click and input listeners attached.');
    } else {
        console.warn('cartItemsContainer not found. Cart event listeners not attached.');
    }
}

// Export fungsi getCartItems
export function getCartItems() {
    console.log('getCartItems called. Returning current cart state.');
    return cart;
}

// Export fungsi clearCart
export function clearCart() {
    console.log('Clearing cart...');
    cart = {};
    saveCartToLocalStorage();
    renderCart();
    console.log('Cart cleared.');
}