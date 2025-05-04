<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservasi Restoran Digital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
    }
    .hero {
      background: url('https://source.unsplash.com/1600x600/?restaurant,dining') center/cover no-repeat;
      color: white;
      padding: 100px 0;
      text-align: center;
    }
    .feature-icon {
      font-size: 48px;
      color: #f4623a;
    }
    .footer {
      background-color: #222;
      color: #bbb;
      padding: 40px 0;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#">RestoOnline</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#fitur">Fitur</a></li>
          <li class="nav-item"><a class="nav-link" href="#daftar">Daftar</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="container">
      <h1 class="display-4 fw-bold">Reservasi & Pemesanan Restoran Digital</h1>
      <p class="lead">Pesan meja, pilih menu, dan nikmati kemudahan layanan restoran modern.</p>
      <a href="#daftar" class="btn btn-lg btn-warning mt-3">Mulai Sekarang</a>
    </div>
  </section>

  <!-- Fitur -->
  <section id="fitur" class="py-5">
    <div class="container text-center">
      <h2 class="mb-5">Fitur Unggulan</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-icon mb-3">📅</div>
          <h5>Reservasi Meja</h5>
          <p>Pilih waktu dan meja dengan mudah langsung dari aplikasi.</p>
        </div>
        <div class="col-md-4">
          <div class="feature-icon mb-3">🍽️</div>
          <h5>Pemesanan Menu</h5>
          <p>Telusuri katalog digital dan pesan menu favorit Anda.</p>
        </div>
        <div class="col-md-4">
          <div class="feature-icon mb-3">📱</div>
          <h5>Notifikasi Otomatis</h5>
          <p>Dapatkan update saat reservasi dikonfirmasi atau makanan siap disajikan.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section id="daftar" class="bg-light py-5">
    <div class="container text-center">
      <h2 class="mb-3">Gabung Sekarang</h2>
      <p class="mb-4">Bergabunglah dengan ratusan pelanggan yang sudah menikmati pengalaman reservasi digital terbaik.</p>
      <a href="/register" class="btn btn-primary btn-lg">Daftar Akun</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer text-center">
    <div class="container">
      <p>&copy; {{ date('Y') }} RestoOnline. All rights reserved.</p>
      <p>Email: support@restoonline.com</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
