// resources/js/pelayan_dashboard/form_submit.js

// Import modules needed for form submission and payment handling
import { getCartItems, clearCart } from './cart_manager'; // Need cart data and clear function
// Import showPaymentModal, hidePaymentModal, and formatRupiah from payment_modal.js
// NOTE: In this older version, showPaymentSuccess might not be imported/used here yet.
import { showPaymentModal, hidePaymentModal, formatRupiah } from './payment_modal'; // <-- Imported formatRupiah
import { updateSelectedTableInfo } from './table_info'; // Need to update table info on change
// Import triggerMidtransSnap from midtrans_integration.js
import { triggerMidtransSnap } from './midtrans_integration';


// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let orderForm;
let submitOrderBtn;
let mejaSelect;
let jumlahTamuInput;
let loadingIndicatorModal; // Loading indicator inside the modal
let paymentSuccessMessageModal; // Success message inside modal
let paymentErrorMessageModal; // Error message inside modal
let btnBayarCashModal; // Cash button in modal
let btnConfirmQrisModal; // QRIS button in modal
let btnBackToOptionsModal; // Back button in modal
let btnBackToOptionsQrisModal; // Back button in modal
let uangDiterimaInputModal; // Cash input in modal
let processPaymentRouteInput; // Reference to the hidden input for the route
// NOTE: In this older version, orderSummaryRouteInput might not be used here yet


// Export fungsi inisialisasi untuk menerima elemen DOM
export function initFormSubmit(elements) {
    console.log('Initializing Form Submit module...'); // Added Log
    orderForm = elements.orderForm;
    submitOrderBtn = elements.submitOrderBtn;
    mejaSelect = elements.mejaSelect;
    jumlahTamuInput = elements.jumlahTamuInput;
    loadingIndicatorModal = elements.loadingIndicator; // Loading indicator inside the modal
    paymentSuccessMessageModal = elements.paymentSuccessMessage;
    paymentErrorMessageModal = elements.paymentErrorMessage;
    btnBayarCashModal = elements.btnBayarCash;
    btnConfirmQrisModal = elements.btnConfirmQris;
    btnBackToOptionsModal = elements.btnBackToOptions;
    btnBackToOptionsQrisModal = elements.btnBackToOptionsQris;
    uangDiterimaInputModal = elements.uangDiterimaInput;
    processPaymentRouteInput = elements.processPaymentRouteInput; // Get the route element
    // orderSummaryRouteInput = elements.orderSummaryRouteInput; // Might not be in elements yet


    console.log('Form Submit initialized. Attaching listeners...'); // Added Log
    attachFormSubmitListeners(); // Attach listeners after getting elements
    // checkSubmitButtonStatus is called from cart_manager and table_info after updates
}

// Fungsi untuk mengecek status tombol submit
// Tombol submit aktif jika keranjang tidak kosong DAN meja sudah dipilih
export function checkSubmitButtonStatus() {
     console.log('Executing checkSubmitButtonStatus...');
     if (!submitOrderBtn || !mejaSelect) {
         console.warn('Submit button or meja select element not found. Cannot check submit button status.');
         return;
     }
    const cart = getCartItems(); // Get current cart state
    const isCartEmpty = Object.keys(cart).length === 0;
    const isTableSelected = mejaSelect.value !== "";

    console.log(`Submit button status check: Cart Empty - ${isCartEmpty}, Table Selected - ${isTableSelected}.`);

    if (!isCartEmpty && isTableSelected) {
        submitOrderBtn.disabled = false;
        console.log('Submit button enabled.');
    } else {
        submitOrderBtn.disabled = true;
         console.log('Submit button disabled.');
    }
    console.log(`Submit button final disabled state: ${submitOrderBtn.disabled}`);
}


// Form Submission (AJAX to storeOrder)
function attachFormSubmitListeners() {
    if (orderForm && submitOrderBtn) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Order form submitted.');

            const cart = getCartItems(); // Get current cart state

            if (Object.keys(cart).length === 0) {
                alert('Keranjang pesanan kosong. Silakan pilih menu terlebih dahulu.');
                console.warn('Order submission cancelled: Cart is empty.');
                return false;
            }
            if (!mejaSelect || mejaSelect.value === "") {
                alert('Silakan pilih meja terlebih dahulu.');
                 if(mejaSelect) mejaSelect.focus();
                 console.warn('Order submission cancelled: No table selected.');
                return false;
            }
            if (!jumlahTamuInput || !jumlahTamuInput.value || parseInt(jumlahTamuInput.value, 10) < 1) {
                alert('Jumlah tamu harus diisi dan minimal 1.');
                if(jumlahTamuInput) jumlahTamuInput.focus();
                 console.warn('Order submission cancelled: Invalid number of guests.');
                return false;
            }

            const formData = new FormData(orderForm);
             console.log('Collecting form data.');
             console.log('FormData contents:');
             for (let pair of formData.entries()) {
                 console.log(pair[0]+ ': ' + pair[1]);
             }


            // Show loading indicator on the main submit button
             $(submitOrderBtn).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');


            console.log('Sending order data via AJAX to storeOrder...');

            $.ajax({
                url: orderForm.action,
                method: orderForm.method,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Order store AJAX success:', response);
                    // Hide main submit button loading indicator
                     $(submitOrderBtn).prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Proses Pesanan');


                    if (response.success) {
                        console.log('Order saved successfully. Showing payment modal.');
                        // Show the payment modal with order details
                        // Store reservasi ID on the modal element for later use
                        const paymentModalEl = document.getElementById('paymentModal');
                        if (paymentModalEl) {
                             paymentModalEl.dataset.reservasiId = response.reservasi_id;
                             paymentModalEl.dataset.totalBill = parseFloat(response.total_bill); // Store total bill too
                             paymentModalEl.dataset.kodeOrder = response.kode_reservasi; // Store kode order too
                             console.log('Stored order data on modal element:', paymentModalEl.dataset);
                        } else {
                             console.warn('Payment modal element not found, cannot store order data.');
                        }

                        showPaymentModal(response.reservasi_id, response.total_bill, response.kode_reservasi);

                         // Optional: Clear cart after successful order creation (before payment)
                         // clearCart(); // Decide if cart should clear here or after successful payment

                    } else {
                        console.warn('Server reported order store failed:', response.message);
                        alert('Gagal menyimpan pesanan: ' + response.message);
                        if (response.errors) {
                            console.error('Validation errors:', response.errors);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Store Order Error:', status, error, xhr.responseText);
                     if (submitOrderBtn) $(submitOrderBtn).prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Proses Pesanan');


                    let errorMessage = 'Terjadi kesalahan saat menyimpan pesanan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        errorMessage = 'Validasi gagal. Silakan periksa input Anda.';
                        console.log('Validation errors response:', xhr.responseJSON.errors);
                    } else {
                        errorMessage += ' (' + status + ': ' + error + ')';
                    }
                    alert('Error: ' + errorMessage);
                }
            });
        });
    } else {
         if (!orderForm) console.warn('Order form element not found. Submit listener not attached.');
         if (!submitOrderBtn) console.warn('Submit order button not found. Submit listener not attached.');
    }

    // Attach change listener to mejaSelect to update submit button status
    if (mejaSelect) {
        mejaSelect.addEventListener('change', checkSubmitButtonStatus);
        console.log('Meja select change listener attached in form_submit.js');
    } else {
         console.warn('mejaSelect not found in form_submit.js. Change listener not attached.');
    }
     // Attach input listener to jumlahTamuInput if needed for validation logic
     if (jumlahTamuInput) {
         jumlahTamuInput.addEventListener('input', checkSubmitButtonStatus); // Or more specific validation check
         console.log('Jumlah tamu input listener attached in form_submit.js');
     } else {
         console.warn('jumlahTamuInput not found in form_submit.js. Input listener not attached.');
     }
}

// Function to handle the actual payment processing AJAX call
// Called from payment_modal.js when Pay Cash or Confirm QRIS is clicked
export function processPaymentAjax(paymentMethod, amountPaid = null) {
    console.log(`Processing payment via AJAX. Method: ${paymentMethod}, Amount: ${amountPaid}`);

    // Get current reservation ID from the data attribute on the modal element
    const paymentModalEl = document.getElementById('paymentModal');
    const currentReservasiId = paymentModalEl ? paymentModalEl.dataset.reservasiId : null;
    // Get the route URL from the hidden input
    const processPaymentRouteTemplate = processPaymentRouteInput ? processPaymentRouteInput.value : null;
    // NOTE: In this older version, orderSummaryRouteInput might not be used here yet


    if (!currentReservasiId || !processPaymentRouteTemplate) {
        console.error('Cannot process payment: Reservation ID or Route URL template is missing.');
        alert('Internal error: Reservation ID or Route URL not found.');
        hidePaymentModal(); // Hide modal on error
        return;
    }

    // Construct the final payment URL by replacing the placeholder
    const finalPaymentUrl = processPaymentRouteTemplate.replace(':reservasiId', currentReservasiId);
    console.log('Constructed payment AJAX URL:', finalPaymentUrl);

    // NOTE: In this older version, there might not be a separate summary URL/redirect logic here
    // The success handling might just redirect to a default page or refresh.


    // Show loading indicator in modal
    if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'block';
    // Disable buttons in modal
    // Ensure these elements exist before disabling
    if (btnBayarCashModal) $(btnBayarCashModal).prop('disabled', true);
    if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...'); // Specific text for QRIS confirm
    if (btnBackToOptionsModal) btnBackToOptionsModal.disabled = true;
    if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = true; // Disable both back buttons
    if (uangDiterimaInputModal && paymentMethod === 'cash') uangDiterimaInputModal.disabled = true; // Disable cash input if cash method


    const postData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        payment_method: paymentMethod,
    };

    if (paymentMethod === 'cash') {
        postData.amount_paid = amountPaid;
    }
    // For QRIS, amount_paid might be sent later via Midtrans callback,
    // but the initial call might just confirm the method.
    // The backend processPayment method handles the logic based on method.

    console.log(`Sending payment data for Reservasi ID ${currentReservasiId}:`, postData);


    $.ajax({
        url: finalPaymentUrl, // Use the constructed Payment URL
        method: 'POST',
        data: postData,
        success: function(response) {
            console.log('Payment AJAX success:', response);
            // Hide loading indicator
             if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'none';


            if (response.success) {
                console.log('Payment successful.');
                 // Display success message in modal briefly before redirect
                 if (paymentSuccessMessageModal) {
                     // Check if response.change exists before adding to message
                     // NOTE: formatRupiah might not be correctly imported/used here yet in this version
                     const changeMessage = response.change !== undefined && response.change !== null
                                           ? ' Kembalian: ' + response.change // Using raw change value
                                           : '';
                     paymentSuccessMessageModal.textContent = response.message + changeMessage;
                     paymentSuccessMessageModal.style.display = 'block';
                 }


                // Clear cart after successful payment
                clearCart();

                // *** Midtrans Snap Trigger for QRIS ***
                // The backend should return 'snap_token' for QRIS payment
                if (paymentMethod === 'qris' && response.snap_token) {
                    console.log('Received Snap Token for QRIS. Triggering Midtrans Snap.');
                    // Check if Snap JS is loaded
                    if (typeof Snap === 'undefined') {
                        console.error('Midtrans Snap JS is not loaded. Cannot trigger Snap.');
                        alert('Gagal memuat Midtrans. Silakan coba lagi.');
                        // Re-enable buttons if Snap JS is missing
                         if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
                         if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = false;
                        return; // Stop here if Snap is not available
                    }

                    // Call Snap.pay() with the token
                    triggerMidtransSnap(response.snap_token);
                    // The rest of the success handling (hiding modal, redirect) will happen
                    // inside the Midtrans Snap callbacks (onSuccess, onPending, onError, onClose)
                    // So, we RETURN here and let Midtrans Snap handle the flow.
                    return; // Stop further execution in this success handler
                }


                // For Cash payment, or if QRIS doesn't involve Snap modal (less common)
                // This version likely just redirects directly after success
                console.log('Payment successful (Cash or QRIS without Snap modal). Redirecting...');
                setTimeout(function() {
                    // NOTE: This redirect URL might be hardcoded or derived differently in this version
                    // It might not use the orderSummaryRouteInput
                    const redirectUrl = response.redirect_url || '{{ route("pelayan.dashboard") }}'; // Fallback to dashboard
                    console.log('Redirecting to:', redirectUrl);
                    hidePaymentModal(); // Hide the modal before redirect
                    window.location.href = redirectUrl; // Redirect
                }, 1500); // Delay redirect slightly

            } else {
                console.warn('Payment failed:', response.message);
                // Display error message in modal
                 if (paymentErrorMessageModal) {
                     paymentErrorMessageModal.textContent = response.message;
                     paymentErrorMessageModal.style.display = 'block';
                 }

                // Re-enable relevant buttons based on payment method if not redirecting
                // Ensure these elements exist before re-enabling
                if (paymentMethod === 'cash') {
                    // Re-enable Pay Cash button
                     if (btnBayarCashModal) $(btnBayarCashModal).prop('disabled', false).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
                     if (uangDiterimaInputModal) uangDiterimaInputModal.disabled = false;
                     if (btnBackToOptionsModal) btnBackToOptionsModal.disabled = false;

                } else if (paymentMethod === 'qris') {
                    // Re-enable Confirm QRIS button
                     if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
                     if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = false;
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Payment Error:', status, error, xhr.responseText);
             if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'none';

            // Re-enable buttons in modal on error
            // Ensure these elements exist before re-enabling
             if (btnBayarCashModal) $(btnBayarCashModal).prop('disabled', false).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
             if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
             if (btnBackToOptionsModal) btnBackToOptionsModal.disabled = false;
             if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = false;
             if (uangDiterimaInputModal) uangDiterimaInputModal.disabled = false;


            let errorMessage = 'Terjadi kesalahan saat memproses pembayaran.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else {
                errorMessage += ' (' + status + ': ' + error + ')';
            }
             if (paymentErrorMessageModal) {
                 paymentErrorMessageModal.textContent = 'Error: ' + errorMessage;
                 paymentErrorMessageModal.style.display = 'block';
             }
        }
    });
}