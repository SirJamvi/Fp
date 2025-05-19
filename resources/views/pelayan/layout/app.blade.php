<!DOCTYPE html>
<html lang="id">
<head>

    <style>
/* Custom switch (CSS ini ada di layout file, tidak terkait langsung dgn modal) */
.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 22px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #28a745;
}

input:checked + .slider:before {
  transform: translateX(18px);
}
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayan - @yield('title', 'Dashboard') | Sistem Restoran</title>

    {{--
        ====================================================================
        PENTING: Pastikan Bootstrap CSS dan JavaScript diimpor di sini via Vite.
        1. Pastikan Anda sudah install Bootstrap: npm install bootstrap
        2. Di file resources/css/app.css (atau app.scss), tambahkan:
           @import "~bootstrap"; // atau path import Bootstrap lainnya
        3. Di file resources/js/app.js, tambahkan:
           import 'bootstrap'; // atau import 'bootstrap/dist/js/bootstrap.bundle';
        4. Jalankan npm run dev atau npm run build.
        ====================================================================
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles') {{-- Untuk CSS tambahan dari child view seperti dashboard.blade.php --}}

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

     {{--
        ====================================================================
        PENTING: Jika script kustom di @stack('scripts') memerlukan jQuery
        sebelum script dari @vite dieksekusi, pastikan jQuery dimuat di sini.
        Anda sudah memuatnya dari CDN, yang OK.
        Jika app.js Anda juga mengimpor jQuery, pastikan tidak ada konflik.
        ====================================================================
     --}}
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body class="bg-gray-100">
    <div class="min-h-full">
        <div class="flex h-screen">
            <div class="w-64 bg-teal-700 text-white p-4">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Sistem Pelayan</h1>
                    <p class="text-sm mt-2">{{ auth()->user()->nama }}</p>
                </div>
                @include('pelayan.partials.navbar')
            </div>

            <div class="flex-1 p-8 overflow-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold">@yield('title', 'Dashboard Pelayan')</h2>

                </div>

                @yield('content')
            </div>
        </div>
    </div>

    {{-- Load Midtrans Snap script - DImuat HANYA DI SINI --}}
    {{-- Use your actual Client Key --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @stack('scripts') {{-- Script dari child view seperti dashboard.blade.php akan dimuat di sini --}}
</body>
</html>