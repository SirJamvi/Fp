<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Admin Panel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    {{-- Navbar Komponen --}}
    <x-navbar />

    <main class="p-4">
        @yield('content')
    </main>
</body>
</html>