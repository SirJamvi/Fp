<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Restoran</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <!-- Bagian Kiri -->
        <div class="login-hero">
            <div class="hero-overlay">
                <div class="hero-text">
                    <h1 class="text-4xl font-bold mb-4">Restoran Lezat</h1>
                    <p class="text-xl mb-6">Sistem Manajemen Restoran Modern</p>
                    <p class="text-lg">
                        "Selamat datang di sistem manajemen restoran kami. Masuk untuk mengakses dashboard admin dan kelola operasional restoran Anda."
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            <span>Manajemen Menu</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            <span>Manajemen Meja</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            <span>Laporan Keuangan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian Kanan (Form Login) -->
        <div class="login-form-container">
            <!-- Ikon Dekoratif -->
            <div class="form-icons-bg" aria-hidden="true">
                <i class="fas fa-utensils icon icon-1 icon-blur"></i>
                <i class="fas fa-concierge-bell icon icon-2 icon-blur"></i>
                <i class="fas fa-mug-hot icon icon-3 icon-blur"></i>
                <i class="fas fa-pizza-slice icon icon-4 icon-blur"></i>
                <i class="fas fa-hamburger icon icon-5 icon-blur"></i>
                <i class="fas fa-ice-cream icon icon-6 icon-blur"></i>
                <i class="fas fa-wine-glass-alt icon icon-7 icon-blur"></i>
                <i class="fas fa-egg icon icon-8 icon-blur"></i>
            </div>

            <div class="text-center mb-8 relative z-10">
                <h1 class="text-3xl font-bold text-orange">Selamat Datang</h1>
                <p class="text-gray-600">Silakan login ke akun admin Anda</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 text-red-600 rounded-lg relative z-10">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6 relative z-10">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" required autofocus
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange focus:border-orange focus-orange"
                           placeholder="email@contoh.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange focus:border-orange focus-orange pr-10"
                               placeholder="••••••••">
                        <i class="fas fa-eye-slash password-toggle" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" 
                               class="h-4 w-4 checkbox-orange focus:ring-orange border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                            Ingat saya
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium link-orange hover:text-orange">
                            Lupa password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-orange hover:bg-orange-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange">
                        Login
                    </button>
                </div>
            </form>
        </div>  
    </div>

    <!-- Toggle Password Script -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
