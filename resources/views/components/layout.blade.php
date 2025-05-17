<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ $title ?? 'Dashboard' }} | Sistem Restoran</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

    <div class="min-h-full">
       

        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="w-64 bg-blue-800 text-white p-4">
                <h1 class="text-2xl font-bold mb-6">Admin Restoran</h1>
                <x-navbar />
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-8 overflow-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold">{{ $title ?? '' }}</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ auth()->user()->nama }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Slot content dari halaman -->
                {{ $slot }}
            </div>
        </div>
    </div>

</body>

</html>