// resources/js/pelayan_dashboard/midtrans_integration.js

// Import hidePaymentModal from payment_modal.js
import { hidePaymentModal } from './payment_modal';

// Ensure Snap JS is loaded before attempting to use it
// The script tag is in dashboard.blade.php

// Function to trigger Midtrans Snap payment modal
export function triggerMidtransSnap(snapToken) {
    console.log('Attempting to trigger Midtrans Snap with token:', snapToken); // Added Log

    // Check if Snap JS is loaded and Snap object is available
    if (typeof Snap === 'undefined') {
        console.error('Midtrans Snap JS is not loaded. Cannot trigger Snap.'); // Added Log
        alert('Gagal memuat Midtrans. Silakan coba lagi.');
        // Consider re-enabling payment buttons here if Snap JS is missing
        return;
    }

    if (!snapToken) {
        console.error('Snap Token is missing. Cannot trigger Midtrans Snap.'); // Added Log
        alert('Gagal mendapatkan token pembayaran dari server.');
        // Consider re-enabling payment buttons here if token is missing
        return;
    }

    // Trigger the Snap payment modal
    try {
        Snap.pay(snapToken, {
            onSuccess: function(result) {
                /* You may add your own implementation here */
                console.log('Midtrans Snap Success:', result); // Added Log
                alert("Pembayaran Berhasil!");
                // Handle successful payment (e.g., update UI, redirect)
                // The backend should also handle the Midtrans notification callback
                // You might redirect to a success page or update the current page status
                // For now, let's just hide the modal and maybe show a message
                hidePaymentModal();
                // Redirect to summary page or refresh dashboard if needed
                // window.location.href = '/pelayan/order/summary/' + result.order_id; // Example redirect
                 // Or, if you want to show success message in the modal itself:
                 // showPaymentSuccess('Pembayaran Non-Tunai Berhasil!', '/pelayan/order/summary/' + result.order_id); // Requires showPaymentSuccess to be exported and imported
            },
            onPending: function(result) {
                /* You may add your own implementation here */
                console.log('Midtrans Snap Pending:', result); // Added Log
                alert("Menunggu pembayaran Anda.");
                // Handle pending payment (e.g., update UI to pending state)
                hidePaymentModal(); // Hide modal, user completes payment outside
            },
            onError: function(result) {
                /* You may add your own implementation here */
                console.log('Midtrans Snap Error:', result); // Added Log
                alert("Pembayaran Gagal!");
                // Handle error (e.g., show error message, allow retry)
                hidePaymentModal(); // Hide modal
                // Show error message in the main dashboard or modal if it was still open
            },
            onClose: function() {
                /* You may add your own implementation here */
                console.log('Midtrans Snap closed without finishing.'); // Added Log
                // Handle modal close (e.g., user closed the popup)
                // You might want to re-enable payment buttons
            }
        });
        console.log('Midtrans Snap.pay() called.'); // Added Log
    } catch (e) {
        console.error('Error calling Snap.pay():', e); // Added Log
        alert('Terjadi kesalahan saat memulai pembayaran Midtrans.');
        // Consider re-enabling payment buttons here
    }
}

// Note: The Midtrans Notification URL (webhook) must be configured in your Midtrans Dashboard
// This function only handles the frontend Snap popup interaction.
// The actual payment status update should primarily rely on the backend Notification URL.