@php
    $pageTitle = 'Dashboard Absensi';
@endphp

<x-app-layout>
    {{-- Dashboard Component --}}
    @livewire('attendance-dashboard')

    @push('scripts')
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
    @endpush
</x-app-layout>
