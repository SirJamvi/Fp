// resources/js/utils.js

/**
 * Menampilkan pesan kustom kepada pengguna.
 * Menggantikan fungsi alert() bawaan browser.
 * @param {string} message - Pesan yang akan ditampilkan.
 * @param {string} type - Tipe alert (e.g., 'success', 'danger', 'info', 'warning'). Default: 'info'.
 * @param {string} title - Judul alert. Default: 'Pemberitahuan'.
 */
export function showCustomAlert(message, type = 'info', title = 'Pemberitahuan') {
    // Buat elemen div sementara untuk pesan alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show fixed-top mx-auto mt-3 shadow-lg`;
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.zIndex = '1050'; // Pastikan di atas modal Bootstrap
    alertDiv.style.left = '0';
    alertDiv.style.right = '0';
    alertDiv.style.top = '0'; // Posisikan di bagian atas

    alertDiv.innerHTML = `
        <h5 class="alert-heading">${title}</h5>
        <p>${message}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alertDiv);

    // Hapus alert secara otomatis setelah beberapa detik
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000); // Tampilkan selama 5 detik
}
