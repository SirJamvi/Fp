<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Tambahkan CSRF token --}}
    <title>{{ $title ?? 'Dashboard Koki' }}</title>
    
    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/koki_dashboard/main.js'])

    {{-- Bootstrap & FontAwesome (Pastikan ini tidak duplikat dengan yang diimport via Vite jika sudah) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #e9ecef;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
            display: flex; /* Untuk menata judul dan tombol logout */
            justify-content: space-between; /* Untuk menata judul dan tombol logout */
            align-items: center; /* Untuk menata judul dan tombol logout */
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }
        .table tbody td {
            vertical-align: middle;
            font-size: 0.875rem;
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.4em 0.6em;
            border-radius: 0.35rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.2rem;
        }
        .order-notes {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
            font-style: italic;
        }
        /* Style tambahan untuk gambar menu */
        .menu-image {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 8px;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Dashboard Koki</h6>
                    {{-- Tombol Logout --}}
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Pesanan Baru</div>
                                            <div class="text-lg fw-bold" id="newOrdersCount">0</div>
                                        </div>
                                        <i class="fas fa-utensils fa-2x text-white-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="#">Lihat Detail</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Sedang Diproses</div>
                                            <div class="text-lg fw-bold" id="preparingOrdersCount">0</div>
                                        </div>
                                        <i class="fas fa-clock fa-2x text-white-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="#">Lihat Detail</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Pesanan Selesai Hari Ini</div>
                                            <div class="text-lg fw-bold" id="completedOrdersCount">0</div>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="#">Lihat Detail</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Daftar Pesanan Dapur</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Reservasi</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Meja</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Menu & Catatan</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu Pesan</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                <th class="text-secondary opacity-7">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ordersTableBody">
                                            {{-- Reservations will be loaded here by JavaScript --}}
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Memuat pesanan...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Memuat Bootstrap JS, jQuery (jika masih diperlukan) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

{{-- Hidden inputs for API routes --}}
<input type="hidden" id="getOrdersRoute" value="{{ route('koki.orders.get') }}">
<input type="hidden" id="updateOrderStatusBaseRoute" value="{{ url('koki/orders') }}"> 

</body>
</html>