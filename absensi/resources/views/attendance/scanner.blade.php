<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Scanner - Sistem Absensi</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Livewire Styles --}}
    @livewireStyles
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .qr-scanner-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        #qr-video {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
        }
        
        .scan-frame {
            position: relative;
            border-style: dashed;
        }
        
        @keyframes scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }
        
        .scanning-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4ade80, transparent);
            animation: scanline 2s linear infinite;
        }
        
        .result-card {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8">
        {{-- Livewire Component --}}
        @livewire('q-r-scanner-interface')
        
        {{-- Footer --}}
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p>Sistem Absensi Siswa - QR Code Scanner</p>
            <p class="mt-1">Dikembangkan untuk SMK</p>
        </div>
    </div>
    
    {{-- Livewire Scripts --}}
    @livewireScripts
    
    {{-- Alpine.js for transitions --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('scripts')
</body>
</html>
