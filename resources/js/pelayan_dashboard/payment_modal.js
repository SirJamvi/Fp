// resources/js/pelayan_dashboard/payment_modal.js

import { processPaymentAjax } from './form_submit'; // Import fungsi proses pembayaran
// import { clearCart } from './cart_manager'; // clearCart dipanggil di form_submit setelah sukses pembayaran

// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let paymentModalEl;
let paymentOptionsDiv;
let cashPaymentFormDiv;
let qrisPaymentInfoDiv;
let uangDiterimaInput;
let kembalianDisplay;
let btnBayarCash;
let btnConfirmQris;
let btnBackToOptions;
let btnBackToOptionsQris;
let loadingIndicatorModal;
let modalTotalBillSpan;
let modalKodeOrderStrong;
let paymentSuccessMessageModal;
let paymentErrorMessageModal;
let btnCash; // Tombol pilih metode Cash
let btnQris; // Tombol pilih metode QRIS
let paymentSuccessActionsDiv; // Container untuk tombol aksi setelah sukses
let btnBackToDashboard; // Tombol kembali ke dashboard
let btnViewSummary; // Tombol lihat ringkasan

// Export fungsi inisialisasi untuk menerima elemen DOM
export function initPaymentModal(elements) {
    console.log('Initializing Payment Modal module...');
    paymentModalEl = elements.paymentModalEl;
    paymentOptionsDiv = elements.paymentOptions;
    cashPaymentFormDiv = elements.cashPaymentForm;
    qrisPaymentInfoDiv = elements.qrisPaymentInfo;
    uangDiterimaInput = elements.uangDiterimaInput;
    kembalianDisplay = elements.kembalianDisplay;
    btnBayarCash = elements.btnBayarCash;
    btnConfirmQris = elements.btnConfirmQris;
    btnBackToOptions = elements.btnBackToOptions;
    btnBackToOptionsQris = elements.btnBackToOptionsQris;
    loadingIndicatorModal = elements.loadingIndicator;
    modalTotalBillSpan = elements.modalTotalBillSpan;
    modalKodeOrderStrong = elements.modalKodeOrderStrong;
    paymentSuccessMessageModal = elements.paymentSuccessMessage;
    paymentErrorMessageModal = elements.paymentErrorMessage;
    btnCash = elements.btnCash;
    btnQris = elements.btnQris;
    paymentSuccessActionsDiv = elements.paymentSuccessActions; // Ambil elemen baru
    btnBackToDashboard = elements.btnBackToDashboard; // Ambil elemen baru
    btnViewSummary = elements.btnViewSummary; // Ambil elemen baru


    attachModalEventListeners();
    console.log('Payment Modal initialized.');
}


// Fungsi untuk menampilkan modal pembayaran
// Menerima totalBill yang sudah dihitung oleh server
export function showPaymentModal(reservasiId, totalBill, kodeOrder) {
    console.log(`Showing payment modal for Reservasi ID: ${reservasiId}, Total Bill: ${totalBill}, Kode Order: ${kodeOrder}`);
    if (!paymentModalEl) {
        console.error('Payment modal element not found. Cannot show modal.');
        return;
    }

    // Reset tampilan modal ke kondisi awal
    resetPaymentModal();

    // Simpan data reservasi di elemen modal (untuk diakses nanti)
    paymentModalEl.dataset.reservasiId = reservasiId;
    paymentModalEl.dataset.totalBill = totalBill; // Simpan totalBill dari server
    paymentModalEl.dataset.kodeOrder = kodeOrder;

    // Perbarui tampilan total tagihan dan kode order di modal
    if (modalTotalBillSpan) {
        modalTotalBillSpan.textContent = formatRupiah(totalBill); // Tampilkan total dari server
        console.log(`Modal Total Bill updated to: ${formatRupiah(totalBill)}`);
    } else {
         console.warn('Modal total bill span not found.');
    }
    if (modalKodeOrderStrong) {
        modalKodeOrderStrong.textContent = kodeOrder;
        console.log(`Modal Kode Order updated to: ${kodeOrder}`);
    } else {
         console.warn('Modal kode order strong element not found.');
    }


    // Tampilkan modal menggunakan Bootstrap 5 JS
    const paymentModal = new bootstrap.Modal(paymentModalEl);
    paymentModal.show();
    console.log('Bootstrap payment modal show() called.');
}

// Fungsi untuk menyembunyikan modal pembayaran
export function hidePaymentModal() {
    console.log('Hiding payment modal.');
    if (paymentModalEl) {
        const paymentModal = bootstrap.Modal.getInstance(paymentModalEl);
        if (paymentModal) {
            paymentModal.hide();
            console.log('Bootstrap payment modal hide() called.');
        } else {
            console.warn('Bootstrap modal instance not found for payment modal.');
        }
    } else {
        console.warn('Payment modal element not found. Cannot hide modal.');
    }
}

// Fungsi untuk mereset tampilan modal ke kondisi awal (pilihan metode pembayaran)
function resetPaymentModal() {
    console.log('Resetting payment modal display.');
    if (paymentOptionsDiv) paymentOptionsDiv.style.display = 'block';
    if (cashPaymentFormDiv) cashPaymentFormDiv.style.display = 'none';
    if (qrisPaymentInfoDiv) qrisPaymentInfoDiv.style.display = 'none';
    if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'none';
    if (paymentSuccessMessageModal) paymentSuccessMessageModal.style.display = 'none';
    if (paymentErrorMessageModal) paymentErrorMessageModal.style.display = 'none';
    if (paymentSuccessActionsDiv) paymentSuccessActionsDiv.style.display = 'none'; // Sembunyikan tombol aksi sukses

    // Reset input uang diterima dan kembalian
    if (uangDiterimaInput) uangDiterimaInput.value = '';
    if (kembalianDisplay) kembalianDisplay.textContent = formatRupiah(0);

    // Pastikan tombol kembali aktif
    if (btnBackToOptions) btnBackToOptions.disabled = false;
    if (btnBackToOptionsQris) btnBackToOptionsQris.disabled = false;

     // Pastikan tombol Bayar Tunai dan Konfirmasi QRIS aktif (kecuali jika belum ada uang diterima)
    if (btnBayarCash) $(btnBayarCash).prop('disabled', true).html('<i class="bi bi-cash me-2"></i> Bayar Tunai'); // Default disabled sampai uang diterima diisi
    if (btnConfirmQris) $(btnConfirmQris).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai'); // Default aktif
}

// Fungsi untuk melampirkan event listeners pada elemen modal
function attachModalEventListeners() {
    console.log('Attaching payment modal event listeners...');
    if (!paymentModalEl) {
        console.warn('Payment modal element not found. Modal event listeners not attached.');
        return;
    }

    // Listener saat modal ditutup (opsional, bisa digunakan untuk reset state atau lainnya)
    // paymentModalEl.addEventListener('hidden.bs.modal', function (event) {
    //     console.log('Payment modal was hidden.');
    //     // Lakukan sesuatu setelah modal ditutup jika perlu
    // });

    // Listener untuk tombol pilih metode pembayaran
    if (btnCash) {
        btnCash.addEventListener('click', function() {
            console.log('Cash payment method selected.');
            if (paymentOptionsDiv) paymentOptionsDiv.style.display = 'none';
            if (cashPaymentFormDiv) cashPaymentFormDiv.style.display = 'block';

            // Fokuskan input uang diterima saat form tunai muncul
            if (uangDiterimaInput) uangDiterimaInput.focus();

             // Update total tagihan di form tunai (jika ada elemen terpisah) - sekarang pakai modalTotalBillSpan
        });
    } else { console.warn('Cash button not found.'); }

    if (btnQris) {
        btnQris.addEventListener('click', function() {
            console.log('QRIS payment method selected.');
            if (paymentOptionsDiv) paymentOptionsDiv.style.display = 'none';
            if (qrisPaymentInfoDiv) qrisPaymentInfoDiv.style.display = 'block';
             // Update total tagihan di info QRIS (jika ada elemen terpisah) - sekarang pakai modalTotalBillSpan
        });
    } else { console.warn('QRIS button not found.'); }


    // Listener untuk input uang diterima (metode tunai)
    if (uangDiterimaInput && kembalianDisplay && btnBayarCash && modalTotalBillSpan) {
        uangDiterimaInput.addEventListener('input', function() {
            const uangDiterima = parseFloat(uangDiterimaInput.value) || 0;
            const totalTagihan = parseFloat(modalTotalBillSpan.textContent.replace('Rp ', '').replace(/\./g, '').replace(/,/g, '.')) || 0; // Ambil total dari span modal
            const kembalian = uangDiterima - totalTagihan;

            // console.log(`Uang Diterima: ${uangDiterima}, Total Tagihan: ${totalTagihan}, Kembalian: ${kembalian}`); // Debug log

            kembalianDisplay.textContent = formatRupiah(kembalian);

            // Aktifkan tombol Bayar Tunai jika uang diterima cukup atau lebih
            if (uangDiterima >= totalTagihan) {
                $(btnBayarCash).prop('disabled', false);
            } else {
                $(btnBayarCash).prop('disabled', true);
            }
        });
    } else { console.warn('Cash payment input/display elements not found.'); }


    // Listener untuk tombol "Bayar Tunai"
    if (btnBayarCash && uangDiterimaInput && modalTotalBillSpan) {
        btnBayarCash.addEventListener('click', function() {
            const uangDiterima = parseFloat(uangDiterimaInput.value) || 0;
            const totalTagihan = parseFloat(modalTotalBillSpan.textContent.replace('Rp ', '').replace(/\./g, '').replace(/,/g, '.')) || 0; // Ambil total dari span modal

            if (uangDiterima < totalTagihan) {
                alert('Uang yang diterima kurang dari total tagihan.');
                return;
            }

            // Panggil fungsi proses pembayaran dengan metode 'cash'
            processPaymentAjax('cash', uangDiterima);
        });
    } else { console.warn('Pay Cash button or related elements not found.'); }


    // Listener untuk tombol "Konfirmasi Pembayaran Non-Tunai"
    if (btnConfirmQris) {
        btnConfirmQris.addEventListener('click', function() {
            // Panggil fungsi proses pembayaran dengan metode 'qris'
            // Jumlah uang tidak perlu dikirim untuk QRIS, cukup methodnya
            processPaymentAjax('qris');
        });
    } else { console.warn('Confirm QRIS button not found.'); }


    // Listener untuk tombol "Kembali" dari form tunai
    if (btnBackToOptions && paymentOptionsDiv && cashPaymentFormDiv) {
        btnBackToOptions.addEventListener('click', function() {
            console.log('Back button clicked from Cash form.');
            resetPaymentModal(); // Kembali ke tampilan pilihan metode
        });
    } else { console.warn('Back button from Cash form or related elements not found.'); }


    // Listener untuk tombol "Kembali" dari info QRIS
    if (btnBackToOptionsQris && paymentOptionsDiv && qrisPaymentInfoDiv) {
        btnBackToOptionsQris.addEventListener('click', function() {
             console.log('Back button clicked from QRIS info.');
            resetPaymentModal(); // Kembali ke tampilan pilihan metode
        });
    } else { console.warn('Back button from QRIS info or related elements not found.'); }

    // Listener untuk tombol aksi setelah sukses pembayaran (Kembali ke Dashboard / Lihat Ringkasan)
    // Listener ini diatur di form_submit.js setelah pembayaran berhasil,
    // karena URL ringkasan pesanan memerlukan ID reservasi yang didapat setelah order disimpan.
    // Pastikan elemen btnBackToDashboard dan btnViewSummary ada di HTML dan diambil di initPaymentModal.
    // Log di initPaymentModal sudah memeriksa keberadaan elemen ini.

    console.log('Payment modal event listeners attached.');
}


// Helper function to format number as Rupiah currency
export function formatRupiah(number) {
    if (isNaN(number)) {
        return 'Rp 0';
    }
    const formatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0, // Tidak menampilkan desimal untuk Rupiah
        maximumFractionDigits: 0,
    });
    return formatter.format(number);
}
