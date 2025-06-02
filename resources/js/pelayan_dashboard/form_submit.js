// resources/js/pelayan_dashboard/form_submit.js

import { getCartItems, clearCart } from './cart_manager';
import { showPaymentModal, hidePaymentModal } from './payment_modal';
import { showCustomAlert } from '../utils'; // Import custom alert utility

// Elemen DOM
let orderForm,
    submitOrderBtn,
    mejaSelect,
    jumlahTamuInput,
    loadingIndicatorModal,
    paymentSuccessMessageModal,
    paymentErrorMessageModal,
    btnBayarCashModal,
    btnConfirmQrisModal,
    btnBackToOptionsModal,
    btnBackToOptionsQrisModal,
    uangDiterimaInputModal,
    processPaymentRouteInput,
    orderSummaryRouteInput,
    paymentModalEl,
    areaSelect,
    btnBackToDashboard,
    btnViewSummary;

export function initFormSubmit(elements) {
  console.log('Initializing Form Submit module...');

  // Simpan referensi elemen‐elemen DOM
  orderForm                   = elements.orderForm;
  submitOrderBtn              = elements.submitOrderBtn;
  mejaSelect                  = elements.mejaSelect;
  jumlahTamuInput             = elements.jumlahTamuInput;
  loadingIndicatorModal       = elements.loadingIndicator; // Loading indicator di modal pembayaran
  paymentSuccessMessageModal  = elements.paymentSuccessMessage;
  paymentErrorMessageModal    = elements.paymentErrorMessage;
  btnBayarCashModal           = elements.btnBayarCash;
  btnConfirmQrisModal         = elements.btnConfirmQris;
  btnBackToOptionsModal       = elements.btnBackToOptions;
  btnBackToOptionsQrisModal   = elements.btnBackToOptionsQris;
  uangDiterimaInputModal      = elements.uangDiterimaInput;
  processPaymentRouteInput    = elements.processPaymentRouteInput;
  orderSummaryRouteInput      = elements.orderSummaryRouteInput;
  paymentModalEl              = elements.paymentModalEl;
  areaSelect                  = elements.areaSelect;
  btnBackToDashboard          = elements.btnBackToDashboard;
  btnViewSummary              = elements.btnViewSummary;

  attachFormSubmitListeners();

  // Pasang listener untuk event ketika modal pembayaran ditutup
  if (paymentModalEl) {
    paymentModalEl.addEventListener('hidden.bs.modal', async function () {
      // Hanya batalkan reservasi jika statusnya masih "pending_payment"
      const reservasiId = paymentModalEl.dataset.reservasiId;
      const status      = paymentModalEl.dataset.reservasiStatus;

      if (reservasiId && status === 'pending_payment') {
        console.log(`[FormSubmit] Modal ditutup sebelum bayar, membatalkan reservasi ID ${reservasiId}`);

        try {
          const cancelUrl = `/pelayan/reservasi/${reservasiId}/cancel`;
          const token     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          const res = await fetch(cancelUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: JSON.stringify({})
          });

          console.log('Cancel response status:', res.status);

          if (!res.ok) {
            // Jika status HTTP bukan 200, baca pesan error JSON (jika ada) atau lempar
            let errMsg = 'Gagal membatalkan reservasi.';
            try {
              const errJson = await res.json();
              if (errJson.message) errMsg = errJson.message;
            } catch (_) { }
            throw new Error(errMsg);
          }

          // Ambil JSON response
          const json = await res.json();
          console.log('Cancel response JSON:', json);

          if (json.success) {
            showCustomAlert('Pesanan dibatalkan. Meja kembali tersedia.', 'info', 'Pesanan Dibatalkan');
          } else {
            console.warn('Cancel reservation response (success=false):', json);
          }
        } catch (err) {
          console.error('Error cancelling reservation:', err);
          showCustomAlert(err.message || 'Gagal mengirim permintaan batal pesanan.', 'danger', 'Cancel Error');
        }
      }
    });
  }

  console.log('Form Submit module initialized.');
}

export function checkSubmitButtonStatus() {
  if (!submitOrderBtn || !mejaSelect || !jumlahTamuInput) {
    console.warn('One or more essential elements for submit button status check are missing in form_submit.js.');
    return;
  }

  const cart         = getCartItems(); // Dapatkan item keranjang dari cart_manager
  const isCartEmpty  = Object.keys(cart).length === 0;
  const isTableSel   = mejaSelect.value !== "";
  const tamuValid    = parseInt(jumlahTamuInput.value, 10) >= 1;

  console.log(`[FormSubmit] checkSubmitButtonStatus: Cart Empty: ${isCartEmpty}, Table Selected: ${isTableSel}, Tamu Valid: ${tamuValid}`);

  submitOrderBtn.disabled = isCartEmpty || !isTableSel || !tamuValid;
}

function attachFormSubmitListeners() {
  // Event listener submit form order (membuat reservasi & orders)
  if (orderForm) {
    orderForm.addEventListener('submit', handleFormSubmit);
  }

  // Event listener untuk tombol bayar tunai di modal
  if (btnBayarCashModal) {
    btnBayarCashModal.addEventListener('click', function () {
      const uangDiterima  = parseFloat(uangDiterimaInputModal.value) || 0;
      const totalTagihan   = parseFloat(paymentModalEl.dataset.totalBill) || 0;

      if (uangDiterima < totalTagihan) {
        showCustomAlert('Uang yang diterima kurang dari total tagihan.', 'warning', 'Pembayaran Tunai');
        return;
      }
      processPaymentAjax('tunai', uangDiterima);
    });
  }

  // Event listener untuk tombol konfirmasi QRIS
  if (btnConfirmQrisModal) {
    btnConfirmQrisModal.addEventListener('click', function () {
      processPaymentAjax('qris');
    });
  }

  // Tombol Back to Dashboard (setelah pembayaran sukses)
  if (btnBackToDashboard) {
    btnBackToDashboard.addEventListener('click', () => {
      window.location.href = '/pelayan/dashboard';
    });
  }

  // Tombol Lihat Ringkasan (setelah pembayaran sukses)
  if (btnViewSummary) {
    btnViewSummary.addEventListener('click', function () {
      const reservasiId = this.dataset.reservasiId;
      if (reservasiId) {
        const summaryRoute = orderSummaryRouteInput.value.replace(':reservasiId', reservasiId);
        window.location.href = summaryRoute;
      } else {
        showCustomAlert('ID Reservasi tidak ditemukan untuk melihat ringkasan.', 'danger');
      }
    });
  }
}

async function handleFormSubmit(e) {
  e.preventDefault();
  console.log('Handling form submit...');

  const cart = getCartItems();
  if (Object.keys(cart).length === 0) {
    showCustomAlert('Keranjang kosong. Mohon pilih menu terlebih dahulu.', 'warning');
    return;
  }

  if (!mejaSelect.value) {
    showCustomAlert('Pilih meja terlebih dahulu.', 'warning');
    mejaSelect.focus();
    return;
  }

  const jumlahTamu = parseInt(jumlahTamuInput.value, 10);
  if (isNaN(jumlahTamu) || jumlahTamu < 1) {
    showCustomAlert('Jumlah tamu harus minimal 1.', 'warning');
    jumlahTamuInput.focus();
    return;
  }

  // Disable tombol dan tampilkan spinner
  submitOrderBtn.disabled = true;
  submitOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

  const formData = new FormData(orderForm);
  // Tambahkan item keranjang ke formData
  Object.keys(cart).forEach((menuId, index) => {
    formData.append(`items[${index}][menu_id]`, cart[menuId].id);
    formData.append(`items[${index}][quantity]`, cart[menuId].quantity);
    formData.append(`items[${index}][notes]`, cart[menuId].notes || '');
  });

  try {
    const response = await fetch(orderForm.action, {
      method: orderForm.method,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: formData
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('Order submission failed:', errorData);
      if (response.status === 422 && errorData.errors) {
        let errorMessage = 'Terjadi kesalahan validasi:<br>';
        for (const key in errorData.errors) {
          errorMessage += `- ${errorData.errors[key].join(', ')}<br>`;
        }
        showCustomAlert(errorMessage, 'danger', 'Kesalahan Validasi');
      } else {
        showCustomAlert(errorData.message || 'Gagal membuat pesanan. Silakan coba lagi.', 'danger');
      }
      return; // Penting: keluar jika ada error
    }

    const data = await response.json();
    console.log('Order submitted successfully:', data);

    if (data.success) {
      handleOrderSuccess(data);
    } else {
      handleOrderError(data);
    }
  } catch (error) {
    console.error('Error during order submission:', error);
    showCustomAlert('Terjadi kesalahan saat memproses pesanan: ' + error.message, 'danger');
  } finally {
    // Re-enable tombol dan kembalikan teks asli
    submitOrderBtn.disabled = false;
    submitOrderBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Proses Pesanan';
  }
}

function handleOrderSuccess(response) {
  if (!paymentModalEl) {
    showCustomAlert('Modal pembayaran tidak tersedia.', 'danger');
    return;
  }

  // Tandai bahwa reservasi masih menunggu pembayaran
  paymentModalEl.dataset.reservasiId     = response.reservasi_id;
  paymentModalEl.dataset.reservasiStatus = 'pending_payment';
  paymentModalEl.dataset.totalBill       = parseFloat(response.total_bill);
  paymentModalEl.dataset.kodeOrder       = response.order_code; // Menggunakan order_code

  showPaymentModal(response.reservasi_id, response.total_bill, response.order_code);
}

function handleOrderError(response) {
  showCustomAlert('Gagal menyimpan pesanan: ' + response.message, 'danger');
}

export async function processPaymentAjax(paymentMethod, amountPaid = null) {
  const reservasiId = paymentModalEl?.dataset.reservasiId;
  const totalBill   = parseFloat(paymentModalEl?.dataset.totalBill);

  if (!reservasiId || isNaN(totalBill) || !processPaymentRouteInput) {
    showCustomAlert('Data pembayaran tidak lengkap.', 'danger');
    hidePaymentModal();
    return;
  }

  const routeTemplate = processPaymentRouteInput.value;
  const finalUrl      = routeTemplate.replace(':reservasiId', reservasiId);

  let payload = { payment_method: paymentMethod };
  if (paymentMethod === 'tunai') {
    if (amountPaid === null || isNaN(amountPaid) || amountPaid < totalBill) {
      showCustomAlert('Uang diterima kurang dari total tagihan atau tidak valid.', 'warning');
      return;
    }
    payload.uang_diterima = amountPaid;
  }
  payload.total_bill = totalBill; // untuk validasi backend

  // Tampilkan spinner dan disable semua input/button di modal
  loadingIndicatorModal.style.display = 'block';
  paymentModalEl.querySelectorAll('button').forEach(b => b.disabled = true);
  paymentModalEl.querySelectorAll('input').forEach(i => i.disabled = true);

  try {
    const response = await fetch(finalUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('Payment processing failed:', errorData);
      throw new Error(errorData.message || 'Gagal memproses pembayaran.');
    }

    const data = await response.json();
    console.log('Payment response from backend:', data);

    // Jika ini metode QRIS, cek apakah ada snap_token
    if (paymentMethod === 'qris' && data.snap_token) {
      // Panggil Midtrans Snap
      window.snap.pay(data.snap_token, {
        onSuccess: (result) => {
          console.log('Midtrans onSuccess:', result);
          // Tandai reservasi sudah lunas, agar hidden.bs.modal nanti tidak cancel
          paymentModalEl.dataset.reservasiStatus = 'paid';

          // Redirect ke halaman ringkasan (order summary)
          const summaryUrl = orderSummaryRouteInput.value.replace(':reservasiId', reservasiId);
          window.location.href = summaryUrl;
        },
        onPending: (result) => {
          console.log('Midtrans onPending:', result);
          // Misal tampilkan QRIS Image:
          if (result.qris_image_url) {
            document.getElementById('qris-container').innerHTML =
              '<img src="' + result.qris_image_url + '" alt="QRIS Code" class="img-fluid"><br>' +
              '<small>Scan QR code di atas untuk membayar</small>';
            document.getElementById('qris-container').style.display = 'block';
          }
        },
        onError: (error) => {
          console.error('Midtrans onError:', error);
          showCustomAlert('Pembayaran QRIS gagal. Silakan coba lagi.', 'danger', 'QRIS Payment');
          // Tampilkan kembali opsi QRIS agar user bisa mencoba ulang
          paymentErrorMessageModal.style.display = 'block';
          document.getElementById('paymentOptions').style.display = 'block';
          document.getElementById('qrisPaymentInfo').style.display = 'block';
        },
        onClose: () => {
          console.warn('User menutup popup Midtrans sebelum selesai.');
          // Kembalikan tampilan opsi pembayaran QRIS
          document.getElementById('paymentOptions').style.display = 'block';
          document.getElementById('qrisPaymentInfo').style.display = 'block';
        }
      });

      // Setelah memanggil snap.pay(), kita berhenti di sini. Semua penanganan setelah
      // sukses/pending/error ditangani oleh callback.
      return;
    }

    // Jika metode TUNAI, lanjutkan seperti biasa:
    if (paymentMethod === 'tunai') {
      loadingIndicatorModal.style.display = 'none';
      paymentSuccessMessageModal.style.display = 'block';
      document.getElementById('paymentOptions').style.display    = 'none';
      document.getElementById('cashPaymentForm').style.display    = 'none';
      document.getElementById('paymentSuccessActions').style.display = 'flex';

      // Set reservasi_id untuk tombol Lihat Ringkasan
      if (btnViewSummary) {
        btnViewSummary.dataset.reservasiId = reservasiId;
      }
      clearCart(); // kosongkan keranjang
      showCustomAlert('Pembayaran Tunai Berhasil!', 'success', 'Pembayaran Selesai');

      // Tandai bahwa status sudah 'paid', agar hidden.bs.modal nanti tidak cancel
      paymentModalEl.dataset.reservasiStatus = 'paid';
      return;
    }

    // Jika bukan qris atau tunai, tampilkan error
    throw new Error(data.message || 'Metode pembayaran tidak dikenali.');
  }
  catch (error) {
    console.error('Error in payment processing:', error);
    loadingIndicatorModal.style.display = 'none';
    paymentErrorMessageModal.style.display = 'block';
    paymentErrorMessageModal.textContent = error.message || 'Terjadi kesalahan saat memproses pembayaran.';
    showCustomAlert(error.message || 'Gagal memproses pembayaran.', 'danger', 'Error Pembayaran');

    // Kembalikan tampilan opsi pembayaran atau form yang relevan
    document.getElementById('paymentOptions').style.display = 'block';
    if (paymentMethod === 'tunai') {
      document.getElementById('cashPaymentForm').style.display = 'block';
    } else if (paymentMethod === 'qris') {
      document.getElementById('qrisPaymentInfo').style.display = 'block';
    }
  }
  finally {
    // Aktifkan kembali semua kontrol di modal
    paymentModalEl.querySelectorAll('button').forEach(b => b.disabled = false);
    paymentModalEl.querySelectorAll('input').forEach(i => i.disabled = false);
    // Pastikan tombol “Bayar Tunai” tetap disable bila uang tidak cukup
    if (paymentMethod === 'tunai' && uangDiterimaInputModal && btnBayarCashModal) {
      const total  = parseFloat(paymentModalEl.dataset.totalBill);
      const recvd  = parseFloat(uangDiterimaInputModal.value) || 0;
      btnBayarCashModal.disabled = (recvd < total);
    }
  }
}
