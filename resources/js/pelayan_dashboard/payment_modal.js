// resources/js/pelayan_dashboard/payment_modal.js

import { processPaymentAjax } from './form_submit';
import { showCustomAlert } from '../utils'; // Import custom alert utility

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
let btnCash;
let btnQris;
let paymentSuccessActionsDiv;
let btnBackToDashboard;
let btnViewSummary;

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
    paymentSuccessActionsDiv = elements.paymentSuccessActions;
    btnBackToDashboard = elements.btnBackToDashboard;
    btnViewSummary = elements.btnViewSummary;

    attachModalEventListeners();
    console.log('Payment Modal initialized.');
}

export function showPaymentModal(reservasiId, totalBill, kodeOrder) {
    console.log(`Showing payment modal for Reservasi ID: ${reservasiId}, Total Bill: ${totalBill}, Kode Order: ${kodeOrder}`);
    if (!paymentModalEl) {
        console.error('Payment modal element not found. Cannot show modal.');
        showCustomAlert('Elemen modal pembayaran tidak ditemukan.', 'danger');
        return;
    }

    resetPaymentModal();

    paymentModalEl.dataset.reservasiId = reservasiId;
    paymentModalEl.dataset.totalBill = totalBill;
    paymentModalEl.dataset.kodeOrder = kodeOrder;

    if (modalTotalBillSpan) {
        modalTotalBillSpan.textContent = formatRupiah(totalBill);
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

    const paymentModal = new bootstrap.Modal(paymentModalEl);
    paymentModal.show();
    console.log('Bootstrap payment modal show() called.');
}

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

function resetPaymentModal() {
    console.log('Resetting payment modal display.');
    if (paymentOptionsDiv) paymentOptionsDiv.style.display = 'block';
    if (cashPaymentFormDiv) cashPaymentFormDiv.style.display = 'none';
    if (qrisPaymentInfoDiv) qrisPaymentInfoDiv.style.display = 'none';
    if (loadingIndicatorModal) loadingIndicatorModal.style.display = 'none';
    if (paymentSuccessMessageModal) paymentSuccessMessageModal.style.display = 'none';
    if (paymentErrorMessageModal) paymentErrorMessageModal.style.display = 'none';
    if (paymentSuccessActionsDiv) paymentSuccessActionsDiv.style.display = 'none';

    if (uangDiterimaInput) uangDiterimaInput.value = '';
    if (kembalianDisplay) {
        kembalianDisplay.textContent = formatRupiah(0);
        kembalianDisplay.classList.remove('text-danger', 'text-success');
    }

    if (btnBackToOptions) btnBackToOptions.disabled = false;
    if (btnBackToOptionsQris) btnBackToOptionsQris.disabled = false;

    if (btnBayarCash) $(btnBayarCash).prop('disabled', true).html('<i class="bi bi-cash me-2"></i> Bayar Tunai');
    if (btnConfirmQris) $(btnConfirmQris).prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai');
}

function attachModalEventListeners() {
    console.log('Attaching payment modal event listeners...');
    if (!paymentModalEl) {
        console.warn('Payment modal element not found. Modal event listeners not attached.');
        return;
    }

    if (btnCash) {
        btnCash.addEventListener('click', function () {
            console.log('Cash payment method selected.');
            if (paymentOptionsDiv) paymentOptionsDiv.style.display = 'none';
            if (cashPaymentFormDiv) cashPaymentFormDiv.style.display = 'block';
            if (uangDiterimaInput) uangDiterimaInput.focus();
        });
    } else { console.warn('Cash button not found.'); }

    if (btnQris) {
        btnQris.addEventListener('click', function () {
            console.log('QRIS payment method selected.');
            if (paymentOptionsDiv) paymentOptionsDiv.style.display = 'none';
            if (qrisPaymentInfoDiv) qrisPaymentInfoDiv.style.display = 'block';
        });
    } else { console.warn('QRIS button not found.'); }

    // Diperbaiki bagian perhitungan dan tampilan kembalian
    if (uangDiterimaInput && kembalianDisplay && btnBayarCash && paymentModalEl) {
        uangDiterimaInput.addEventListener('input', function () {
            const uangDiterima = parseFloat(uangDiterimaInput.value) || 0;
            const totalTagihan = parseFloat(paymentModalEl.dataset.totalBill) || 0;
            const kembalian = uangDiterima - totalTagihan;

            console.log(`Uang Diterima: ${uangDiterima}, Total Tagihan: ${totalTagihan}, Kembalian: ${kembalian}`);

            if (uangDiterima >= totalTagihan) {
                kembalianDisplay.textContent = formatRupiah(kembalian);
                kembalianDisplay.classList.remove('text-danger');
                kembalianDisplay.classList.add('text-success');
                $(btnBayarCash).prop('disabled', false);
            } else {
                kembalianDisplay.textContent = 'Belum cukup';
                kembalianDisplay.classList.remove('text-success');
                kembalianDisplay.classList.add('text-danger');
                $(btnBayarCash).prop('disabled', true);
            }
        });
    } else { console.warn('Cash payment input/display elements not found.'); }

    // Tombol Bayar Tunai dan Konfirmasi QRIS listeners dipindahkan ke form_submit.js
    // untuk konsistensi karena mereka memanggil processPaymentAjax

    if (btnBackToOptions && paymentOptionsDiv && cashPaymentFormDiv) {
        btnBackToOptions.addEventListener('click', function () {
            console.log('Back button clicked from Cash form.');
            resetPaymentModal();
        });
    } else { console.warn('Back button from Cash form or related elements not found.'); }

    if (btnBackToOptionsQris && paymentOptionsDiv && qrisPaymentInfoDiv) {
        btnBackToOptionsQris.addEventListener('click', function () {
            console.log('Back button clicked from QRIS info.');
            resetPaymentModal();
        });
    } else { console.warn('Back button from QRIS info or related elements not found.'); }

    console.log('Payment modal event listeners attached.');
}

export function formatRupiah(number) {
    if (isNaN(number)) {
        return 'Rp 0';
    }
    const formatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
    return formatter.format(number);
}
