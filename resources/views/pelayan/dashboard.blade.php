@extends('pelayan.layout.app')

@section('title', $title ?? 'Dashboard Pelayan')

@push('styles')
{{-- Custom styles for the dashboard --}}
<style>
    body {
        background-color: #f8f9fa; /* Light gray background */
    }
    .content-wrapper {
        padding-top: 20px;
        padding-bottom: 20px;
    }
    .card-header {
        font-weight: bold;
    }
    .menu-item-card img {
        height: 150px; /* Fixed height for menu images */
        object-fit: cover; /* Crop images to fit */
    }
    .menu-item-card .card-body {
        padding: 10px; /* Smaller padding */
    }
    .menu-item-card .card-title {
        font-size: 0.95rem; /* Slightly smaller title */
        margin-bottom: 5px;
    }
    .menu-item-card .card-text {
        font-size: 0.85rem; /* Smaller description text */
        color: #6c757d;
    }
    .menu-item-card .price {
        font-size: 1rem; /* Price font size */
        font-weight: bold;
        color: #28a745; /* Green color for price */
    }
    .add-to-cart-btn {
        font-size: 0.9rem; /* Smaller button text */
        padding: 5px 10px; /* Smaller button padding */
    }

    /* Order Summary Card */
    .order-summary-card {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: sticky; /* Make it sticky */
        top: 20px; /* Distance from top */
    }
    .order-summary-card h5 {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .cart-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    .cart-item-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
    }
     .cart-item-details h6 {
         font-size: 1rem;
         margin-bottom: 2px;
     }
     .cart-item-details p {
         font-size: 0.85rem;
     }
    .quantity-input {
        width: 50px; /* Smaller input width */
        padding: 5px;
        text-align: center;
    }
     .item-notes {
         font-size: 0.8rem;
         padding: 5px;
         margin-top: 5px;
     }
    .total-section {
        border-top: 1px solid #dee2e6;
        padding-top: 15px;
        margin-top: 15px;
    }
    .total-section div {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 1.1rem;
    }
    .total-section .grand-total {
        font-size: 1.3rem;
        font-weight: bold;
        color: #dc3545; /* Red color for grand total */
    }
    .btn-block-custom {
        display: block;
        width: 100%;
    }

    /* Alert Spacing */
    .alert-spaced {
        margin-top: 15px;
        margin-bottom: 15px;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .order-summary-card {
            position: static; /* Remove sticky on smaller screens */
            margin-top: 20px;
        }
    }
     @media (max-width: 575.98px) {
         .menu-item-card img {
             height: 120px;
         }
         .menu-item-card .card-body {
             padding: 8px;
         }
         .menu-item-card .card-title {
             font-size: 0.9rem;
         }
         .menu-item-card .card-text {
             font-size: 0.8rem;
         }
         .menu-item-card .price {
             font-size: 0.9rem;
         }
         .add-to-cart-btn {
             font-size: 0.85rem;
             padding: 4px 8px;
         }
         .cart-item-img {
             width: 50px;
             height: 50px;
         }
          .cart-item-details h6 {
              font-size: 0.9rem;
          }
          .cart-item-details p {
              font-size: 0.8rem;
          }
          .quantity-input {
              width: 45px;
          }
          .item-notes {
              font-size: 0.75rem;
          }
          .total-section div {
              font-size: 1rem;
          }
          .total-section .grand-total {
              font-size: 1.2rem;
          }
     }

     /* Payment Modal Custom Styles */
     #paymentOptions .btn-block.btn-lg {
         width: 100%;
         margin-bottom: 15px; /* Space between buttons */
     }

     #cashPaymentForm .form-group label,
     #qrisPaymentInfo p {
         font-weight: bold;
     }

     #kembalianDisplay {
         font-size: 1.5rem;
         font-weight: bold;
         margin-top: 5px;
     }

     #loadingIndicator {
         text-align: center;
     }

     #loadingIndicator .spinner-border {
         width: 3rem;
         height: 3rem;
     }

     #loadingIndicator p {
         font-size: 1.1rem;
         margin-top: 10px;
     }

     /* Basic styling for payment status messages */
     #paymentSuccessMessage,
     #paymentErrorMessage {
         margin-top: 15px;
     }

     /* Styles for success action buttons */
     #paymentSuccessActions {
         margin-top: 20px;
         text-align: center; /* Center the buttons */
     }
      #paymentSuccessActions .btn {
          margin: 0 5px; /* Space between buttons */
      }


</style>
@endpush

@section('content')
<div class="container-fluid content-wrapper">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show alert-spaced" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show alert-spaced" role="alert">
        <h5 class="alert-heading"><i class="bi bi-x-octagon-fill me-2"></i> Terjadi Kesalahan Validasi:</h5>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('pelayan.order.store') }}" method="POST" id="orderForm">
    @csrf
    <div class="row">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><i class="bi bi-card-list me-2"></i>Pilih Menu</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label for="meja_id" class="form-label fw-bold">Pilih Meja:</label>
                            <select name="meja_id" id="meja_id" class="form-select form-select-lg @error('meja_id') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Nomor Meja --</option>
                                @foreach($mejas as $meja)
                                <option value="{{ $meja->id }}" 
                                        data-kapasitas="{{ $meja->kapasitas }}" 
                                        data-area="{{ $meja->area }}" 
                                        data-status="{{ $meja->status }}" 
                                        {{ old('meja_id') == $meja->id ? 'selected' : '' }}>
                                    {{ $meja->nomor_meja }} (Area: {{ $meja->area }}, Kapasitas: {{ $meja->kapasitas }}) - Status: {{ ucfirst($meja->status) }}
                                </option>
                                @endforeach
                            </select>
                            @error('meja_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-2 mb-md-0">
                            <label for="jumlah_tamu" class="form-label fw-bold">Jumlah Tamu:</label>
                            <input type="number" name="jumlah_tamu" id="jumlah_tamu" class="form-control form-control-lg @error('jumlah_tamu') is-invalid @enderror" value="{{ old('jumlah_tamu', 1) }}" min="1" required placeholder="Cth: 4">
                            @error('jumlah_tamu')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="nama_pelanggan" class="form-label fw-bold">Nama Pelanggan <small class="text-muted">(Opsional)</small>:</label>
                            <input type="text" name="nama_pelanggan" id="nama_pelanggan" class="form-control form-control-lg @error('nama_pelanggan') is-invalid @enderror" value="{{ old('nama_pelanggan') }}" placeholder="Cth: Budi (Walk-in)">
                            @error('nama_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div id="selectedTableInfo" class="mb-3 alert alert-info py-2" style="display: none;">
                        <i class="bi bi-info-circle me-2"></i>
                        Kapasitas meja terpilih (<span id="selectedTableCapacity">0</span>) kurang dari jumlah tamu.
                        Sistem akan otomatis mencari meja tambahan jika tersedia.
                    </div>

                      

                


                    {{-- Search Bar --}}
                    <div class="search-bar input-group input-group-lg mb-3">
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-search"></i></span>
                        <input type="text" id="menuSearch" class="form-control" placeholder="Cari menu...">
                    </div>

                    {{-- Category Navigation --}}
                    @if($categories->isNotEmpty())
                    <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button" role="tab" aria-controls="all-content" aria-selected="true">Semua</button>
                        </li>
                        @foreach($menusByCategory as $category => $menus)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="{{ Str::slug($category) }}-tab" data-bs-toggle="tab" data-bs-target="#{{ Str::slug($category) }}-content" type="button" role="tab" aria-controls="{{ Str::slug($category) }}-content" aria-selected="false">
                                {{ App\Models\Menu::getCategoryOptions()[$category] ?? ucfirst($category) }}
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    {{-- Menu Content per Category --}}
                    <div class="tab-content" id="categoryTabsContent">
                        {{-- All Menu Tab --}}
                        <div class="tab-pane fade show active" id="all-content" role="tabpanel" aria-labelledby="all-tab">
                            <div class="row g-3">
                                @forelse($menusByCategory->flatten() as $menu)
                                <div class="col-sm-6 col-md-4 col-lg-3 menu-item-col" data-category="{{ Str::slug($menu->category) }}" data-name="{{ strtolower($menu->name) }}">
                                    <div class="card menu-item-card h-100 d-flex flex-column">
                                        <img src="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/img/default-food.png') }}" class="card-img-top" alt="{{ $menu->name }}">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title mb-1">{{ $menu->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">{{ Str::limit($menu->description, 30) }}</p>
                                            <div class="mt-auto">
                                                <p class="price mb-1">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                                <button type="button" class="btn btn-primary btn-sm add-to-cart-btn w-100"
                                                        data-id="{{ $menu->id }}"
                                                        data-name="{{ $menu->name }}"
                                                        data-price="{{ $menu->price }}"
                                                        data-image="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/img/default-food.png') }}">
                                                    <i class="bi bi-plus-lg me-1"></i> Tambah
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center">Tidak ada menu tersedia saat ini.</div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Other Category Content --}}
                        @foreach($menusByCategory as $category => $menus)
                        <div class="tab-pane fade" id="{{ Str::slug($category) }}-content" role="tabpanel" aria-labelledby="{{ Str::slug($category) }}-tab">
                            <div class="row g-3">
                                @forelse($menus as $menu)
                                <div class="col-sm-6 col-md-4 col-lg-3 menu-item-col" data-category="{{ Str::slug($category) }}" data-name="{{ strtolower($menu->name) }}">
                                    <div class="card menu-item-card h-100 d-flex flex-column">
                                        <img src="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/img/default-food.png') }}" class="card-img-top" alt="{{ $menu->name }}">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title mb-1">{{ $menu->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">{{ Str::limit($menu->description, 30) }}</p>
                                            <div class="mt-auto">
                                                <p class="price mb-1">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                                <button type="button" class="btn btn-primary btn-sm add-to-cart-btn w-100"
                                                        data-id="{{ $menu->id }}"
                                                        data-name="{{ $menu->name }}"
                                                        data-price="{{ $menu->price }}"
                                                        data-image="{{ $menu->image ? asset('storage/' . $menu->image) : asset('assets/img/default-food.png') }}">
                                                    <i class="bi bi-plus-lg me-1"></i> Tambah
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center">Tidak ada menu dalam kategori ini.</div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>

        {{-- Order Summary Column (Cart) --}}
        <div class="col-lg-4">
            <div class="order-summary-card">
                <h5><i class="bi bi-basket3-fill me-2"></i>Ringkasan Pesanan</h5>
                <div id="cartItems" class="mb-3">
                    {{-- Cart items will be rendered here by JavaScript --}}
                    <p class="text-muted text-center" id="emptyCartMessage">Keranjang kosong. Pilih menu untuk memulai.</p>
                </div>
                <div class="total-section">
                    <div>
                        <span>Total Items:</span>
                        <span id="totalItems">0</span>
                    </div>
                    <div class="grand-total">
                        <span>Total Harga (Estimasi):</span> {{-- Added "Estimasi" --}}
                        <span id="grandTotal">Rp 0</span>
                    </div>
                </div>
                {{-- Submit Order Button --}}
                <button type="submit" class="btn btn-success btn-block-custom w-100 mt-3" id="submitOrderBtn" disabled>
                    <i class="bi bi-check-circle-fill me-2"></i> Proses Pesanan
                </button>
            </div>
            {{-- Hidden inputs for cart items sent to backend --}}
            <div id="hiddenInputs">
                {{-- Hidden inputs for cart items will be generated here by JavaScript --}}
            </div>
        </div>
    </div>
</form>
    {{-- END OF ORDER FORM --}}

    <input type="hidden" id="processPaymentRoute" value="{{ route('pelayan.order.pay', ['reservasi_id' => ':reservasiId']) }}">
    <input type="hidden" id="orderSummaryRoute" value="{{ route('pelayan.order.summary', ['reservasi_id' => ':reservasiId']) }}">


    {{-- Payment Modal --}}
    {{-- Add data-reservasi-id attribute to store the reservation ID after creation --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-reservasi-id="">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Pilih Metode Pembayaran</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                     {{-- Display order summary info in modal header --}}
                    <div class="mb-3 p-2 border rounded">
                         <p class="mb-1">Kode Order: <strong id="modalKodeOrder">N/A</strong></p>
                         {{-- REMOVED hardcoded "Rp " here. JavaScript will add it via formatRupiah --}}
                         <h5 class="mb-0">Total Tagihan: <strong><span id="modalTotalBill">Rp 0</span></strong></h5> {{-- Removed "Rp " --}}
                     </div>

                    {{-- Payment Options Section --}}
                    <div id="paymentOptions">
                        <button type="button" class="btn btn-success btn-block btn-lg mb-3" id="btnCash">
                            <i class="bi bi-wallet2 me-2"></i> Tunai (Cash)
                        </button>
                        <button type="button" class="btn btn-info btn-block btn-lg" id="btnQris">
                             <i class="bi bi-qr-code me-2"></i> Non-Tunai (QRIS/Midtrans)
                        </button>
                    </div>

                    {{-- Cash Payment Form Section --}}
                    <div id="cashPaymentForm" style="display: none;">
                        <div class="form-group mb-3">
                            <label for="uangDiterima" class="form-label">Uang yang Diterima:</label>
                            <input type="number" class="form-control form-control-lg" id="uangDiterima" placeholder="Masukkan nominal uang">
                        </div>
                        <div class="form-group mb-3">
                            <label for="kembalian" class="form-label">Kembalian:</label>
                            <p id="kembalianDisplay" class="form-control-static fs-4 fw-bold text-success">Rp 0</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-block btn-lg mt-3" id="btnBayarCash" disabled>
                             <i class="bi bi-cash me-2"></i> Bayar Tunai
                        </button>
                         <button type="button" class="btn btn-secondary btn-block mt-2" id="btnBackToOptions">Kembali</button>
                    </div>

                    {{-- QRIS Payment Info Section --}}
                     <div id="qrisPaymentInfo" style="display: none;">
                         <p class="text-center py-3"><i class="bi bi-info-circle me-2"></i> Anda memilih pembayaran Non-Tunai.</p>
                         <p class="text-center text-muted small">Silakan lanjutkan proses di terminal pembayaran atau scan QR code (jika tersedia). Klik konfirmasi setelah pembayaran berhasil dilakukan.</p>
                         <button type="button" class="btn btn-primary btn-block btn-lg mt-3" id="btnConfirmQris">
                             <i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai
                        </button>
                        <button type="button" class="btn btn-secondary btn-block mt-2" id="btnBackToOptionsQris">Kembali</button>
                     </div>

                    {{-- Loading Indicator --}}
                    <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memproses...</p>
                    </div>

                     {{-- Payment Status Messages --}}
                     <div id="paymentSuccessMessage" class="alert alert-success mt-3" style="display: none;">
                         Pembayaran Berhasil!
                     </div>
                     <div id="paymentErrorMessage" class="alert alert-danger mt-3" style="display: none;">
                         Gagal memproses pembayaran.
                     </div>

                     {{-- Payment Success Actions (Hidden initially) --}}
                     <div id="paymentSuccessActions" style="display: none;">
                         <button type="button" class="btn btn-secondary" id="btnBackToDashboard">Kembali ke Dashboard</button>
                         <button type="button" class="btn btn-primary" id="btnViewSummary">Lihat Ringkasan Pesanan</button>
                     </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to show table combination notification
    function showTableCombinationNotification(tables) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show';
        notification.innerHTML = `
            <strong><i class="bi bi-info-circle me-2"></i>Meja Digabungkan:</strong>
            <ul class="mb-1">
                ${tables.map(t => `<li>Meja ${t.nomor_meja} (Kapasitas: ${t.kapasitas})</li>`).join('')}
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.content-wrapper').prepend(notification);
    }

    // Handle table selection and guest count validation
    document.getElementById('jumlah_tamu').addEventListener('change', function() {
        const jumlahTamu = parseInt(this.value);
        const mejaSelect = document.getElementById('meja_id');
        const selectedOption = mejaSelect.options[mejaSelect.selectedIndex];
        const infoDiv = document.getElementById('selectedTableInfo');
        
        if (selectedOption && selectedOption.value) {
            const tableCapacity = parseInt(selectedOption.dataset.kapasitas);
            
            if (jumlahTamu > tableCapacity) {
                document.getElementById('selectedTableCapacity').textContent = tableCapacity;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }
    });

    // Handle form submission
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const items = [];
        
        // Collect order items
        document.querySelectorAll('.cart-item').forEach(item => {
            items.push({
                menu_id: item.dataset.menuId,
                quantity: parseInt(item.querySelector('.quantity-input').value),
                notes: item.querySelector('.item-notes-input')?.value || null
            });
        });
        
        formData.delete('items');
        formData.append('items', JSON.stringify(items));
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If tables were combined
                if (data.combined_tables && data.combined_tables.length > 1) {
                    // Get table info from select options
                    const tablesInfo = data.combined_tables.map(id => {
                        const option = document.querySelector(`#meja_id option[value="${id}"]`);
                        return {
                            nomor_meja: option.text.split('(')[0].trim(),
                            kapasitas: parseInt(option.dataset.kapasitas)
                        };
                    });
                    
                    showTableCombinationNotification(tablesInfo);
                }
                
                // Proceed to payment modal
                const paymentModal = document.getElementById('paymentModal');
                paymentModal.dataset.reservasiId = data.reservasi_id;
                document.getElementById('modalKodeOrder').textContent = data.kode_reservasi;
                document.getElementById('modalTotalBill').textContent = 'Rp ' + formatRupiah(data.total_bill.toString());
                
                const modal = new bootstrap.Modal(paymentModal);
                modal.show();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses pesanan');
        });
    });

    // Format Rupiah helper function
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function renderHiddenInputs(cartItems) {
    const container = document.getElementById('hiddenInputs');
    container.innerHTML = ''; // kosongkan terlebih dahulu
    cartItems.forEach((item, index) => {
        const menuInput = document.createElement('input');
        menuInput.type = 'hidden';
        menuInput.name = `items[${index}][menu_id]`;
        menuInput.value = item.id;
        container.appendChild(menuInput);

        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = `items[${index}][quantity]`;
        qtyInput.value = item.quantity;
        container.appendChild(qtyInput);
    });

});
</script>
@endpush

@push('scripts')
{{--
    |--------------------------------------------------------------------------
    | PENTING: Hapus pemuatan skrip Midtrans Snap dari sini!
    |--------------------------------------------------------------------------
    | Skrip Midtrans Snap seharusnya hanya dimuat sekali di file layout utama.
    | Memuatnya di sini menyebabkan konflik dan error.
    | Pemuatan sudah ada di layout/app.blade.php.
    --}}
{{-- Hapus baris berikut:
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
--}}

{{-- The main app.js file loaded by @vite will import other refactored JS files --}}
{{-- Make sure your layout file includes @vite(['resources/js/app.js']) --}}
@endpush
