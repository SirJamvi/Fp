// resources/js/pelayan_dashboard/main.js

// Import initialization functions from other modules
import { initCart, addItemToCart } from './cart_manager'; // Need addItemToCart to pass to listener
import { initMenuFilter, attachAddToCartListeners } from './menu_filter'; // Need attachAddToCartListeners from here
import { initTableInfo } from './table_info';
// Import checkSubmitButtonStatus and processPaymentAjax from form_submit.js
import { initFormSubmit, checkSubmitButtonStatus, processPaymentAjax } from './form_submit';
// Import showPaymentModal from payment_modal.js
import { initPaymentModal, showPaymentModal } from './payment_modal';

// Ensure jQuery is available globally if needed by Bootstrap or other scripts
// Your layout should load jQuery before app.js
// window.$ = window.jQuery; // Uncomment if jQuery is not globally available


// Wait for the DOM to be fully loaded before initializing
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded. Initializing Pelayan Dashboard scripts.'); // Log start

    // --- Get all necessary DOM elements ---
    // Pass these elements to the initialization functions of each module
    console.log('Attempting to get DOM elements...'); // Added Log
    const elements = {
        // Cart Manager elements
        cartItemsContainer: document.getElementById('cartItems'),
        emptyCartMessage: document.getElementById('emptyCartMessage'),
        totalItemsSpan: document.getElementById('totalItems'),
        grandTotalSpan: document.getElementById('grandTotal'),
        hiddenInputsContainer: document.getElementById('hiddenInputs'),
        submitOrderBtn: document.getElementById('submitOrderBtn'), // Also needed by form_submit

        // Menu Filter elements
        menuSearchInput: document.getElementById('menuSearch'),
        categoryTabs: document.getElementById('categoryTabs'),

        // Table Info elements
        mejaSelect: document.getElementById('meja_id'),
        selectedTableInfoDiv: document.getElementById('selectedTableInfo'),
        jumlahTamuInput: document.getElementById('jumlah_tamu'), // Also needed by form_submit

        // Form Submit elements
        orderForm: document.getElementById('orderForm'), // Main form
        processPaymentRouteInput: document.getElementById('processPaymentRoute'), // Get the payment route element
        orderSummaryRouteInput: document.getElementById('orderSummaryRoute'), // Get the new summary route element


        // Payment Modal elements
        paymentModalEl: document.getElementById('paymentModal'),
        paymentOptions: document.getElementById('paymentOptions'),
        cashPaymentForm: document.getElementById('cashPaymentForm'),
        qrisPaymentInfo: document.getElementById('qrisPaymentInfo'),
        uangDiterimaInput: document.getElementById('uangDiterima'),
        kembalianDisplay: document.getElementById('kembalianDisplay'),
        btnBayarCash: document.getElementById('btnBayarCash'),
        btnConfirmQris: document.getElementById('btnConfirmQris'),
        btnBackToOptions: document.getElementById('btnBackToOptions'),
        btnBackToOptionsQris: document.getElementById('btnBackToOptionsQris'),
        loadingIndicator: document.getElementById('loadingIndicator'), // Modal loading
        modalTotalBillSpan: document.getElementById('modalTotalBill'),
        // cashModalTotalBillSpan: document.getElementById('cashModalTotalBill'), // Removed if duplicated in HTML
        // qrisModalTotalBillSpan: document.getElementById('qrisModalTotalBill'), // Removed if duplicated in HTML
        modalKodeOrderStrong: document.getElementById('modalKodeOrder'),
        // cashModalKodeOrderStrong: document.getElementById('cashModalKodeOrder'), // Removed if duplicated in HTML
        // qrisModalKodeOrderStrong: document.getElementById('qrisModalKodeOrder'), // Removed if duplicated in HTML
        paymentSuccessMessage: document.getElementById('paymentSuccessMessage'),
        paymentErrorMessage: document.getElementById('paymentErrorMessage'),
        btnCash: document.getElementById('btnCash'), // Button to select Cash
        btnQris: document.getElementById('btnQris'), // Button to select QRIS
        paymentSuccessActions: document.getElementById('paymentSuccessActions'), // Get success actions container
        btnBackToDashboard: document.getElementById('btnBackToDashboard'), // Get back to dashboard button
        btnViewSummary: document.getElementById('btnViewSummary'), // Get view summary button
        loadingIndicatorModal: document.getElementById('loadingIndicator') // Alias for modal loading
    };
    console.log('Finished getting DOM elements.'); // Added Log


    // --- Check if essential elements are found ---
    // Add checks here to quickly see if required elements are missing
    const requiredElements = [
        'cartItemsContainer', 'emptyCartMessage', 'totalItemsSpan',
        'grandTotalSpan', 'hiddenInputsContainer', 'submitOrderBtn',
        'mejaSelect', 'jumlahTamuInput', 'orderForm', 'paymentModalEl', // paymentModalEl is required for modal init
        'processPaymentRouteInput', 'orderSummaryRouteInput', // New: Required route elements
        'paymentOptions', 'cashPaymentForm', 'qrisPaymentInfo', 'uangDiterimaInput',
        'kembalianDisplay', 'btnBayarCash', 'btnConfirmQris', 'btnBackToOptions',
        'btnBackToOptionsQris', 'loadingIndicator', 'modalTotalBillSpan',
        // 'cashModalTotalBillSpan', 'qrisModalTotalBillSpan', // Removed if duplicated in HTML
        'modalKodeOrderStrong',
        // 'cashModalKodeOrderStrong', 'qrisModalKodeOrderStrong', // Removed if duplicated in HTML
        'paymentSuccessMessage', 'paymentErrorMessage', 'btnCash', 'btnQris',
        'paymentSuccessActions', 'btnBackToDashboard', 'btnViewSummary' // Include new elements
    ];
    console.log('Checking for required DOM elements...'); // Added Log
    requiredElements.forEach(id => {
        if (!elements[id]) {
            console.error(`ERROR: Required DOM element "${id}" not found! Script may not function correctly.`);
        } else {
             console.log(`Required DOM element "${id}" found.`); // Added Log for confirmation
        }
    });
     console.log('Finished checking required DOM elements.'); // Added Log


    // --- Initialize Modules ---
    // Pass the collected elements to each module's init function
    // Ensure init functions are called in a logical order if they have dependencies
    console.log('Initializing modules...'); // Added Log
    initCart(elements); // initCart needs elements including submitOrderBtn for checkSubmitButtonStatus
    initMenuFilter(elements); // initMenuFilter needs elements to find search/category tabs
    initTableInfo(elements); // initTableInfo needs elements for table select
    initFormSubmit(elements); // initFormSubmit needs form, submit button, meja select, jumlah tamu, and route elements
    initPaymentModal(elements); // initPaymentModal needs modal elements
    console.log('Finished initializing modules.'); // Added Log


    // --- Attach Listeners that Span Modules ---
    // Attach listeners for "Add to Cart" buttons AFTER menu items are in the DOM
    // Pass the addItemToCart function from cart_manager so menu_filter can call it
    console.log('Attaching cross-module listeners...'); // Added Log
    attachAddToCartListeners(addItemToCart);
    console.log('Finished attaching cross-module listeners.'); // Added Log


    // Ensure checkSubmitButtonStatus is callable globally or passed where needed
    // It's exported from form_submit.js and imported here.
    // Modules that need to trigger it (like cart_manager, table_info)
    // should import it directly or call it via main.js if preferred.
    // In the current structure, cart_manager and table_info import checkSubmitButtonStatus directly.
    // So we don't need to export it from here.


    // Ensure processPaymentAjax is callable from payment_modal.js
    // It's exported from form_submit.js and imported here.
    // payment_modal.js needs to import it directly.


    console.log('Pelayan Dashboard scripts initialization complete.'); // Log end

    // Initial check for submit button status on page load
    console.log('Initial checkSubmitButtonStatus call on DOMContentLoaded...'); // Added Log
    checkSubmitButtonStatus();
    console.log('Script finished DOMContentLoaded execution.'); // Added Log
});
