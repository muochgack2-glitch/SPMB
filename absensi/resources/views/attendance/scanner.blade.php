@php
    $pageTitle = 'QR Scanner';
    $breadcrumbs = [
        ['label' => 'QR Scanner']
    ];
@endphp

<x-app-layout>
    {{-- Livewire Scanner Component --}}
    @livewire(App\Livewire\QRScannerInterface::class)
</x-app-layout>

@push('styles')
<style>
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
        background: linear-gradient(90deg, transparent, #10b981, transparent);
        animation: scanline 2s linear infinite;
    }
</style>
@endpush
