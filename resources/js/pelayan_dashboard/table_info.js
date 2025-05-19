// resources/js/pelayan_dashboard/table_info.js

// Import dependencies (like checkSubmitButtonStatus from form_submit)
import { checkSubmitButtonStatus } from './form_submit';

// --- Elemen DOM (diambil di main.js dan diteruskan) ---
let mejaSelect;
let selectedTableInfoDiv;
let jumlahTamuInput;

// Export fungsi inisialisasi untuk menerima elemen DOM
export function initTableInfo(elements) {
    console.log('Initializing Table Info module...'); // Added Log
    mejaSelect = elements.mejaSelect;
    selectedTableInfoDiv = elements.selectedTableInfoDiv;
    jumlahTamuInput = elements.jumlahTamuInput;

    console.log('Table Info initialized.'); // Added Log

    // Attach event listener to the table select element
    attachTableSelectListener();
    // Initial update based on the default selected value (if any)
    updateSelectedTableInfo();
}

// Function to update the displayed table information
export function updateSelectedTableInfo() {
    console.log('Executing updateSelectedTableInfo...'); // Added Log
    if (!mejaSelect || !selectedTableInfoDiv) {
        console.warn('Meja select or selected table info div not found. Cannot update table info display.'); // Added Log
        return;
    }

    const selectedOption = mejaSelect.options[mejaSelect.selectedIndex];

    if (selectedOption && selectedOption.value !== "") {
        const nomorMeja = selectedOption.textContent.split('(')[0].trim(); // Extract number before '('
        const area = selectedOption.dataset.area || '-';
        const status = selectedOption.dataset.status || '-';

        selectedTableInfoDiv.innerHTML = `Meja: <strong>${nomorMeja}</strong> | Area: <strong>${area}</strong> | Status: <strong>${ucfirst(status)}</strong>`;
        selectedTableInfoDiv.style.display = 'block';
        console.log(`Table info updated: Meja ${nomorMeja}, Area ${area}, Status ${status}.`); // Added Log
    } else {
        selectedTableInfoDiv.style.display = 'none';
        console.log('No table selected. Hiding table info.'); // Added Log
    }

    // Also check submit button status whenever table selection changes
    checkSubmitButtonStatus();
    console.log('updateSelectedTableInfo complete.'); // Added Log
}

// Attach event listener to the table select element
function attachTableSelectListener() {
    console.log('Attaching table select listener...'); // Added Log
    if (mejaSelect) {
        mejaSelect.addEventListener('change', updateSelectedTableInfo);
        console.log('Table select change listener attached.'); // Added Log
    } else {
        console.warn('mejaSelect not found. Table select listener not attached.'); // Added Log
    }
}

// Helper function to capitalize the first letter (could be in a utils file)
function ucfirst(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Export updateSelectedTableInfo so it can be called from other modules if needed
// Export checkSubmitButtonStatus from form_submit.js directly where needed (e.g., cart_manager)