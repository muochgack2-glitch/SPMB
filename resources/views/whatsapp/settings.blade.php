@extends('layouts.admin')

@section('title', 'Pengaturan WhatsApp Gateway')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">⚙️ Pengaturan WhatsApp Gateway</h1>
            <p class="text-muted mb-0">Konfigurasi sistem WhatsApp Gateway</p>
        </div>
        <div>
            <a href="{{ route('whatsapp.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('whatsapp.settings.update') }}">
        @csrf

        @foreach($settings as $group => $groupSettings)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom" style="background: var(--bg-primary);">
                <h5 class="mb-0" style="color: var(--text-primary);">
                    @if($group == 'general')
                    <i class="fas fa-cog me-2"></i>Pengaturan Umum
                    @elseif($group == 'connection')
                    <i class="fas fa-plug me-2"></i>Koneksi
                    @elseif($group == 'notification')
                    <i class="fas fa-bell me-2"></i>Notifikasi
                    @elseif($group == 'advanced')
                    <i class="fas fa-sliders-h me-2"></i>Lanjutan
                    @elseif($group == 'rate_limiting')
                    <i class="fas fa-clock me-2"></i>Rate Limiting
                    @else
                    <i class="fas fa-folder me-2"></i>{{ ucfirst($group) }}
                    @endif
                </h5>
            </div>
            <div class="card-body" style="background: var(--bg-primary);">
                <div class="row">
                    @foreach($groupSettings as $setting)
                    <div class="col-md-6 mb-3">
                        <label for="{{ $setting->key }}" class="form-label" style="color: var(--text-primary);">
                            {{ $setting->label }}
                            @if($setting->description)
                            <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                            @endif
                        </label>
                        
                        @if($setting->type == 'boolean')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="{{ $setting->key }}" value="true" {{ $setting->value == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $setting->key }}">
                                {{ $setting->value == 'true' ? 'Aktif' : 'Nonaktif' }}
                            </label>
                        </div>
                        @elseif($setting->type == 'integer')
                        <input type="number" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @elseif($setting->type == 'json' || $setting->type == 'array')
                        <textarea class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                        @else
                        <input type="text" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @endif
                        
                        @if($setting->description)
                        <small class="text-muted">{{ $setting->description }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="card border-0 shadow-sm">
            <div class="card-body" style="background: var(--bg-primary);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1" style="color: var(--text-primary);">Simpan Perubahan</h6>
                        <p class="text-muted small mb-0">Pastikan semua pengaturan sudah benar sebelum menyimpan</p>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="card border-danger shadow-sm mt-4">
        <div class="card-header bg-danger bg-opacity-10 border-danger">
            <h5 class="mb-0 text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
            </h5>
        </div>
        <div class="card-body" style="background: var(--bg-primary);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1" style="color: var(--text-primary);">Logout WhatsApp</h6>
                    <p class="text-muted small mb-0">Putuskan koneksi WhatsApp dan hapus session. Anda perlu scan QR code lagi.</p>
                </div>
                <div>
                    <form method="POST" action="{{ route('whatsapp.logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin logout dari WhatsApp? Anda perlu scan QR code lagi.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout WhatsApp
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection
