<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayan - @yield('title', 'Dashboard') | Sistem Restoran</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tambahan: CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-full">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="w-64 bg-teal-700 text-white p-4">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Sistem Pelayan</h1>
                    <p class="text-sm mt-2">{{ auth()->user()->nama }}</p>
                </div>
                @include('pelayan.partials.navbar')
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-8 overflow-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold">@yield('title', 'Dashboard Pelayan')</h2>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-100 hover:text-white flex items-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
