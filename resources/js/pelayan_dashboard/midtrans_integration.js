// resources/js/pelayan_dashboard/midtrans_integration.js

/**
 * Memicu popup Midtrans Snap
 * @param {string} snapToken — token yang dikembalikan oleh server
 * @returns {boolean} true jika pemanggilan berhasil dipicu, false jika gagal
 */
export function triggerMidtransSnap(snapToken) {
    // Cek apakah library Snap sudah dimuat
    if (typeof window.snap === 'undefined') {
        console.error('Midtrans Snap JS is not loaded. Cannot trigger snap.');
        alert('Gagal memuat Midtrans. Silakan coba lagi.');
        return false;
    }

    try {
        window.snap.pay(snapToken, {
            onSuccess: function(result) {
                console.log('Pembayaran sukses:', result);
                // redirect atau update UI di sini
                // Anda mungkin ingin memicu refresh halaman atau menampilkan pesan sukses
                // setelah pembayaran Midtrans berhasil.
                // Contoh: window.location.reload();
                // Atau panggil fungsi di form_submit.js untuk menampilkan pesan sukses dan tombol aksi
                // Misalnya, panggil fungsi baru di form_submit.js: handlePaymentSuccess(result);
                // Untuk saat ini, kita log saja dan biarkan user menutup modal.
            },
            onPending: function(result) {
                console.log('Pembayaran pending:', result);
                // beri tahu user untuk melanjutkan pembayaran
                alert('Pembayaran Anda tertunda. Harap selesaikan pembayaran melalui metode yang Anda pilih.');
            },
            onError: function(result) {
                console.error('Pembayaran error:', result);
                alert('Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: function() {
                console.warn('User menutup popup Snap tanpa menyelesaikan pembayaran.');
                // Anda bisa memberi tahu user bahwa pembayaran belum selesai
                // alert('Anda belum menyelesaikan pembayaran.'); // Mungkin terlalu mengganggu, log saja cukup
            }
        });
        console.log('Midtrans snap.pay() called.');
        return true;
    } catch (e) {
        console.error('Error calling snap.pay():', e);
        alert('Terjadi kesalahan saat memulai pembayaran Midtrans.');
        return false;
    }
}

/**
 * (Opsional) Fungsi untuk menambahkan tag <script> Snap.js ke <head> secara dinamis.
 * Panggil ini sekali dengan clientKey Anda sebelum memicu triggerMidtransSnap.
 * Namun, disarankan memuatnya di layout utama Blade untuk menghindari duplikasi.
 */
export function loadMidtransSnapJs(clientKey, isProduction = false) {
    const baseUrl = isProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';

    if (document.querySelector(`script[src="${baseUrl}"]`)) {
        console.log('Midtrans Snap script already exists.');
        return; // sudah ada, tidak perlu tambah lagi
    }

    const script = document.createElement('script');
    script.src = baseUrl;
    script.setAttribute('data-client-key', clientKey);
    script.async = true; // Muat secara asynchronous
    script.onload = () => console.log('Midtrans Snap script loaded.');
    script.onerror = () => {
        console.error('Failed to load Midtrans Snap script.');
        alert('Gagal memuat skrip pembayaran. Silakan muat ulang halaman.');
    };
    document.head.appendChild(script);
    console.log('Attempted to append Midtrans Snap script to head.');
}
