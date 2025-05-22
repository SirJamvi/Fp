// resources/js/koki_dashboard/main.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('Koki Dashboard scripts loaded.');

    const ordersTableBody = document.getElementById('ordersTableBody');
    const newOrdersCount = document.getElementById('newOrdersCount');
    const preparingOrdersCount = document.getElementById('preparingOrdersCount');
    const completedOrdersCount = document.getElementById('completedOrdersCount');
    const getOrdersRoute = document.getElementById('getOrdersRoute').value;
    const updateOrderStatusBaseRoute = document.getElementById('updateOrderStatusBaseRoute').value; 
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let previousNewOrdersCount = 0;

    // Fungsi utama untuk mengambil dan menampilkan pesanan
    async function fetchAndRenderOrders() {
        if (ordersTableBody.innerHTML.trim() === '' || ordersTableBody.children.length === 0) {
            ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Memuat pesanan...</td></tr>';
        }
        
        try {
            const response = await fetch(getOrdersRoute, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }

            const data = await response.json();

            if (data.success) {
                newOrdersCount.textContent = data.summary.new_orders;
                preparingOrdersCount.textContent = data.summary.preparing_orders;
                completedOrdersCount.textContent = data.summary.completed_orders;
                previousNewOrdersCount = data.summary.new_orders;
                renderOrdersTable(data.reservations);
            } else {
                ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat pesanan: ${data.message}</td></tr>`;
            }

        } catch (error) {
            console.error('Error fetching orders:', error);
            ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Terjadi kesalahan saat memuat pesanan. Silakan cek konsol browser untuk detail.</td></tr>`;
        }
    }

    // Render tabel pesanan
    function renderOrdersTable(reservations) {
        ordersTableBody.innerHTML = '';

        if (reservations.length === 0) {
            ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pesanan aktif.</td></tr>';
            return;
        }

        reservations.forEach(reservation => {
            let rowSpan = reservation.items.length;
            reservation.items.forEach((item, itemIndex) => {
                const row = document.createElement('tr');

                if (itemIndex === 0) {
                    row.innerHTML += `
                        <td rowspan="${rowSpan}" class="text-center">
                            <span class="text-dark fw-bold">${reservation.kode_reservasi}</span>
                        </td>
                        <td rowspan="${rowSpan}" class="text-center">
                            <span class="text-dark">${reservation.table_display}</span>
                        </td>
                    `;
                }

                row.innerHTML += `
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${item.menu_image}" class="menu-image me-2" alt="${item.menu_name}">
                            <div>
                                <h6 class="mb-0 text-sm">${item.menu_name}</h6>
                                ${item.notes ? `<p class="order-notes mb-0">${item.notes}</p>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="text-xs fw-bold">${item.quantity}x</span>
                    </td>
                    ${itemIndex === 0 ? `
                        <td rowspan="${rowSpan}" class="text-center">
                            <span class="text-xs text-secondary">${new Date(reservation.ordered_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}</span>
                        </td>
                        <td rowspan="${rowSpan}" class="text-center">
                            <span class="badge ${reservation.status_badge_class}">${reservation.current_status.charAt(0).toUpperCase() + reservation.current_status.slice(1)}</span>
                        </td>
                        <td rowspan="${rowSpan}" class="align-middle">
                            ${generateActionButtons(reservation.current_status, reservation.reservasi_id)}
                        </td>
                    ` : ''}
                `;
                ordersTableBody.appendChild(row);
            });
        });

        attachButtonListeners();
    }

    // Generate tombol aksi
    function generateActionButtons(status, reservasiId) {
        let buttons = '';
        if (status === 'pending') {
            buttons += `<button class="btn btn-primary btn-sm me-2 update-status-btn" data-reservasi-id="${reservasiId}" data-status="preparing">Siapkan</button>`;
            buttons += `<button class="btn btn-secondary btn-sm update-status-btn" data-reservasi-id="${reservasiId}" data-status="cancelled">Batalkan</button>`;
        } else if (status === 'preparing') {
            buttons += `<button class="btn btn-success btn-sm update-status-btn" data-reservasi-id="${reservasiId}" data-status="completed">Selesai</button>`;
            buttons += `<button class="btn btn-secondary btn-sm update-status-btn" data-reservasi-id="${reservasiId}" data-status="cancelled">Batalkan</button>`;
        }
        return buttons;
    }

    // Handler tombol aksi
    function attachButtonListeners() {
        document.querySelectorAll('.update-status-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const reservasiId = this.dataset.reservasiId;
                const newStatus = this.dataset.status;

                // Langsung eksekusi tanpa confirm dialog
                this.disabled = true;
                this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...`;

                try {
                    const url = `${updateOrderStatusBaseRoute}/${reservasiId}/update-status`;
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ status: newStatus })
                    });

                    const data = await response.json();

                    if (data.success) {
                        console.log('Status diperbarui:', data.message);
                        fetchAndRenderOrders();
                    } else {
                        console.error('Gagal memperbarui:', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.disabled = false;
                }
            });
        });
    }

    // Inisialisasi pertama dan polling
    fetchAndRenderOrders();
    setInterval(fetchAndRenderOrders, 10000);
});