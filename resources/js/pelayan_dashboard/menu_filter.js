// resources/js/pelayan_dashboard/menu_filter.js

// Import helper functions or dependencies if needed
// import { ucfirst } from './utils'; // Example if you have a utils file

// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let menuSearchInput;
let categoryTabs;

// Export fungsi inisialisasi untuk menerima elemen DOM
export function initMenuFilter(elements) {
    console.log('Initializing Menu Filter module...'); // Added Log
    menuSearchInput = elements.menuSearchInput;
    categoryTabs = elements.categoryTabs;

    console.log('Menu Filter initialized. Attaching listeners...'); // Added Log
    attachMenuFilterListeners(); // Attach listeners after getting elements
}


// Fungsi untuk Filter Menu (Search dan Kategori)
export function filterMenus() {
    console.log('Executing filterMenus...'); // Added Log
    if (!menuSearchInput) {
         console.warn('menuSearchInput not found. Skipping menu filtering.');
         return;
    }
    const searchTerm = menuSearchInput.value.toLowerCase();
    // Dapatkan ID tab kategori yang sedang aktif
    const activeTab = categoryTabs ? categoryTabs.querySelector('.nav-link.active') : null;
    const activeCategoryId = activeTab ? activeTab.id.replace('-tab', '') : 'all';

    console.log(`Filtering menus. Search: "${searchTerm}", Category Tab: "${activeCategoryId}"`); // Added Log filtering parameters

    // Iterasi melalui semua elemen menu item
    const menuItems = document.querySelectorAll('.menu-item-col');
    console.log(`Found ${menuItems.length} menu items to filter.`); // Added Log number of items
    menuItems.forEach(menuItemCol => {
        const menuName = menuItemCol.dataset.name;
        const menuCategory = menuItemCol.dataset.category;

        const matchesSearch = menuName ? menuName.includes(searchTerm) : false;
        const matchesCategory = (activeCategoryId === 'all' || (menuCategory && menuCategory === activeCategoryId));

        if (matchesSearch && matchesCategory) {
            menuItemCol.style.display = '';
        } else {
            menuItemCol.style.display = 'none';
        }
    });
    console.log('Menu filtering complete.'); // Added Log completion
}

// Pasang listener ke input search menu dan tab kategori
function attachMenuFilterListeners() {
    console.log('Attaching menu filter listeners...'); // Added Log start
    if (menuSearchInput) {
        menuSearchInput.addEventListener('input', filterMenus);
        console.log('Menu search input listener attached.'); // Added Log listener attachment
    } else {
        console.warn('menuSearchInput not found. Search listener not attached.');
    }

    // Listener untuk tab kategori (menggunakan event delegation pada induknya)
    // Pastikan elemen categoryTabs ada dan Bootstrap JS (Tab component) dimuat
    if (categoryTabs && typeof bootstrap !== 'undefined' && typeof bootstrap.Tab !== 'undefined') {
        console.log('Attaching Bootstrap Tab shown.bs.tab listener on categoryTabs.'); // Added Log listener attachment
         // Use jQuery for Bootstrap events if you are mixing jQuery and native JS
         $(categoryTabs).on('shown.bs.tab', function (event) { // Using jQuery for Bootstrap event
            console.log('Bootstrap Tab shown.bs.tab event triggered.'); // Added Log event trigger
            filterMenus(); // Call filterMenus every time a new tab is shown
        });
    } else {
         console.warn('categoryTabs element or Bootstrap Tab component not found. Tab filtering on change disabled.');
    }
    console.log('Finished attaching menu filter listeners.'); // Added Log end
}

// Function to attach add-to-cart listeners to menu buttons
// This function is called from main.js after DOM is ready and menus are loaded
export function attachAddToCartListeners(addItemToCartCallback) {
    console.log('Attaching add to cart listeners...'); // Log start
    const buttons = document.querySelectorAll('.add-to-cart-btn');
    console.log(`Found ${buttons.length} add to cart buttons.`); // Log number of buttons found

    if (buttons.length === 0) {
        console.warn('No add to cart buttons found.'); // Added Log
        return;
    }

    buttons.forEach(button => {
        // Remove existing listener to prevent duplicates if called multiple times
        // This is important if menus are dynamically re-rendered
        // button.removeEventListener('click', handleAddToCartClick); // Need to define handleAddToCartClick

        // Add new listener
        button.addEventListener('click', function() {
            console.log('Add to cart button clicked!'); // Log button click
            const id = this.dataset.id;
            const name = this.dataset.name;
            // Pastikan harga di-parse sebagai float
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;

            console.log('Item data from button:', { id, name, price, image }); // Log item data

            // Validasi dasar data menu
            if (!id || !name || isNaN(price)) {
                console.error('Missing or invalid data attributes for add-to-cart button:', this.dataset); // Log error
                alert('Gagal menambahkan menu. Data menu tidak lengkap atau tidak valid.');
                return;
            }

            // Call the addItemToCart function passed from main.js
            if (typeof addItemToCartCallback === 'function') {
                console.log('Calling addItemToCartCallback...'); // Log callback call
                addItemToCartCallback({ id, name, price, image });
            } else {
                console.error('addItemToCartCallback is not a function or not provided.'); // Log error
                alert('Internal error: Cannot add item to cart.');
            }
        });
         console.log(`Listener attached to button with ID: ${button.dataset.id}`); // Added Log for each button
    });
    console.log('Finished attaching add to cart listeners.'); // Log end
}