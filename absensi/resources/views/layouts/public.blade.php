<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Absensi QR Code - SMK PGRI BLORA">
    <meta name="theme-color" content="#1e3a8a">

    <title>{{ config('app.name', 'Absensi QR') }} - QR Scanner</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional Styles -->
    @stack('styles')
    
    <!-- Initialize Dark Mode Early -->
    <script>
        (function() {
            const darkMode = localStorage.getItem('darkMode') === 'true';
            if (darkMode) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    
    {{-- Fixed Header dengan Login Button --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-screen-2xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                {{-- Logo & School Name --}}
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-purple-500 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-gray-900 dark:text-white">SMK PGRI BLORA</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Sistem Absensi QR Code</p>
                    </div>
                </div>

                {{-- Login Button --}}
                <a href="{{ route('login') }}" class="group relative px-6 py-3 bg-gradient-to-r from-primary-500 to-purple-600 hover:from-primary-600 hover:to-purple-700 text-white rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-2xl">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login Admin
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="pt-24 pb-8 px-6 min-h-screen">
        <div class="max-w-screen-2xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4">
        <div class="max-w-screen-2xl mx-auto px-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                © {{ date('Y') }} SMK PGRI BLORA. Sistem Absensi QR Code.
            </p>
        </div>
    </footer>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>
</html>
