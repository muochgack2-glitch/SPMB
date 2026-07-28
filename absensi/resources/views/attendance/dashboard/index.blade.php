<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Absensi - Sistem Absensi Siswa</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Livewire Styles --}}
    @livewireStyles
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    {{-- Navigation --}}
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold">📚 Sistem Absensi QR Code</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('attendance.dashboard') }}" class="bg-blue-800 px-3 py-2 rounded">Dashboard</a>
                    <a href="{{ route('attendance.scanner') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Scanner</a>
                    <a href="{{ route('attendance.students.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Siswa</a>
                    <a href="{{ route('attendance.classes.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Kelas</a>
                    <a href="{{ route('attendance.reports.daily') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Laporan</a>
                    <a href="{{ route('attendance.settings.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Pengaturan</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="bg-white shadow-sm border-b border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-800">Dashboard Absensi</h2>
                <span class="text-sm text-gray-500">Monitor kehadiran siswa real-time</span>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="container mx-auto px-4 py-8">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <p>{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <p>{{ session('error') }}</p>
            </div>
        </div>
        @endif

        {{-- Dashboard Component --}}
        @livewire('attendance-dashboard')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; {{ date('Y') }} {{ \App\Models\AttendanceSetting::get('school_name', 'SMK Negeri 1') }} - Sistem Absensi Siswa</p>
                <p class="mt-1">Dikembangkan dengan ❤️ untuk pendidikan Indonesia</p>
            </div>
        </div>
    </footer>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Auto-refresh toast notification --}}
    <script>
        // Show toast when data refreshes
        window.addEventListener('DOMContentLoaded', function() {
            setInterval(function() {
                // This will be triggered by wire:poll.30s
                console.log('Dashboard auto-refreshing...');
            }, 30000);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt+S = Open Scanner
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                window.open('{{ route("attendance.scanner") }}', '_blank');
            }

            // Alt+R = Refresh Dashboard
            if (e.altKey && e.key === 'r') {
                e.preventDefault();
                Livewire.dispatch('refresh-dashboard');
            }
        });
    </script>
</body>
</html>
