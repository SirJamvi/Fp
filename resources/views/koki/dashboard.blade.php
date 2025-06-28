<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Dashboard Koki' }}</title>

  {{-- Vite CSS & JS --}}
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/koki_dashboard/main.js'])

  {{-- Bootstrap & FontAwesome --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    /* Background image + lebih tipis overlay */
    body {
      background: url('https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=1740&q=80')
                  no-repeat center center fixed;
      background-size: cover;
      position: relative;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #004d40;
    }
    body::before {
      content: '';
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(224, 242, 241, 0.6); /* overlay lebih tipis */
      z-index: -1;
    }

    /* Card dengan efek glassmorphism lebih kontras */
    .card {
      background-color: rgba(255, 255, 255, 0.85); /* sedikit lebih padat */
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }

    /* Header, body, footer */
    .card-header {
      background-color: #00796b;
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem 0.75rem 0 0;
      border-bottom: none;
    }
    .card-body { padding: 1.5rem; }
    .card-footer {
      background-color: transparent;
      border-top: none;
      padding: 0.75rem 1.5rem;
    }

    /* Tombol */
    .btn-sm {
      padding: 0.4rem 0.75rem;
      font-size: 0.75rem;
      border-radius: 0.3rem;
    }
    .btn-danger.btn-sm {
      background-color: #d32f2f;
      color: #fff;
      border: none;
    }
    .btn-danger.btn-sm:hover {
      background-color: #b71c1c;
    }

    /* Statistik cards */
    .card.bg-danger { background-color: #d32f2f !important; color: #fff; }
    .card.bg-warning { background-color: #fbc02d !important; color: #212121; }
    .card.bg-success { background-color: #388e3c !important; color: #fff; }
    .text-white-75 { opacity: 0.75; }
    .text-lg { font-size: 1.8rem; font-weight: bold; }

    /* Tabel */
    .table thead th {
      font-size: 0.75rem;
      text-transform: uppercase;
      color: #00796b;
      border-bottom: 2px solid #b2dfdb;
    }
    .table tbody td {
      font-size: 0.9rem;
      color: #004d40;
    }

    /* Misc */
    .menu-image {
      width: 36px;
      height: 36px;
      object-fit: cover;
      border-radius: 50%;
      margin-right: 10px;
      border: 1px solid #b2dfdb;
    }
    .order-notes {
      font-size: 0.8rem;
      color: #616161;
      margin-top: 5px;
      font-style: italic;
    }
    .spinner-border {
      width: 2rem;
      height: 2rem;
    }
    .stretched-link::after {
      content: '';
      position: absolute;
      inset: 0;
    }
    a.small.text-white:hover {
      opacity: 0.8;
    }

    @media (max-width: 768px) {
      .card-header h6 { font-size: 1rem; }
      .btn-sm { font-size: 0.7rem; }
      .text-lg { font-size: 1.4rem; }
    }
  </style>
</head>
<body>

  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Dashboard Koki</h6>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
              </button>
            </form>
          </div>
          <div class="card-body">
            <div class="row g-4">
              <div class="col-xl-4 col-md-6">
                <div class="card bg-danger text-white h-100">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-white-75 small">Pesanan Baru</div>
                      <div class="text-lg" id="newOrdersCount">0</div>
                    </div>
                    <i class="fas fa-utensils fa-2x text-white-50"></i>
                  </div>
                </div>
              </div>
              <div class="col-xl-4 col-md-6">
                <div class="card bg-warning text-white h-100">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-white-75 small">Sedang Diproses</div>
                      <div class="text-lg" id="preparingOrdersCount">0</div>
                    </div>
                    <i class="fas fa-clock fa-2x text-white-50"></i>
                  </div>
                </div>
              </div>
              <div class="col-xl-4 col-md-6">
                <div class="card bg-success text-white h-100">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-white-75 small">Pesanan Selesai Hari Ini</div>
                      <div class="text-lg" id="completedOrdersCount">0</div>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-white-50"></i>
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
                    <table class="table table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th>No. Reservasi</th>
                          <th>Meja</th>
                          <th>Menu &amp; Catatan</th>
                          <th>Jumlah</th>
                          <th>Waktu Pesan</th>
                          <th>Status</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody id="ordersTableBody">
                        <tr>
                          <td colspan="7" class="text-center py-4">
                            <div class="spinner-border text-secondary" role="status">
                              <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2 text-secondary">Memuat pesanan...</div>
                          </td>
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

  {{-- Bootstrap JS & jQuery --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  {{-- API routes --}}
  <input type="hidden" id="getOrdersRoute" value="{{ route('koki.orders.get') }}">
  <input type="hidden" id="updateOrderStatusBaseRoute" value="{{ url('koki/orders') }}">
</body>
</html>
    