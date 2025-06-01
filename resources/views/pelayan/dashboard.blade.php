@extends('pelayan.layout.app')

@section('title', $title ?? 'Dashboard Pelayan')

@push('styles')
{{-- Custom styles for the dashboard --}}
<style>
    /* Styling untuk harga diskon */
    .original-price {
        text-decoration: line-through;
        color: #888;
        font-size: 0.8em;
        margin-right: 5px;
    }
    .discounted-price {
        color: #dc3545; /* Merah untuk harga diskon */
        font-weight: bold;
    }
    .price-display {
        display: flex;
        align-items: baseline;
        justify-content: flex-end; /* Memastikan harga rata kanan */
        font-size: 1.1em;
        margin-bottom: 0.5rem;
    }
    .menu-item-card .card-body .mt-auto {
        display: flex;
        flex-direction: column;
        align-items: flex-end; /* Rata kanan pada elemen dalam mt-auto */
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
                    <div class="mb-3">
                    <label for="area" class="form-label">Pilih Area</label>
                    <select id="area" class="form-select" name="area">
                        <option value="">-- Pilih Area --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="meja" class="form-label">Pilih Meja</label>
                    <select id="meja" class="form-select" name="meja_id" disabled>
                        <option value="">-- Pilih Meja --</option>
                        {{-- Meja akan dimuat via AJAX --}}
                    </select>
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
                    @if(!empty($categories))
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
                                        <img src="{{ $menu->image_url }}" class="card-img-top" alt="{{ $menu->name }}">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title mb-1">{{ $menu->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">{{ Str::limit($menu->description, 30) }}</p>
                                            <div class="mt-auto">
                                                <div class="price-display">
                                                    @if ($menu->discount_percentage > 0)
                                                        <span class="original-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                                        <span class="discounted-price">Rp {{ number_format($menu->final_price, 0, ',', '.') }}</span>
                                                    @else
                                                        <p class="price mb-0">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-primary btn-sm add-to-cart-btn w-100"
                                                        data-id="{{ $menu->id }}"
                                                        data-name="{{ $menu->name }}"
                                                        data-price="{{ $menu->final_price }}" {{-- Menggunakan final_price --}}
                                                        data-image="{{ $menu->image_url }}"> {{-- Pastikan ini ada --}}
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
                                        <img src="{{ $menu->image_url }}" class="card-img-top" alt="{{ $menu->name }}">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title mb-1">{{ $menu->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">{{ Str::limit($menu->description, 30) }}</p>
                                            <div class="mt-auto">
                                                <div class="price-display">
                                                    @if ($menu->discount_percentage > 0)
                                                        <span class="original-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                                        <span class="discounted-price">Rp {{ number_format($menu->final_price, 0, ',', '.') }}</span>
                                                    @else
                                                        <p class="price mb-0">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-primary btn-sm add-to-cart-btn w-100"
                                                        data-id="{{ $menu->id }}"
                                                        data-name="{{ $menu->name }}"
                                                        data-price="{{ $menu->final_price }}" {{-- Menggunakan final_price --}}
                                                        data-image="{{ $menu->image_url }}"> {{-- Pastikan ini ada --}}
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
                        <span>Total Harga (Estimasi):</span>
                        <span id="grandTotal">Rp 0</span>
                    </div>
                </div>
                {{-- Submit Order Button --}}
                <button type="submit" id="submitOrderBtn" class="btn btn-secondary w-100" disabled>Proses Pesanan</button>
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
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-reservasi-id="">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Pilih Metode Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-2 border rounded">
                        <p class="mb-1">Kode Order: <strong id="modalKodeOrder">N/A</strong></p>
                        <h5 class="mb-0">Total Tagihan: <strong><span id="modalTotalBill">Rp 0</span></strong></h5>
                    </div>

                    <div id="paymentOptions">
                        <button type="button" class="btn btn-success btn-block btn-lg mb-3" id="btnCash">
                            <i class="bi bi-wallet2 me-2"></i> Tunai (Cash)
                        </button>
                        <button type="button" class="btn btn-info btn-block btn-lg" id="btnQris">
                            <i class="bi bi-qr-code me-2"></i> Non-Tunai (QRIS/Midtrans)
                        </button>
                    </div>

                    <div id="cashPaymentForm" style="display: none;">
                        <div class="form-group mb-3">
                            <label for="uangDiterima" class="form-label">Masukan Uang Di terima:</label>
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

                    <div id="qrisPaymentInfo" style="display: none;">
                        <p class="text-center py-3"><i class="bi bi-info-circle me-2"></i> Anda memilih pembayaran Non-Tunai.</p>
                        <p class="text-center text-muted small">Silakan lanjutkan proses di terminal pembayaran atau scan QR code (jika tersedia). Klik konfirmasi setelah pembayaran berhasil dilakukan.</p>
                        <button type="button" class="btn btn-primary btn-block btn-lg mt-3" id="btnConfirmQris">
                            <i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Non-Tunai
                        </button>
                        <button type="button" class="btn btn-secondary btn-block mt-2" id="btnBackToOptionsQris">Kembali</button>
                    </div>

                    <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memproses...</p>
                    </div>

                    <div id="paymentSuccessMessage" class="alert alert-success mt-3" style="display: none;">
                        Pembayaran Berhasil!
                    </div>
                    <div id="paymentErrorMessage" class="alert alert-danger mt-3" style="display: none;">
                        Gagal memproses pembayaran.
                    </div>

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
{{-- Hapus script ini, karena sudah dihandle oleh main.js --}}
{{-- <script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi elemen dengan ID yang benar
    const elements = {
        orderForm: document.getElementById('orderForm'),
        submitOrderBtn: document.getElementById('submitOrderBtn'),
        mejaSelect: document.getElementById('meja'),
        jumlahTamuInput: document.getElementById('jumlah_tamu'),
        loadingIndicator: document.getElementById('loadingIndicator'),
        paymentSuccessMessage: document.getElementById('paymentSuccessMessage'),
        paymentErrorMessage: document.getElementById('paymentErrorMessage'),
        btnBayarCash: document.getElementById('btnBayarCash'),
        btnConfirmQris: document.getElementById('btnConfirmQris'),
        btnBackToOptions: document.getElementById('btnBackToOptions'),
        btnBackToOptionsQris: document.getElementById('btnBackToOptionsQris'),
        uangDiterimaInput: document.getElementById('uangDiterima'),
        processPaymentRouteInput: document.getElementById('processPaymentRoute'),
        orderSummaryRouteInput: document.getElementById('orderSummaryRoute'),
        paymentModalEl: document.getElementById('paymentModal'),
        areaSelect: document.getElementById('area')

        
    };

    // Fungsi untuk menangani perubahan area
    function handleAreaChange(selectedArea) {
        const mejaSelect = elements.mejaSelect;
        mejaSelect.innerHTML = '<option value="">-- Pilih Meja --</option>';
        mejaSelect.disabled = true;

        if (selectedArea) {
            fetch(`/pelayan/get-meja-by-area/${encodeURIComponent(selectedArea)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
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
                    }
                })
                .catch(error => {
                    console.error('Error fetching meja:', error);
                    mejaSelect.innerHTML = '<option value="">-- Gagal memuat meja --</option>';
                });
        }
    }

    // Event listener untuk perubahan area
    if (elements.areaSelect) {
        elements.areaSelect.addEventListener('change', function() {
            handleAreaChange(this.value);
        });
    }

    // Fungsi untuk mengecek status tombol submit
    function checkSubmitButtonStatus() {
        if (!elements.submitOrderBtn || !elements.mejaSelect || !elements.jumlahTamuInput) return;

        // Asumsi ada fungsi getCartItems() yang mengembalikan isi keranjang
        const isCartEmpty = true; // Ganti dengan logika yang sesuai
        const isTableSelected = elements.mejaSelect.value !== "";
        const tamuValid = parseInt(elements.jumlahTamuInput.value, 10) >= 1;

        elements.submitOrderBtn.disabled = isCartEmpty || !isTableSelected || !tamuValid;
    }

    // Event listener untuk perubahan meja dan jumlah tamu
    if (elements.mejaSelect) {
        elements.mejaSelect.addEventListener('change', checkSubmitButtonStatus);
    }
    if (elements.jumlahTamuInput) {
        elements.jumlahTamuInput.addEventListener('input', checkSubmitButtonStatus);
    }
});
</script> --}}
@endpush
 

@push('scripts')
{{-- Pastikan hanya main.js yang di-load, dan main.js akan mengimpor modul lainnya --}}
<script src="{{ asset('js/pelayan_dashboard/main.js') }}" type="module"></script>
{{-- <script src="{{ asset('js/payment_modal.js') }}"></script> --}}
{{-- <script src="{{ asset('js/pelayan_dashboard/form_submit.js') }}"></script> --}}

  <script
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('services.midtrans.client_key') }}">
  </script>
  
<script src="{{ asset('js/pelayan_dashboard/main.js') }}" type="module"></script>
@endpush


