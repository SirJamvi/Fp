// resources/js/pelayan_dashboard/cart_manager.js

// Import dependencies (like checkSubmitButtonStatus from form_submit)
import { checkSubmitButtonStatus } from './form_submit';
// Import formatRupiah from payment_modal
import { formatRupiah } from './payment_modal'; // Import formatRupiah


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
let submitOrderBtn; // Need this to call checkSubmitButtonStatus


// Export fungsi inisialisasi untuk menerima elemen DOM
export function initCart(elements) {
    console.log('Initializing Cart Manager module...'); // Added Log
    cartItemsContainer = elements.cartItemsContainer;
    emptyCartMessage = elements.emptyCartMessage;
    totalItemsSpan = elements.totalItemsSpan;
    grandTotalSpan = elements.grandTotalSpan;
    hiddenInputsContainer = elements.hiddenInputsContainer;
    submitOrderBtn = elements.submitOrderBtn; // Get the submit button element

    console.log('Cart Manager initialized.'); // Added Log

    // Load cart from localStorage on initialization
    loadCartFromLocalStorage();
    // Initial render of the cart
    renderCart();

    // Attach event listeners for cart item interactions (delegation)
    attachCartEventListeners();
}

// Load cart data from localStorage
function loadCartFromLocalStorage() {
    console.log('Attempting to load cart from localStorage...'); // Added Log
    const savedCart = localStorage.getItem(localStorageKey);
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
            console.log('Cart loaded from localStorage:', JSON.stringify(cart)); // Added Log
        } catch (e) {
            console.error('Error parsing cart data from localStorage:', e); // Added Log
            cart = {}; // Reset cart if parsing fails
        }
    } else {
        cart = {}; // Initialize empty cart if no data in localStorage
        console.log('No cart data found in localStorage. Initialized empty cart.'); // Added Log
    }
}

// Save cart data to localStorage
function saveCartToLocalStorage() {
    console.log('Saving cart to localStorage:', JSON.stringify(cart)); // Added Log
    localStorage.setItem(localStorageKey, JSON.stringify(cart));
}

// --- Fungsi Render Keranjang (Ini adalah fungsi utama untuk menampilkan isi cart) ---
function renderCart() {
    console.log('Rendering cart. Current cart state:', JSON.stringify(cart)); // Debug log: CRUCIAL - What is cart state at start of render?

    // Check if essential elements exist before proceeding
    if (!cartItemsContainer || !hiddenInputsContainer || !totalItemsSpan || !grandTotalSpan || !emptyCartMessage || !submitOrderBtn) {
         console.error('Cannot render cart: One or more essential DOM elements are missing.'); // Added Log
         return;
    }


    // Kosongkan tampilan keranjang dan input hidden sebelum menggambar ulang
    cartItemsContainer.innerHTML = '';
    hiddenInputsContainer.innerHTML = '';
    let totalItems = 0;
    let grandTotal = 0;

    const cartKeys = Object.keys(cart);

    if (cartKeys.length === 0) {
        console.log('Cart is empty. Showing empty message.'); // Added Log
        emptyCartMessage.style.display = 'block';
    } else {
        console.log(`Cart has ${cartKeys.length} unique items. Proceeding to render.`); // Added Log
        emptyCartMessage.style.display = 'none';

        // Sort keys to maintain consistent order (optional but good practice)
        const sortedCartKeys = cartKeys.sort();

        // Loop melalui setiap item di objek cart
        sortedCartKeys.forEach(menuId => {
            const item = cart[menuId];
            const subtotal = item.price * item.quantity;

            console.log('Rendering item:', item); // Added Log for each item

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

            // Buat input hidden untuk dikirim ke backend saat form disubmit
            // Menggunakan menuId sebagai bagian dari nama array untuk memudahkan backend
            const hiddenInputMenuId = `<input type="hidden" name="items[${item.id}][menu_id]" value="${item.id}">`;
            const hiddenInputQuantity = `<input type="hidden" name="items[${item.id}][quantity]" value="${item.quantity}" class="item-quantity-input-${item.id}">`; // Tambah class untuk mudah update
            const hiddenInputNotes = `<input type="hidden" name="items[${item.id}][notes]" value="${item.notes || ''}" class="item-notes-input-${item.id}">`; // Tambah class untuk mudah update

            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputMenuId);
            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputQuantity);
            hiddenInputsContainer.insertAdjacentHTML('beforeend', hiddenInputNotes);

            // Perbarui total jumlah item dan total harga
            totalItems += item.quantity;
            grandTotal += subtotal;
        });
    }

    // Perbarui tampilan total
    totalItemsSpan.textContent = totalItems;
    grandTotalSpan.textContent = formatRupiah(grandTotal); // Use formatRupiah

    // Save cart state to localStorage after rendering
    saveCartToLocalStorage();

    // Perbarui status tombol submit setelah render
    checkSubmitButtonStatus();
    console.log('Cart rendering complete.'); // Added Log
}

// --- Fungsi Tambah Item ke Keranjang ---
export function addItemToCart(menuItem) {
    console.log('Attempting to add item to cart:', menuItem); // Added Log
    // Validasi dasar input
    if (!menuItem || !menuItem.id || !menuItem.name || isNaN(menuItem.price)) {
        console.error('Invalid menu item data received:', menuItem); // Added Log
        alert('Data menu tidak valid.');
        return;
    }

    const menuId = menuItem.id;

    // Jika item sudah ada di keranjang, tambahkan kuantitasnya
    if (cart[menuId]) {
        cart[menuId].quantity++;
        console.log(`Item ${menuId} already in cart. Increased quantity to ${cart[menuId].quantity}.`); // Added Log
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
        console.log(`Item ${menuId} added to cart for the first time.`); // Added Log
    }

    // Render ulang tampilan keranjang setelah ada perubahan
    renderCart();
    console.log('Item added and cart re-rendered.'); // Added Log
}

// --- Fungsi Hapus Item dari Keranjang ---
function removeItemFromCart(menuId) {
    console.log('Attempting to remove item from cart with ID:', menuId); // Added Log
    if (cart[menuId]) {
        delete cart[menuId];
        console.log(`Item ${menuId} removed from cart.`); // Added Log
        // Render ulang tampilan keranjang setelah item dihapus
        renderCart();
        console.log('Item removed and cart re-rendered.'); // Added Log
    } else {
        console.warn(`Item with ID ${menuId} not found in cart.`); // Added Log
    }
}

// --- Fungsi Perbarui Kuantitas Item di Keranjang ---
function updateItemQuantity(menuId, newQuantity) {
     console.log(`Attempting to update quantity for item ${menuId} to ${newQuantity}.`); // Added Log
    const quantity = parseInt(newQuantity, 10);

    // Validasi kuantitas
    if (isNaN(quantity) || quantity < 1) {
        console.warn(`Invalid quantity ${newQuantity} for item ${menuId}. Removing item.`); // Added Log
        removeItemFromCart(menuId); // Hapus item jika kuantitas tidak valid (misal < 1)
        return;
    }

    if (cart[menuId]) {
        cart[menuId].quantity = quantity;
         console.log(`Quantity for item ${menuId} updated to ${quantity}.`); // Added Log
        // Render ulang tampilan keranjang setelah kuantitas diperbarui
        renderCart();
        console.log('Quantity updated and cart re-rendered.'); // Added Log
    } else {
        console.warn(`Item with ID ${menuId} not found in cart. Cannot update quantity.`); // Added Log
    }
}

// --- Fungsi Perbarui Catatan Item di Keranjang ---
function updateItemNotes(menuId, notes) {
    console.log(`Attempting to update notes for item ${menuId}: "${notes}".`); // Added Log
    if (cart[menuId]) {
        cart[menuId].notes = notes;
        console.log(`Notes for item ${menuId} updated.`); // Added Log
        // Render ulang keranjang (hanya untuk mengupdate input hidden)
        // Tampilan textarea sudah terupdate otomatis oleh browser
        renderCart(); // Re-render to update hidden input
        console.log('Notes updated and cart re-rendered (for hidden input).'); // Added Log
    } else {
        console.warn(`Item with ID ${menuId} not found in cart. Cannot update notes.`); // Added Log
    }
}


// --- Event Listeners untuk Interaksi Keranjang (Menggunakan Event Delegation) ---
function attachCartEventListeners() {
    console.log('Attaching cart event listeners...'); // Added Log
    // Menggunakan event delegation pada kontainer keranjang
    if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', function(event) {
            const target = event.target;
            const button = target.closest('button'); // Cari elemen button terdekat dari target klik

            if (button) {
                const menuId = button.dataset.id;
                 console.log(`Button clicked in cart. Button class: ${button.className}, Item ID: ${menuId}`); // Added Log

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
                 console.log(`Quantity input changed for item ${menuId}. New value: ${target.value}`); // Added Log
                updateItemQuantity(menuId, newQuantity);
            } else if (target.classList.contains('item-notes')) {
                 // Listener untuk perubahan textarea catatan
                 const menuId = target.dataset.id;
                 const newNotes = target.value;
                 console.log(`Notes input changed for item ${menuId}. New value: "${newNotes}"`); // Added Log
                 updateItemNotes(menuId, newNotes);
            }
        });
         console.log('Cart click and input listeners attached.'); // Added Log
    } else {
         console.warn('cartItemsContainer not found. Cart event listeners not attached.'); // Added Log
    }
}

// Export fungsi getCartItems agar bisa diakses oleh modul lain (misal form_submit)
export function getCartItems() {
     console.log('getCartItems called. Returning current cart state.'); // Added Log
    return cart;
}

// Export fungsi clearCart agar bisa diakses oleh modul lain (misal form_submit setelah sukses pembayaran)
export function clearCart() {
    console.log('Clearing cart...'); // Added Log
    cart = {}; // Reset cart object
    saveCartToLocalStorage(); // Clear localStorage
    renderCart(); // Update UI
    console.log('Cart cleared.'); // Added Log
}

// Export checkSubmitButtonStatus from here so other modules can import it
// This function is actually defined in form_submit.js, so we need to re-export it
// Or, more cleanly, the modules that need it should import it directly from form_submit.js
// In the current structure, cart_manager imports checkSubmitButtonStatus directly.
// So we don't need to export it from here.

// Export formatRupiah from payment_modal so cart_manager can use it
// This is handled by importing formatRupiah directly in cart_manager.js
