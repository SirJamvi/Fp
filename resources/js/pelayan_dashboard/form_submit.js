// resources/js/pelayan_dashboard/form_submit.js

import { getCartItems, clearCart } from './cart_manager';
import { showPaymentModal, hidePaymentModal, formatRupiah } from './payment_modal';
import { triggerMidtransSnap } from './midtrans_integration';

// Elemen DOM - Pastikan semua elemen yang dibutuhkan diinisialisasi di initFormSubmit
let orderForm, submitOrderBtn, mejaSelect, jumlahTamuInput, loadingIndicatorModal,
    paymentSuccessMessageModal, paymentErrorMessageModal, btnBayarCashModal,
    btnConfirmQrisModal, btnBackToOptionsModal, btnBackToOptionsQrisModal,
    uangDiterimaInputModal, processPaymentRouteInput, orderSummaryRouteInput,
    paymentModalEl; // Tambahkan referensi ke elemen modal itu sendiri

export function initFormSubmit(elements) {
    // Inisialisasi elemen DOM dari objek elements yang diteruskan dari main.js
    orderForm = elements.orderForm;
    submitOrderBtn = elements.submitOrderBtn;
    mejaSelect = elements.mejaSelect;
    jumlahTamuInput = elements.jumlahTamuInput;
    loadingIndicatorModal = elements.loadingIndicator;
    paymentSuccessMessageModal = elements.paymentSuccessMessage;
    paymentErrorMessageModal = elements.paymentErrorMessage;
    btnBayarCashModal = elements.btnBayarCash;
    btnConfirmQrisModal = elements.btnConfirmQris;
    btnBackToOptionsModal = elements.btnBackToOptions;
    btnBackToOptionsQrisModal = elements.btnBackToOptionsQris;
    uangDiterimaInputModal = elements.uangDiterimaInput;
    processPaymentRouteInput = elements.processPaymentRouteInput;
    orderSummaryRouteInput = elements.orderSummaryRouteInput; // Ambil elemen route summary
    paymentModalEl = elements.paymentModalEl; // Ambil elemen modal

    attachFormSubmitListeners();
}

// Fungsi untuk memeriksa status tombol submit (diimpor dan digunakan oleh cart_manager dan table_info)
export function checkSubmitButtonStatus() {
    // console.log('Executing checkSubmitButtonStatus...'); // Debug log
    if (!submitOrderBtn || !mejaSelect) {
        console.warn('Submit button or meja select element not found. Cannot check submit button status.');
        return;
    }
   const cart = getCartItems();
   const isCartEmpty = Object.keys(cart).length === 0;
   const isTableSelected = mejaSelect.value !== "";

   // console.log(`Submit button status check: Cart Empty - ${isCartEmpty}, Table Selected - ${isTableSelected}.`); // Debug log
   if (!isCartEmpty && isTableSelected) {
       submitOrderBtn.disabled = false;
       // console.log('Submit button enabled.'); // Debug log
   } else {
       submitOrderBtn.disabled = true;
       // console.log('Submit button disabled.'); // Debug log
   }
   // console.log(`Submit button final disabled state: ${submitOrderBtn.disabled}`); // Debug log
}


// Fungsi untuk melampirkan event listeners pada form dan elemen terkait
function attachFormSubmitListeners() {
   if (orderForm && submitOrderBtn) {
       orderForm.addEventListener('submit', function(e) {
           e.preventDefault();
           console.log('Order form submitted.');

           const cart = getCartItems();

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
            // Debug log untuk melihat isi formData
            // console.log('FormData contents:');
            // for (let pair of formData.entries()) {
            //     console.log(pair[0]+ ': ' + pair[1]);
            // }

            // Nonaktifkan tombol submit dan tampilkan spinner saat proses
            $(submitOrderBtn).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
           console.log('Sending order data via AJAX to storeOrder...');

           // Kirim data pesanan ke backend menggunakan AJAX
           $.ajax({
               url: orderForm.action,
               method: orderForm.method,
               data: formData,
               processData: false, // Penting untuk FormData
               contentType: false, // Penting untuk FormData
               headers: { // Tambahkan CSRF token untuk keamanan
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function(response) {
                   console.log('Order store AJAX success:', response);
                    // Aktifkan kembali tombol submit setelah respons diterima
                    $(submitOrderBtn).prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Proses Pesanan');

                   if (response.success) {
                       console.log('Order saved successfully. Showing payment modal.');
                       // Ambil elemen modal pembayaran
                       const paymentModalEl = document.getElementById('paymentModal');
                       if (paymentModalEl) {
                            // Simpan ID reservasi, total tagihan, dan kode order pada elemen modal
                            paymentModalEl.dataset.reservasiId = response.reservasi_id;
                            // PENTING: Gunakan total_bill dari respons server untuk modal
                            paymentModalEl.dataset.totalBill = parseFloat(response.total_bill);
                            paymentModalEl.dataset.kodeOrder = response.kode_reservasi;
                            console.log('Stored order data on modal element:', paymentModalEl.dataset);

                            // Tampilkan modal pembayaran dengan data dari respons server
                            showPaymentModal(response.reservasi_id, response.total_bill, response.kode_reservasi);
                       } else {
                            console.warn('Payment modal element not found, cannot store order data or show modal.');
                            alert('Terjadi kesalahan internal: Elemen modal pembayaran tidak ditemukan.');
                       }
                   } else {
                       console.warn('Server reported order store failed:', response.message);
                       alert('Gagal menyimpan pesanan: ' + response.message);
                       if (response.errors) {
                           console.error('Validation errors:', response.errors);
                           // Anda bisa menambahkan logika untuk menampilkan error validasi di UI di sini
                       }
                   }
               },
               error: function(xhr, status, error) {
                   console.error('AJAX Store Order Error:', status, error, xhr.responseText);
                   // Aktifkan kembali tombol submit jika terjadi error
                   if (submitOrderBtn) $(submitOrderBtn).prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Proses Pesanan');

                   let errorMessage = 'Terjadi kesalahan saat menyimpan pesanan.';
                   if (xhr.responseJSON && xhr.responseJSON.message) {
                       errorMessage = xhr.responseJSON.message;
                   } else if (xhr.status === 422) {
                       errorMessage = 'Validasi gagal. Silakan periksa input Anda.';
                       console.log('Validation errors response:', xhr.responseJSON.errors);
                       // Anda bisa menambahkan logika untuk menampilkan error validasi di UI di sini
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

   // Lampirkan listener untuk perubahan pada dropdown meja dan input jumlah tamu
   // agar tombol submit diperbarui statusnya
   if (mejaSelect) {
       mejaSelect.addEventListener('change', checkSubmitButtonStatus);
       console.log('Meja select change listener attached.');
   } else {
        console.warn('mejaSelect not found. Change listener not attached.');
   }
    if (jumlahTamuInput) {
        jumlahTamuInput.addEventListener('input', checkSubmitButtonStatus);
        console.log('Jumlah tamu input listener attached.');
    } else {
        console.warn('jumlahTamuInput not found. Input listener not attached.');
    }
}

// Fungsi untuk memproses pembayaran via AJAX
export function processPaymentAjax(paymentMethod, amountPaid = null) {
    console.log(`Processing payment via AJAX. Method: ${paymentMethod}, Amount: ${amountPaid}`);
    // Ambil ID reservasi dari data attribute modal
    const currentReservasiId = paymentModalEl ? paymentModalEl.dataset.reservasiId : null;
    // Ambil URL template route pembayaran dari input hidden
    const processPaymentRouteTemplate = processPaymentRouteInput ? processPaymentRouteInput.value : null;

    if (!currentReservasiId || !processPaymentRouteTemplate) {
        console.error('Cannot process payment: Reservation ID or Route URL template is missing.');
        alert('Internal error: Reservation ID or Route URL not found.');
        if (paymentModalEl) hidePaymentModal(); // Sembunyikan modal jika ada error fatal
        return;
    }

    // Bentuk URL pembayaran final dengan mengganti placeholder :reservasiId
    const finalPaymentUrl = processPaymentRouteTemplate.replace(':reservasiId', currentReservasiId);

    // Tampilkan indikator loading dan nonaktifkan tombol
    if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'block';
    if (btnBayarCashModal) $(btnBayarCashModal).prop('disabled', true);
    if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
    if (btnBackToOptionsModal) btnBackToOptionsModal.disabled = true;
    if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = true;
    if (uangDiterimaInputModal && paymentMethod === 'cash') uangDiterimaInputModal.disabled = true;

    // Data yang akan dikirim ke backend
    const postData = {
        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
        payment_method: paymentMethod, // Metode pembayaran (cash/qris)
    };
    // Tambahkan jumlah uang yang diterima jika metode pembayaran adalah tunai
    if (paymentMethod === 'cash') {
        postData.amount_paid = amountPaid;
    }

    console.log(`Sending payment data for Reservasi ID ${currentReservasiId}:`, postData);

    // Kirim permintaan pembayaran ke backend via AJAX
    $.ajax({
        url: finalPaymentUrl,
        method: 'POST',
        data: postData,
        success: function(response) {
            console.log('Payment AJAX success:', response);
            // Sembunyikan indikator loading
            if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'none';

            if (response.success) {
                // Jika pembayaran QRIS dan ada snap_token, picu popup Midtrans Snap
                if (paymentMethod === 'qris' && response.snap_token) {
                    console.log('Received Snap Token for QRIS. Attempting to trigger Midtrans Snap.');
                    const snapCalledSuccessfully = triggerMidtransSnap(response.snap_token);

                    if (snapCalledSuccessfully) {
                        // Jika Snap berhasil dipicu, Snap callbacks akan menangani UI selanjutnya.
                        // Jangan lanjutkan dengan pesan sukses/redirect generik di sini.
                        console.log('Midtrans Snap.pay() was invoked. Waiting for Midtrans callbacks.');
                        return; // Keluar dari fungsi success AJAX
                    } else {
                        // Jika triggerMidtransSnap mengembalikan false (misal Snap JS belum dimuat)
                        console.warn('Midtrans Snap UI could not be displayed.');
                        // Aktifkan kembali tombol QRIS dan tombol kembali
                        if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
                        if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = false;
                        // Jangan lanjutkan ke pesan sukses generik atau clear cart
                        return;
                    }
                }

                // Blok ini dijalankan untuk pembayaran CASH atau jika QRIS berhasil tanpa Snap (kasus tidak biasa untuk Snap)
                console.log('Payment successful (Cash or non-Snap QRIS). Processing post-payment actions.');
                if (paymentSuccessMessageModal) {
                    // Tampilkan pesan sukses pembayaran
                    const changeAmount = (response.change !== undefined && response.change !== null) ? parseFloat(response.change) : 0;
                    const changeMessage = paymentMethod === 'cash' && changeAmount >= 0 ?
                        ' Kembalian: ' + formatRupiah(changeAmount) :
                        '';
                    paymentSuccessMessageModal.textContent = response.message + changeMessage;
                    paymentSuccessMessageModal.style.display = 'block';
                }

                // Kosongkan keranjang setelah pembayaran berhasil
                clearCart();

                // Tampilkan tombol aksi setelah sukses (Kembali ke Dashboard / Lihat Ringkasan)
                const paymentSuccessActionsDiv = document.getElementById('paymentSuccessActions');
                const btnBackToDashboard = document.getElementById('btnBackToDashboard');
                const btnViewSummary = document.getElementById('btnViewSummary');
                const orderSummaryRouteTemplate = orderSummaryRouteInput ? orderSummaryRouteInput.value : null;

                if (paymentSuccessActionsDiv) paymentSuccessActionsDiv.style.display = 'block';

                // Atur listener untuk tombol "Kembali ke Dashboard"
                if (btnBackToDashboard) {
                    btnBackToDashboard.onclick = function() {
                        window.location.href = '/pelayan/dashboard'; // Redirect ke dashboard
                    };
                }

                // Atur listener untuk tombol "Lihat Ringkasan Pesanan"
                if (btnViewSummary && currentReservasiId && orderSummaryRouteTemplate) {
                    const finalSummaryUrl = orderSummaryRouteTemplate.replace(':reservasiId', currentReservasiId);
                    btnViewSummary.onclick = function() {
                        window.location.href = finalSummaryUrl; // Redirect ke halaman ringkasan
                    };
                } else {
                    // Sembunyikan tombol "Lihat Ringkasan" jika URL tidak tersedia
                    if (btnViewSummary) btnViewSummary.style.display = 'none';
                }

                // Sembunyikan form pembayaran dan pilihan metode
                document.getElementById('paymentOptions').style.display = 'none';
                document.getElementById('cashPaymentForm').style.display = 'none';
                document.getElementById('qrisPaymentInfo').style.display = 'none';


            } else { // response.success is false (pembayaran gagal di server)
                console.warn('Payment failed on server:', response.message);
                if (paymentErrorMessageModal) {
                    paymentErrorMessageModal.textContent = response.message;
                    paymentErrorMessageModal.style.display = 'block';
                }
                // Aktifkan kembali tombol yang relevan agar user bisa mencoba lagi atau kembali
                if (paymentMethod === 'cash') {
                    if (btnBayarCashModal) $(btnBayarCashModal).prop('disabled', false).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
                    if (uangDiterimaInputModal) uangDiterimaInputModal.disabled = false;
                    if (btnBackToOptionsModal) btnBackToOptionsModal.disabled = false;
                } else if (paymentMethod === 'qris') {
                    if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
                    if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = false;
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Payment Error:', status, error, xhr.responseText);
            // Sembunyikan indikator loading
            if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'none';

            // Aktifkan kembali semua tombol yang relevan jika terjadi error generik
            if (btnBayarCashModal) $(btnBayarCashModal).prop('disabled', false).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
            if (btnConfirmQrisModal) $(btnConfirmQrisModal).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
            if (btnBackToOptionsModal) btnBackToOptionsModal.disabled = false;
            if (btnBackToOptionsQrisModal) btnBackToOptionsQrisModal.disabled = false;
            if (uangDiterimaInputModal) uangDiterimaInputModal.disabled = false;

            let errorMessageText = 'Terjadi kesalahan saat memproses pembayaran.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessageText = xhr.responseJSON.message;
            } else {
                errorMessageText += ' (' + status + ': ' + error + ')';
            }
            if (paymentErrorMessageModal) {
                paymentErrorMessageModal.textContent = 'Error: ' + errorMessageText;
                paymentErrorMessageModal.style.display = 'block';
            }
        }
    });
}

// Ekspor fungsi lain yang mungkin dibutuhkan oleh modul lain, seperti checkSubmitButtonStatus
// (Sudah diekspor di bagian atas)
