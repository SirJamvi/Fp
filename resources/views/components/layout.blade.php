<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} | Sistem Restoran</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
      /* === CONTENT WRAPPER BACKGROUND & OVERLAY === */
      .content-wrapper {
        position: relative;
        background: url('https://images.unsplash.com/photo-1593642532744-d377ab507dc8?auto=format&fit=crop&w=1740&q=80')
                    no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
        overflow: hidden;
        padding: 2rem;
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

      /* === SIDEBAR STYLING === */
      .sidebar {
        background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .sidebar a {
        color: #fff;
        transition: all 0.2s ease;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin: 0.25rem 0;
      }
      .sidebar a:hover {
        background-color: rgba(255, 255, 255, 0.15);
        transform: translateX(5px);
      }
      .sidebar a.active {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: 600;
      }
      .sidebar .nav-title {
        color: #ccfbf1;
        font-weight: 500;
        letter-spacing: 0.5px;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        padding-left: 1rem;
      }

      /* === HEADER & LOGOUT BUTTON === */
      .header-title {
        color: #0f766e;
        font-weight: 600;
      }
      .btn-logout {
        color: #dc2626;
        transition: all 0.2s;
      }
      .btn-logout:hover {
        color: #991b1b;
        transform: translateY(-2px);
      }
      
      /* === ADMIN THEME ELEMENTS === */
      .admin-icon {
        background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      
      /* === GLASSMORPHISM === */
      .glass-card {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-radius: 1rem !important;
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
      }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-full flex">
        {{-- Sidebar --}}
        <div class="w-64 sidebar text-white p-4">
            <div class="flex items-center mb-6">
                <div class="admin-icon">
                    <i class="bi bi-laptop text-white"></i>
                </div>
                <h1 class="text-xl font-bold">Admin Dashboard</h1>
            </div>
            <x-navbar />
        </div>

        {{-- Main Content --}}
        <div class="flex-1 overflow-auto">
            <div class="content-wrapper">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold header-title flex items-center">
                        <i class="bi bi-speedometer2 me-2"></i>
                        {{ $title ?? 'Dashboard' }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700 font-medium">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ auth()->user()->nama }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-logout hover:text-red-700 flex items-center">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Konten halaman --}}
                {{ $slot }}
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>