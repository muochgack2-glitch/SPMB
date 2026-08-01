@props(['title' => 'QR Scanner'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Absensi QR Code - SMK PGRI BLORA">
    <meta name="theme-color" content="#1e3a8a">

    <title>{{ config('app.name', 'Absensi QR') }} - {{ $title }}</title>

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
    
    {{-- Main Content (No Header) --}}
    <main class="pt-4 pb-4 px-4 min-h-screen">
        <div class="max-w-screen-2xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>
</html>
