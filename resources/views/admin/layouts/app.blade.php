<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} | Sistem Restoran</title> {{-- Menggunakan $title dari x-slot --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-full flex">
        <div class="w-64 bg-blue-800 text-white p-4">
            <h1 class="text-2xl font-bold mb-6">Admin Restoran</h1>
            <x-navbar />
        </div>

        <div class="flex-1 p-8 overflow-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">{{ $title ?? 'Dashboard' }}</h2> {{-- Menggunakan $title --}}
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->nama }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-700 flex items-center">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            {{ $slot }} {{-- Ini adalah tempat konten dari view yang menggunakan x-layout --}}
        </div>
    </div>

    @stack('scripts') {{-- <<< Tambahkan ini untuk JavaScript --}}
</body>
</html>