// resources/js/pelayan_dashboard/payment_modal.js

// Import helper functions or dependencies if needed
// import { formatRupiah } from './utils'; // Example if you have a utils file
// Import the payment processing AJAX function from form_submit.js
import { processPaymentAjax } from './form_submit';


// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let paymentModalEl;
let paymentOptions;
let cashPaymentForm;
let qrisPaymentInfo;
let uangDiterimaInput;
let kembalianDisplay;
let btnBayarCash;
let btnConfirmQris;
let btnBackToOptions;
let btnBackToOptionsQris;
let loadingIndicator;
let modalTotalBillSpan;
// let cashModalTotalBillSpan; // Removed if duplicated in HTML, check dashboard_blade_modal_layout_fix
// let qrisModalTotalBillSpan; // Removed if duplicated in HTML, check dashboard_blade_modal_layout_fix
let modalKodeOrderStrong;
// let cashModalKodeOrderStrong; // Removed if duplicated in HTML, check dashboard_blade_modal_layout_fix
// let qrisModalKodeOrderStrong; // Removed if duplicated in HTML, check dashboard_blade_modal_layout_fix
let paymentSuccessMessage;
let paymentErrorMessage;
let btnCash; // Button to select Cash
let btnQris; // Button to select QRIS
// NOTE: In this older version, paymentSuccessActions, btnBackToDashboard, btnViewSummary might not be used here yet


// Bootstrap Modal object
let paymentModal = null;


// Export fungsi inisialisasi untuk menerima elemen DOM
export function initPaymentModal(elements) {
    console.log('Initializing Payment Modal module...'); // Added Log
    paymentModalEl = elements.paymentModalEl;
    paymentOptions = elements.paymentOptions;
    cashPaymentForm = elements.cashPaymentForm;
    qrisPaymentInfo = elements.qrisPaymentInfo;
    uangDiterimaInput = elements.uangDiterimaInput;
    kembalianDisplay = elements.kembalianDisplay;
    btnBayarCash = elements.btnBayarCash;
    btnConfirmQris = elements.btnConfirmQris;
    btnBackToOptions = elements.btnBackToOptions;
    btnBackToOptionsQris = elements.btnBackToOptionsQris;
    loadingIndicator = elements.loadingIndicator;
    modalTotalBillSpan = elements.modalTotalBillSpan;
    // cashModalTotalBillSpan = elements.cashModalTotalBillSpan; // Removed if duplicated in HTML
    // qrisModalTotalBillSpan = elements.qrisModalTotalBillSpan; // Removed if duplicated in HTML
    modalKodeOrderStrong = elements.modalKodeOrderStrong;
    // cashModalKodeOrderStrong = elements.cashModalKodeOrderStrong; // Removed if duplicated in HTML
    // qrisModalKodeOrderStrong = elements.qrisModalKodeOrderStrong; // Removed if duplicated in HTML
    paymentSuccessMessage = elements.paymentSuccessMessage;
    paymentErrorMessage = elements.paymentErrorMessage;
    btnCash = elements.btnCash;
    btnQris = elements.btnQris;
    // paymentSuccessActions = elements.paymentSuccessActions; // Might not be in elements yet
    // btnBackToDashboard = elements.btnBackToDashboard; // Might not be in elements yet
    // btnViewSummary = elements.btnViewSummary; // Might not be in elements yet


    console.log('Payment Modal initialized.'); // Log start initialization

    // Initialize Bootstrap Modal object
    // Check if modal element exists AND Bootstrap JS (specifically Modal) is available
    if (paymentModalEl && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
        console.log('Payment modal element found and Bootstrap.Modal is available.'); // Log success finding elements and Bootstrap
        try {
            paymentModal = new bootstrap.Modal(paymentModalEl);
            console.log('Bootstrap Modal object initialized successfully in payment_modal.js'); // Log successful modal creation
        } catch (e) {
            console.error('Error initializing Bootstrap Modal object in payment_modal.js:', e); // Log specific error during modal creation
        }
    } else {
        // Log which part is missing
        if (!paymentModalEl) console.error('Payment modal element #paymentModal NOT found.');
        if (typeof bootstrap === 'undefined') console.error('Bootstrap JS is NOT loaded.');
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'undefined') console.error('Bootstrap.Modal component is NOT available.');
        console.warn('Payment modal element or Bootstrap Modal class NOT found. Payment modal features disabled.'); // Log warning
    }

    attachPaymentModalListeners(); // Attach listeners
}

// Export Fungsi untuk memformat angka menjadi Rupiah
export function formatRupiah(angka) { // <-- Exported
    const num = parseFloat(angka) || 0;
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// Function to show the payment modal and populate with order data
export function showPaymentModal(reservasiId, totalBill, kodeReservasi) {
    console.log(`Attempting to show payment modal for Reservasi ID: ${reservasiId}, Total Bill: ${totalBill}, Kode Order: ${kodeReservasi}`); // Log attempt
    if (!paymentModal) {
        console.error('Payment modal object is not initialized. Cannot show modal.'); // Log error if modal object is null
        alert('Gagal menampilkan modal pembayaran.'); // Show user-friendly alert
        return;
    }

    // Store data on the modal element itself
    if (paymentModalEl) {
         paymentModalEl.dataset.reservasiId = reservasiId;
         paymentModalEl.dataset.totalBill = totalBill;
         paymentModalEl.dataset.kodeOrder = kodeReservasi;
         console.log('Stored order data on modal element:', paymentModalEl.dataset);
    } else {
         console.warn('Payment modal element not found, cannot store order data.');
    }


    // Update modal display with received data (using the single set of elements)
    if (modalTotalBillSpan) modalTotalBillSpan.textContent = formatRupiah(totalBill);
    if (modalKodeOrderStrong) modalKodeOrderStrong.textContent = kodeReservasi;

    // Reset modal state to show payment options
    if (paymentOptions) paymentOptions.style.display = 'block';
    if (cashPaymentForm) cashPaymentForm.style.display = 'none';
    if (qrisPaymentInfo) qrisPaymentInfo.style.display = 'none';
    if (uangDiterimaInput) uangDiterimaInput.value = '';
    if (kembalianDisplay) kembalianDisplay.textContent = formatRupiah(0);
    if (kembalianDisplay) kembalianDisplay.classList.remove('text-danger');
    if (kembalianDisplay) kembalianDisplay.classList.add('text-success');
    if (btnBayarCash) $(btnBayarCash).prop('disabled', true).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
    if (btnConfirmQris) $(btnConfirmQris).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
    if (btnBackToOptions) btnBackToOptions.style.display = 'block'; // Show back button for options
    if (btnBackToOptionsQris) btnBackToOptionsQris.style.display = 'none'; // Hide QRIS specific back button initially
    if (paymentSuccessMessage) paymentSuccessMessage.style.display = 'none';
    if (paymentErrorMessage) paymentErrorMessage.style.display = 'none';
    if (loadingIndicator) loadingIndicator.style.display = 'none';
    // if (paymentSuccessActions) paymentSuccessActions.style.display = 'none'; // Might not be in elements yet

    paymentModal.show(); // Show the modal
    console.log('Payment modal show() method called.'); // Log that show was called
}

// Function to hide the payment modal
export function hidePaymentModal() {
    console.log('Attempting to hide payment modal.'); // Log attempt
    if (paymentModal) {
        paymentModal.hide();
        console.log('Payment modal hide() method called.'); // Log that hide was called
    } else {
        console.warn('Payment modal object is not initialized. Cannot hide.'); // Log warning
    }
}

// NOTE: showPaymentSuccess function might not be present or exported in this older version.
// export function showPaymentSuccess(message, redirectUrl) { ... }


// Attach listeners for payment modal interactions
function attachPaymentModalListeners() {
    console.log('Attaching payment modal listeners...'); // Log start attaching

    // Ensure all necessary elements exist before attaching listeners
    // NOTE: Required elements list might be shorter in this older version
    const requiredModalElements = [
        btnCash, btnQris, btnBackToOptions, btnBackToOptionsQris,
        uangDiterimaInput, btnBayarCash, btnConfirmQris,
        paymentOptions, cashPaymentForm, qrisPaymentInfo, loadingIndicator,
        paymentSuccessMessage, paymentErrorMessage, paymentModalEl
        // paymentSuccessActions, btnBackToDashboard, btnViewSummary // Might not be in elements yet
    ];

    const allElementsFound = requiredModalElements.every(el => el !== null && typeof el !== 'undefined');

    if (!allElementsFound) {
        console.error('One or more essential payment modal elements not found. Payment modal listeners not fully attached.'); // Log error
        // Log which element is missing for easier debugging
        requiredModalElements.forEach((el, index) => {
            // Check by variable name if possible, or just index
            if (el === null || typeof el === 'undefined') {
                 // This might not show the variable name, check the console output carefully
                 console.error(`Missing element related to index ${index} in requiredModalElements array.`);
            }
        });
        return;
    }

    // Select Cash Payment
    btnCash.addEventListener('click', function() {
        console.log('Cash payment button clicked.');
        if (paymentOptions) paymentOptions.style.display = 'none';
        if (cashPaymentForm) cashPaymentForm.style.display = 'block';
        if (qrisPaymentInfo) qrisPaymentInfo.style.display = 'none'; // Ensure QRIS is hidden
        if (paymentSuccessMessage) paymentSuccessMessage.style.display = 'none';
        if (paymentErrorMessage) paymentErrorMessage.style.display = 'none';
        // if (paymentSuccessActions) paymentSuccessActions.style.display = 'none'; // Might not be in elements yet
        if (uangDiterimaInput) uangDiterimaInput.value = '';
        if (kembalianDisplay) kembalianDisplay.textContent = formatRupiah(0);
        if (kembalianDisplay) kembalianDisplay.classList.remove('text-danger');
        if (kembalianDisplay) kembalianDisplay.classList.add('text-success');
        if (btnBayarCash) $(btnBayarCash).prop('disabled', true).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
         if (loadingIndicator) loadingIndicator.style.display = 'none'; // Ensure modal loading is hidden
         if (btnBackToOptions) btnBackToOptions.style.display = 'block'; // Show cash back button
         if (btnBackToOptionsQris) btnBackToOptionsQris.style.display = 'none'; // Hide qris back button
         if (uangDiterimaInput) uangDiterimaInput.focus(); // Focus on cash input
        console.log('Switched to Cash Payment form.');
    });

    // Select QRIS Payment
    btnQris.addEventListener('click', function() {
        console.log('QRIS payment button clicked.');
        if (paymentOptions) paymentOptions.style.display = 'none';
        if (cashPaymentForm) cashPaymentForm.style.display = 'none'; // Ensure Cash is hidden
        if (qrisPaymentInfo) qrisPaymentInfo.style.display = 'block';
        if (paymentSuccessMessage) paymentSuccessMessage.style.display = 'none';
        if (paymentErrorMessage) paymentErrorMessage.style.display = 'none';
        // if (paymentSuccessActions) paymentSuccessActions.style.display = 'none'; // Might not be in elements yet
        if (btnConfirmQris) $(btnConfirmQris).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai'); // Reset button
         if (loadingIndicator) loadingIndicator.style.display = 'none'; // Ensure modal loading is hidden
         if (btnBackToOptions) btnBackToOptions.style.display = 'none'; // Hide cash back button
         if (btnBackToOptionsQris) btnBackToOptionsQris.style.display = 'block'; // Show qris back button
        console.log('Switched to QRIS Payment info.');
    });

    // Input change for Cash amount
    uangDiterimaInput.addEventListener('input', function() {
        // Get total bill from modal data attribute
        const totalBill = parseFloat(paymentModalEl.dataset.totalBill) || 0;
        let uangDiterima = parseFloat(this.value) || 0;
        let kembalian = uangDiterima - totalBill;
        console.log(`Uang Diterima input changed: ${uangDiterima}. Current Total Bill: ${totalBill}. Calculated change: ${kembalian}`);

        if (kembalianDisplay) kembalianDisplay.textContent = formatRupiah(kembalian);

        if (kembalianDisplay) {
            if (kembalian < 0) {
                kembalianDisplay.classList.remove('text-success');
                kembalianDisplay.classList.add('text-danger');
            } else {
                kembalianDisplay.classList.remove('text-danger');
                kembalianDisplay.classList.add('text-success');
            }
        }

        if (btnBayarCash) {
            if (uangDiterima >= totalBill && uangDiterima > 0) {
                $(btnBayarCash).prop('disabled', false);
            } else {
                $(btnBayarCash).prop('disabled', true);
            }
        }
    });

    // Back button from Cash form
    btnBackToOptions.addEventListener('click', function() {
        console.log('Back to Payment Options clicked from Cash form.');
        if (cashPaymentForm) cashPaymentForm.style.display = 'none';
        if (qrisPaymentInfo) qrisPaymentInfo.style.display = 'none'; // Ensure QRIS is also hidden
        if (paymentOptions) paymentOptions.style.display = 'block';
        if (paymentSuccessMessage) paymentSuccessMessage.style.display = 'none';
        if (paymentErrorMessage) paymentErrorMessage.style.display = 'none';
        // if (paymentSuccessActions) paymentSuccessActions.style.display = 'none'; // Might not be in elements yet
        if (uangDiterimaInput) uangDiterimaInput.value = '';
        if (kembalianDisplay) kembalianDisplay.textContent = formatRupiah(0);
         if (kembalianDisplay) kembalianDisplay.classList.remove('text-danger');
         if (kembalianDisplay) kembalianDisplay.classList.add('text-success');
        if (btnBayarCash) $(btnBayarCash).prop('disabled', true).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
         if (loadingIndicator) loadingIndicator.style.display = 'none'; // Ensure modal loading is hidden
         if (btnBackToOptions) btnBackToOptions.style.display = 'block'; // Show cash back button
         if (btnBackToOptionsQris) btnBackToOptionsQris.style.display = 'none'; // Hide qris back button
         if (uangDiterimaInput) uangDiterimaInput.focus(); // Focus on cash input
        console.log('Returned to Payment Options.');
    });

    // Back button from QRIS info
    btnBackToOptionsQris.addEventListener('click', function() {
        console.log('Back to Payment Options clicked from QRIS info.');
         if (cashPaymentForm) cashPaymentForm.style.display = 'none';
         if (qrisPaymentInfo) qrisPaymentInfo.style.display = 'none';
         if (paymentOptions) paymentOptions.style.display = 'block';
         if (paymentSuccessMessage) paymentSuccessMessage.style.display = 'none';
         if (paymentErrorMessage) paymentErrorMessage.style.display = 'none';
         // if (paymentSuccessActions) paymentSuccessActions.style.display = 'none'; // Might not be in elements yet
         if (btnConfirmQris) $(btnConfirmQris).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
         if (loadingIndicator) loadingIndicator.style.display = 'none'; // Ensure modal loading is hidden
         if (btnBackToOptions) btnBackToOptions.style.display = 'block'; // Show cash back button
         if (btnBackToOptionsQris) btnBackToOptionsQris.style.display = 'none'; // Hide qris back button
         console.log('Returned to Payment Options.');
    });


    // Pay Cash button click
    btnBayarCash.addEventListener('click', function() {
        console.log('Pay Cash button clicked.');
         const totalBill = parseFloat(paymentModalEl.dataset.totalBill) || 0; // Get total bill from modal data attribute
        let amountPaid = parseFloat(uangDiterimaInput ? uangDiterimaInput.value : 0);

        if (amountPaid < totalBill) {
            alert('Jumlah uang yang diterima kurang dari total tagihan.');
             console.warn('Payment cancelled: Amount paid less than total bill.'); // Debug log
            return;
        }

        const currentReservasiId = paymentModalEl.dataset.reservasiId; // Get reservasi ID from modal data attribute

        if (!currentReservasiId || isNaN(amountPaid)) {
            alert('Data pembayaran tidak lengkap atau tidak valid.');
             console.error('Payment cancelled: Missing Reservasi ID or invalid Amount Paid.'); // Debug log
            return;
        }

        // Call function to process payment (will make AJAX call)
        processPaymentAjax('cash', amountPaid);

    });

    // Confirm QRIS button click
    btnConfirmQris.addEventListener('click', function() {
        console.log('Confirm QRIS button clicked.');
        const currentReservasiId = paymentModalEl.dataset.reservasiId; // Get reservasi ID from modal data attribute

        if (!currentReservasiId) {
            alert('Data pesanan tidak lengkap untuk pembayaran.');
             console.error('QRIS Payment cancelled: Missing Reservasi ID.'); // Debug log
            return;
        }

        // Call function to process payment (will make AJAX call for QRIS/Snap Token)
        processPaymentAjax('qris'); // No amount_paid needed for QRIS initially

    });

    // NOTE: Listeners for btnBackToDashboard and btnViewSummary might not be present in this older version.


    console.log('Payment modal listeners attached.'); // Log end attaching
}

// Need to export the showPaymentModal function so form_submit.js can call it
// Also need to export hidePaymentModal and potentially other state/elements if needed by other modules