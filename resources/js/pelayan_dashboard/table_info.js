// resources/js/pelayan_dashboard/table_info.js

import { checkSubmitButtonStatus } from './form_submit';
import { showCustomAlert } from '../utils'; // Import custom alert utility

// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let mejaSelect;
let selectedTableInfoDiv;
let jumlahTamuInput;
let areaSelect; // Tambahkan ini

// Export fungsi inisialisasi untuk menerima elemen DOM
export function initTableInfo(elements) {
    console.log('Initializing Table Info module...');
    mejaSelect = elements.mejaSelect;
    selectedTableInfoDiv = elements.selectedTableInfoDiv;
    jumlahTamuInput = elements.jumlahTamuInput;
    areaSelect = elements.areaSelect; // Inisialisasi ini

    // Lampirkan event listener untuk pemilihan area dan meja
    if (areaSelect) {
        areaSelect.addEventListener('change', function() {
            handleAreaChange(this.value);
            // checkSubmitButtonStatus() akan dipanggil di dalam handleAreaChange setelah fetch selesai
        });
    } else {
        console.warn('Area select element not found in table_info.js');
    }

    if (mejaSelect) {
        mejaSelect.addEventListener('change', function() {
            handleMejaChange(); // Fungsi baru untuk menangani detail pemilihan meja
            checkSubmitButtonStatus(); // Panggil check setelah perubahan meja
        });
    } else {
        console.warn('Meja select element not found in table_info.js');
    }

    if (jumlahTamuInput) {
        jumlahTamuInput.addEventListener('input', checkSubmitButtonStatus); // Panggil check setelah perubahan jumlah tamu
    } else {
        console.warn('Jumlah tamu input element not found in table_info.js');
    }

    // Panggil handleAreaChange saat inisialisasi jika ada area yang sudah terpilih (misal dari old input)
    if (areaSelect && areaSelect.value) {
        handleAreaChange(areaSelect.value);
    } else {
        // Pastikan meja select dinonaktifkan jika tidak ada area yang terpilih
        if (mejaSelect) {
            mejaSelect.innerHTML = '<option value="">-- Pilih Meja --</option>';
            mejaSelect.disabled = true;
        }
    }

    console.log('Table Info module initialized.');
}

function handleAreaChange(selectedArea) {
    // Asumsikan mejaSelect sudah diinisialisasi melalui initTableInfo
    mejaSelect.innerHTML = '<option value="">-- Pilih Meja --</option>';
    mejaSelect.disabled = true;
    selectedTableInfoDiv.style.display = 'none'; // Sembunyikan info meja saat area berubah

    if (selectedArea) {
        fetch(`/pelayan/get-meja-by-area/${encodeURIComponent(selectedArea)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.mejas.length > 0) {
                    data.mejas.forEach(meja => {
                        const option = document.createElement('option');
                        option.value = meja.id;
                        option.textContent = `Meja ${meja.nomor_meja} (Kapasitas: ${meja.kapasitas})`;
                        option.dataset.capacity = meja.kapasitas;
                        mejaSelect.appendChild(option);
                    });
                    mejaSelect.disabled = false;
                } else {
                    mejaSelect.innerHTML = '<option value="">-- Tidak ada meja tersedia --</option>';
                    showCustomAlert('Tidak ada meja tersedia untuk area ini.', 'info');
                }
                // Pengecekan awal setelah meja dimuat
                checkSubmitButtonStatus();
            })
            .catch(error => {
                console.error('Error fetching meja:', error);
                mejaSelect.innerHTML = '<option value="">-- Gagal memuat meja --</option>';
                showCustomAlert('Gagal memuat daftar meja. Silakan coba lagi.', 'danger');
                checkSubmitButtonStatus(); // Periksa juga saat terjadi error
            });
    } else {
        checkSubmitButtonStatus(); // Panggil check jika area tidak dipilih
    }
}

function handleMejaChange() {
    const selectedOption = mejaSelect.options[mejaSelect.selectedIndex];
    const capacity = selectedOption ? parseInt(selectedOption.dataset.capacity, 10) : 0;
    const jumlahTamu = parseInt(jumlahTamuInput.value, 10);

    if (selectedTableInfoDiv) {
        if (capacity > 0 && jumlahTamu > capacity) {
            document.getElementById('selectedTableCapacity').textContent = capacity;
            selectedTableInfoDiv.style.display = 'block';
        } else {
            selectedTableInfoDiv.style.display = 'none';
        }
    }
}
