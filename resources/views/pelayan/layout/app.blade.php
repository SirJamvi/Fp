<!DOCTYPE html>
<html lang="id">
<head>
  <style>
    /* === SWITCH STYLING === */
    .switch { position: relative; display: inline-block; width: 40px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #28a745; }
    input:checked + .slider:before { transform: translateX(18px); }

    /* === CONTENT WRAPPER BACKGROUND & OVERLAY === */
    .content-wrapper {
      position: relative;
      background: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1740&q=80')
                  no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      /* overflow: hidden; */
    }
    .content-wrapper::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(255, 255, 255, 0.75);
      pointer-events: none;
      z-index: 0;
    }
    .content-wrapper > * {
      position: relative;
      z-index: 1;
    }

    /* === MODAL Z-INDEX OVERRIDE === */
    .modal-backdrop {
      z-index: 1040 !important;
    }
    .modal {
      z-index: 1050 !important;
    }
  </style>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pelayan - @yield('title', 'Dashboard') | Sistem Restoran</title>

  {{-- Bootstrap via Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- jQuery & Bootstrap Bundle --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-gray-100">
  <div class="min-h-full">
    <div class="flex h-screen">
      {{-- Sidebar --}}
      <div class="w-64 bg-teal-700 text-white p-4">
        <h1 class="text-2xl font-bold">Sistem Pelayan</h1>
        <p class="text-sm mt-2">{{ auth()->user()->nama }}</p>
        @include('pelayan.partials.navbar')
      </div>

      {{-- Konten Utama --}}
      <div class="flex-1 overflow-auto">
        <div class="content-wrapper p-8">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">@yield('title', 'Dashboard Pelayan')</h2>
          </div>
          @yield('content')
        </div>
      </div>
    </div>
  </div>

  {{-- Midtrans Snap JS --}}
  <script src="https://app.sandbox.midtrans.com/snap/snap.js"
          data-client-key="{{ config('services.midtrans.client_key') }}"></script>

  @stack('scripts')
</body>
</html>
