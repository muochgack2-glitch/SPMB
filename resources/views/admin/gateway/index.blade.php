@extends('layouts.admin')

@section('styles')
<style>
    /* ==================== LIGHT MODE (Default) ==================== */
    .metric-box {
        background: #f1f3f5 !important;
        border: 1px solid #dee2e6 !important;
        color: #212529 !important;
    }
    
    .metric-box h6 {
        color: #212529 !important;
        font-weight: 600;
    }
    
    .metric-box small {
        color: #6c757d !important;
    }
    
    .failover-box {
        background: #f1f3f5 !important;
        border: 1px solid #dee2e6 !important;
        color: #212529 !important;
    }
    
    .failover-box h5 {
        color: #212529 !important;
        font-weight: 600;
    }
    
    .failover-box small {
        color: #6c757d !important;
    }
    
    .failover-box-primary {
        background: rgba(102, 126, 234, 0.1) !important;
        border: 1px solid rgba(102, 126, 234, 0.3) !important;
    }
    
    .failover-box-primary h5 {
        font-weight: 600;
    }
    
    .spinner-lg {
        width: 3rem !important;
        height: 3rem !important;
    }
    
    .qr-code-container {
        display: none;
    }
    
    .qr-code-container[style*="display: block"],
    .qr-code-container[x-show] {
        display: block !important;
    }
    
    .qr-error-container {
        display: none;
    }
    
    .qr-error-container[style*="display: block"],
    .qr-error-container[x-show] {
        display: block !important;
    }
    
    .qr-image {
        width: 400px !important;
        height: 400px !important;
        max-width: 100%;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
    
    .qr-code-box {
        background: #ffffff !important;
    }
    
    .logs-pre {
        max-height: 70vh;
        overflow-y: auto;
        background: #1e1e1e !important;
        color: #d4d4d4 !important;
        font-size: 13px;
        font-family: 'Courier New', monospace;
        line-height: 1.6;
    }
    
    .modal-footer-custom {
        background: #f8f9fa !important;
    }
    
    .connection-status-box {
        background: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .connection-status-box.connected {
        background: #d1e7dd !important;
        border-color: #a3cfbb !important;
    }
    
    .connection-status-box.disconnected {
        background: #fff3cd !important;
        border-color: #ffecb5 !important;
    }

    /* ==================== DARK MODE ==================== */
    
    /* Main card backgrounds */
    html.admin-dark .card,
    .admin-dark .card {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.1) !important;
    }
    
    html.admin-dark .card-header,
    .admin-dark .card-header {
        border-color: rgba(255,255,255,0.1) !important;
    }
    
    html.admin-dark .card-body,
    html.admin-dark .card-footer,
    .admin-dark .card-body,
    .admin-dark .card-footer {
        color: #e2e8f0 !important;
    }
    
    /* Metric boxes in dark mode - FORCE OVERRIDE */
    html.admin-dark .metric-box,
    .admin-dark .metric-box {
        background: #2d3748 !important;
        border: 1px solid #4a5568 !important;
        color: #f7fafc !important;
    }
    
    html.admin-dark .metric-box h6,
    .admin-dark .metric-box h6 {
        color: #f7fafc !important;
        font-weight: 600;
    }
    
    html.admin-dark .metric-box small,
    .admin-dark .metric-box small {
        color: #cbd5e0 !important;
    }
    
    html.admin-dark .metric-box .text-muted,
    .admin-dark .metric-box .text-muted {
        color: #cbd5e0 !important;
    }
    
    /* Failover boxes in dark mode */
    html.admin-dark .failover-box,
    .admin-dark .failover-box {
        background: #2d3748 !important;
        border: 1px solid #4a5568 !important;
        color: #f7fafc !important;
    }
    
    html.admin-dark .failover-box h5,
    .admin-dark .failover-box h5 {
        color: #f7fafc !important;
        font-weight: 600;
    }
    
    html.admin-dark .failover-box small,
    .admin-dark .failover-box small {
        color: #cbd5e0 !important;
    }
    
    html.admin-dark .failover-box-primary,
    .admin-dark .failover-box-primary {
        background: rgba(102, 126, 234, 0.2) !important;
        border: 1px solid rgba(102, 126, 234, 0.4) !important;
        color: #f7fafc !important;
    }
    
    html.admin-dark .failover-box-primary h5,
    .admin-dark .failover-box-primary h5 {
        color: #8b9fff !important;
        font-weight: 600;
    }
    
    html.admin-dark .failover-box-primary small,
    .admin-dark .failover-box-primary small {
        color: #cbd5e0 !important;
    }
    
    html.admin-dark .failover-box-primary .text-primary,
    .admin-dark .failover-box-primary .text-primary {
        color: #8b9fff !important;
    }
    
    /* Text colors in dark mode */
    html.admin-dark .text-muted,
    .admin-dark .text-muted {
        color: rgba(255,255,255,0.6) !important;
    }
    
    html.admin-dark small.text-muted,
    .admin-dark small.text-muted {
        color: rgba(255,255,255,0.5) !important;
    }
    
    html.admin-dark h6,
    .admin-dark h6 {
        color: #f1f5f9 !important;
    }
    
    /* Connection status boxes in dark mode */
    html.admin-dark .connection-status-box,
    .admin-dark .connection-status-box {
        background: #2d3748 !important;
        border: 1px solid #4a5568 !important;
    }
    
    html.admin-dark .connection-status-box.connected,
    .admin-dark .connection-status-box.connected {
        background: rgba(25, 135, 84, 0.2) !important;
        border-color: rgba(25, 135, 84, 0.5) !important;
    }
    
    html.admin-dark .connection-status-box.disconnected,
    .admin-dark .connection-status-box.disconnected {
        background: rgba(255, 193, 7, 0.2) !important;
        border-color: rgba(255, 193, 7, 0.5) !important;
    }
    
    html.admin-dark .connection-status-box h6,
    .admin-dark .connection-status-box h6 {
        color: #f1f5f9 !important;
    }
    
    /* Background utilities */
    html.admin-dark .bg-primary.bg-opacity-10,
    .admin-dark .bg-primary.bg-opacity-10 {
        background: rgba(102, 126, 234, 0.2) !important;
        color: #b8c5ff !important;
    }
    
    /* Alert styles */
    html.admin-dark .alert-info,
    .admin-dark .alert-info {
        background: rgba(13, 110, 253, 0.2) !important;
        border-color: rgba(13, 110, 253, 0.3) !important;
        color: #9ec5fe !important;
    }
    
    html.admin-dark .alert-danger,
    .admin-dark .alert-danger {
        background: rgba(220, 53, 69, 0.2) !important;
        border-color: rgba(220, 53, 69, 0.3) !important;
        color: #ea868f !important;
    }
    
    html.admin-dark .alert-warning,
    .admin-dark .alert-warning {
        background: rgba(255, 193, 7, 0.2) !important;
        border-color: rgba(255, 193, 7, 0.3) !important;
        color: #ffecb5 !important;
    }
    
    /* Modal styles */
    html.admin-dark .modal-content,
    .admin-dark .modal-content {
        background: #1e293b !important;
        color: #e2e8f0 !important;
    }
    
    html.admin-dark .modal-header,
    .admin-dark .modal-header {
        border-color: rgba(255,255,255,0.1) !important;
    }
    
    html.admin-dark .modal-footer-custom,
    .admin-dark .modal-footer-custom {
        background: rgba(255,255,255,0.02) !important;
        border-color: rgba(255,255,255,0.1) !important;
    }
    
    /* Buttons */
    html.admin-dark .btn-outline-primary,
    html.admin-dark .btn-outline-warning,
    html.admin-dark .btn-outline-danger,
    html.admin-dark .btn-outline-info,
    .admin-dark .btn-outline-primary,
    .admin-dark .btn-outline-warning,
    .admin-dark .btn-outline-danger,
    .admin-dark .btn-outline-info {
        border-width: 2px !important;
    }
    
    /* Gradient headers - stay vibrant */
    .gradient-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    /* Success/Danger headers */
    html.admin-dark .bg-success,
    .admin-dark .bg-success {
        background: rgba(25, 135, 84, 0.9) !important;
    }
    
    html.admin-dark .bg-danger,
    .admin-dark .bg-danger {
        background: rgba(220, 53, 69, 0.9) !important;
    }
    
    /* Icon colors */
    html.admin-dark .text-primary,
    .admin-dark .text-primary {
        color: #8b9fff !important;
    }
    
    html.admin-dark .text-info,
    .admin-dark .text-info {
        color: #54b4d3 !important;
    }
    
    html.admin-dark .text-warning,
    .admin-dark .text-warning {
        color: #ffc107 !important;
    }
    
    html.admin-dark .text-success,
    .admin-dark .text-success {
        color: #20c997 !important;
    }
    
    /* QR Code box - stays white */
    html.admin-dark .qr-code-box,
    .admin-dark .qr-code-box {
        background: #ffffff !important;
        border: 2px solid rgba(255,255,255,0.2) !important;
    }
    
    /* Logs pre tag - GitHub style */
    html.admin-dark .logs-pre,
    .admin-dark .logs-pre {
        background: #0d1117 !important;
        color: #c9d1d9 !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid" x-data="gatewayManager()">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1" style="color: #ffffff !important;">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp Gateway Management
                            </h1>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.85) !important;">Monitor dan kelola dual gateway dengan failover otomatis</p>
                        </div>
                        <div class="text-end">
                            <div class="small mb-2" style="color: rgba(255, 255, 255, 0.9) !important;">
                                <i class="fas fa-clock me-1"></i>
                                Last updated: <span x-text="lastUpdated" class="fw-bold"></span>
                            </div>
                            <button class="btn btn-light" @click="refreshAll()" :disabled="isRefreshing" style="font-weight: 600; color: #000 !important; background-color: #fff;">
                                <i class="fas fa-sync-alt me-2" :class="{'fa-spin': isRefreshing}"></i>
                                <span x-text="isRefreshing ? 'Refreshing...' : 'Refresh All'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Refresh Indicator -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info border-0 d-flex align-items-center justify-content-between" role="alert">
                <div>
                    <i class="fas fa-sync-alt me-2"></i>
                    <strong>Auto-refresh aktif</strong> - Status gateway akan diperbarui otomatis setiap 10 detik
                </div>
                <div>
                    <span class="badge bg-primary">
                        <i class="fas fa-circle fa-fade me-1"></i>Real-time monitoring
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gateway Cards -->
    <div class="row">
        @foreach($statuses as $key => $gateway)
        <div class="col-lg-6 mb-4" id="gateway-{{ $key }}">
            <div class="card border-0 shadow-sm h-100">
                <!-- Card Header -->
                <div class="card-header border-0 {{ $gateway['online'] ? 'bg-success' : 'bg-danger' }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-server me-2"></i>{{ $gateway['info']['name'] }}
                            </h5>
                            <small class="text-white" style="opacity: 0.95;">{{ $gateway['info']['purpose'] }}</small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white status-icon">
                                <i class="fas fa-{{ $gateway['online'] ? 'check-circle' : 'times-circle' }}"></i>
                            </h3>
                            <small class="text-white status-text" style="opacity: 0.95;">{{ $gateway['online'] ? 'Online' : 'Offline' }}</small>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                    @if($gateway['online'] && $gateway['status'])
                        <!-- Connection Status -->
                        <div class="connection-status-box {{ $gateway['status']['status'] === 'connected' ? 'connected' : 'disconnected' }} d-flex align-items-center justify-content-between mb-3 p-3 rounded">
                            <div>
                                <h6 class="mb-0">Connection Status</h6>
                                <small class="text-muted">Port {{ parse_url($gateway['info']['url'], PHP_URL_PORT) }}</small>
                            </div>
                            <span class="badge {{ $gateway['status']['status'] === 'connected' ? 'bg-success' : 'bg-warning' }} fs-6 status-badge">
                                <i class="fas fa-{{ $gateway['status']['status'] === 'connected' ? 'link' : 'qrcode' }} me-1"></i>
                                {{ ucfirst($gateway['status']['status']) }}
                            </span>
                        </div>

                        @if($gateway['health'])
                        <!-- Health Metrics -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="metric-box p-3 rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <small class="text-muted">Uptime</small>
                                    </div>
                                    <h6 class="mb-0">
                                        @php
                                            $uptime = $gateway['health']['uptime'];
                                            $days = floor($uptime / 86400);
                                            $hours = floor(($uptime % 86400) / 3600);
                                            $minutes = floor(($uptime % 3600) / 60);
                                        @endphp
                                        @if($days > 0)
                                            {{ $days }}d {{ $hours }}h
                                        @elseif($hours > 0)
                                            {{ $hours }}h {{ $minutes }}m
                                        @else
                                            {{ $minutes }}m
                                        @endif
                                    </h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box p-3 rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-memory text-info me-2"></i>
                                        <small class="text-muted">Memory</small>
                                    </div>
                                    <h6 class="mb-0">
                                        {{ $gateway['health']['memory']['percentage'] }}%
                                        <small class="text-muted">({{ $gateway['health']['memory']['heapUsed'] }}MB)</small>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box p-3 rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-microchip text-warning me-2"></i>
                                        <small class="text-muted">CPU Time</small>
                                    </div>
                                    <h6 class="mb-0">
                                        {{ $gateway['health']['cpu']['user'] + $gateway['health']['cpu']['system'] }}ms
                                    </h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box p-3 rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-qrcode text-success me-2"></i>
                                        <small class="text-muted">QR Status</small>
                                    </div>
                                    <h6 class="mb-0">
                                        {{ $gateway['status']['qrAvailable'] ? 'Available' : 'Not Needed' }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="alert alert-danger border-0" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Gateway Offline</h6>
                                    <p class="mb-0">Gateway tidak dapat dijangkau atau sedang tidak aktif</p>
                                    @if(isset($gateway['error']))
                                    <small class="text-muted">{{ $gateway['error'] }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Card Footer -->
                <div class="card-footer bg-white border-0">
                    <div class="row g-2">
                        <!-- Row 1: Main Actions -->
                        <div class="col-6 col-lg-3">
                            <button class="btn btn-outline-primary w-100" @click="viewQR('{{ $key }}')">
                                <i class="fas fa-qrcode"></i>
                                <span class="d-none d-md-inline ms-1">QR Code</span>
                            </button>
                        </div>
                        <div class="col-6 col-lg-3">
                            <button class="btn btn-outline-warning w-100" @click="restart('{{ $key }}')">
                                <i class="fas fa-sync"></i>
                                <span class="d-none d-md-inline ms-1">Restart</span>
                            </button>
                        </div>
                        <div class="col-6 col-lg-3">
                            <button class="btn btn-outline-danger w-100" @click="logout('{{ $key }}')">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="d-none d-md-inline ms-1">Logout</span>
                            </button>
                        </div>
                        <div class="col-6 col-lg-3">
                            <button class="btn btn-outline-info w-100" @click="viewLogs('{{ $key }}')">
                                <i class="fas fa-file-alt"></i>
                                <span class="d-none d-md-inline ms-1">Logs</span>
                            </button>
                        </div>
                        
                        <!-- Row 2: Critical Action -->
                        <div class="col-12">
                            <button class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2" @click="resetGateway('{{ $key }}')">
                                <i class="fas fa-power-off"></i>
                                <span>Reset & Reconnect (Hard Reset)</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Failover Settings Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt text-primary me-2"></i>Failover Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="form-check form-switch me-3">
                                    <input class="form-check-input" type="checkbox" id="failoverEnabled" 
                                           {{ $failoverSettings['enabled'] ? 'checked' : '' }}
                                           @change="toggleFailover($event.target.checked)"
                                           style="width: 3em; height: 1.5em; cursor: pointer;">
                                </div>
                                <div>
                                    <h6 class="mb-0">Auto Failover</h6>
                                    <small class="text-muted" x-text="failoverEnabled ? 'Aktif - Backup otomatis digunakan jika primary offline' : 'Tidak aktif'">
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="failover-box text-center p-3 rounded">
                                <small class="text-muted d-block">Health Check Timeout</small>
                                <h5 class="mb-0">{{ $failoverSettings['timeout'] }} <small>detik</small></h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="failover-box-primary text-center p-3 rounded">
                                <small class="text-muted d-block">Active Gateway</small>
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-server me-2"></i>Primary (Port 3000)
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-info-circle text-info me-2"></i>Gateway Management Tips
                            </h6>
                            <small class="text-muted">
                                • Restart: Gunakan jika gateway hang atau memory tinggi (tidak perlu scan QR ulang)
                                • Logout: Untuk generate QR baru atau ganti nomor WhatsApp
                            </small>
                        </div>
                        <a href="{{ route('whatsapp.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-line me-2"></i>Lihat Dashboard Pesan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 gradient-header">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-qrcode me-2"></i>Scan QR Code WhatsApp
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div x-show="loadingQR" class="py-5">
                        <div class="spinner-border text-primary mb-3 spinner-lg" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Loading QR code...</p>
                    </div>
                    <div x-show="!loadingQR && qrCode" class="qr-code-container">
                        <div class="qr-code-box bg-white p-4 rounded shadow-sm d-inline-block mb-3">
                            <img :src="qrCode" alt="WhatsApp QR Code" class="img-fluid qr-image" style="display: block;" />
                        </div>
                        <div class="alert alert-info border-0 text-start" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-mobile-alt me-2"></i>Cara Scan QR Code:
                            </h6>
                            <ol class="mb-0 ps-3 small">
                                <li>Buka WhatsApp di HP Anda</li>
                                <li>Tap menu <strong>(⋮)</strong> → <strong>"Perangkat Tertaut"</strong></li>
                                <li>Tap <strong>"Tautkan Perangkat"</strong></li>
                                <li>Arahkan kamera ke QR code di atas</li>
                                <li class="text-danger mt-2"><strong>Penting:</strong> QR code hanya berlaku 30-60 detik. Jika expired, klik tombol "Refresh QR".</li>
                            </ol>
                        </div>
                    </div>
                    <div x-show="!loadingQR && !qrCode" class="qr-error-container">
                        <div class="alert alert-warning border-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span x-text="qrError"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 modal-footer-custom">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" @click="viewQR(currentGateway)">
                        <i class="fas fa-sync-alt me-2"></i>Refresh QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Modal -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 gradient-header">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-file-alt me-2"></i>Gateway Logs
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <pre x-text="logs" class="mb-0 p-4 logs-pre"></pre>
                </div>
                <div class="modal-footer border-0 modal-footer-custom">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" @click="viewLogs(currentGateway)">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Confirmation Modal -->
    <div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hard Reset
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger border-0" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-power-off me-2"></i>WARNING: Hard Reset Gateway
                        </h6>
                        <p class="mb-2">Tindakan ini akan melakukan:</p>
                        <ul class="mb-0 ps-3">
                            <li>Restart PM2 process</li>
                            <li>Hapus session WhatsApp</li>
                            <li>Generate QR code baru</li>
                        </ul>
                    </div>
                    <p class="mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Gateway akan <strong>offline 10-15 detik</strong>. Pastikan tidak ada pengiriman pesan penting yang sedang berlangsung.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" class="btn btn-danger" @click="confirmReset()">
                        <i class="fas fa-power-off me-2"></i>Ya, Reset Gateway
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restart Confirmation Modal -->
    <div class="modal fade" id="restartConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-sync me-2"></i>Konfirmasi Restart Gateway
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-redo me-2"></i>Restart Node.js Process
                        </h6>
                        <p class="mb-2">Tindakan ini akan:</p>
                        <ul class="mb-0 ps-3">
                            <li>Restart Node.js process</li>
                            <li>Koneksi terputus sementara (5-10 detik)</li>
                            <li><strong>Session WhatsApp tetap tersimpan</strong></li>
                            <li><strong>Tidak perlu scan QR ulang</strong></li>
                        </ul>
                    </div>
                    <p class="mb-0">
                        <i class="fas fa-lightbulb text-info me-2"></i>
                        Gunakan ini jika gateway hang, lambat, atau memory tinggi.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" class="btn btn-warning" @click="confirmRestart()">
                        <i class="fas fa-sync me-2"></i>Ya, Restart Gateway
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-sign-out-alt me-2"></i>Konfirmasi Logout WhatsApp
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout dari WhatsApp
                        </h6>
                        <p class="mb-2">Tindakan ini akan:</p>
                        <ul class="mb-0 ps-3">
                            <li>Logout dari session WhatsApp</li>
                            <li>Session akan dihapus</li>
                            <li>Generate QR code baru</li>
                            <li><strong>Anda perlu scan QR ulang</strong></li>
                        </ul>
                    </div>
                    <p class="mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Gunakan ini untuk generate QR baru atau ganti nomor WhatsApp.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" class="btn btn-warning" @click="confirmLogout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Ya, Logout WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Result Modal -->
    <div class="modal fade" id="resetResultModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0" :class="resetSuccess ? 'bg-success' : 'bg-danger'" class="text-white">
                    <h5 class="modal-title text-white">
                        <i :class="resetSuccess ? 'fas fa-check-circle' : 'fas fa-times-circle'" class="me-2"></i>
                        <span x-text="resetSuccess ? 'Reset Berhasil' : 'Reset Gagal'"></span>
                    </h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <div x-show="resetSuccess">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                        <h5 class="mb-3">Gateway Berhasil Di-reset!</h5>
                        <p class="text-muted mb-3" x-text="resetMessage"></p>
                        <div class="alert alert-info border-0 text-start" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Langkah Selanjutnya:
                            </h6>
                            <ol class="mb-0 ps-3 small">
                                <li>Halaman akan reload otomatis dalam <strong x-text="countdown"></strong> detik</li>
                                <li>Setelah reload, klik tombol <strong>"Lihat QR"</strong></li>
                                <li>Scan QR code dengan WhatsApp Anda</li>
                            </ol>
                        </div>
                    </div>
                    <div x-show="!resetSuccess">
                        <i class="fas fa-times-circle text-danger mb-3" style="font-size: 4rem;"></i>
                        <h5 class="mb-3">Reset Gagal</h5>
                        <div class="alert alert-danger border-0 text-start" role="alert">
                            <p class="mb-0" x-text="resetMessage"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" x-show="!resetSuccess">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" @click="location.reload()" x-show="!resetSuccess">
                        <i class="fas fa-sync-alt me-2"></i>Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function gatewayManager() {
    return {
        loadingQR: false,
        qrCode: null,
        qrError: null,
        logs: '',
        currentGateway: null,
        failoverEnabled: {{ $failoverSettings['enabled'] ? 'true' : 'false' }},
        resetSuccess: false,
        resetMessage: '',
        countdown: 15,
        countdownInterval: null,
        isRefreshing: false,
        lastUpdated: '{{ now()->format("H:i:s") }}',
        autoRefreshInterval: null,
        gatewayStatuses: @json($statuses),

        init() {
            // Start auto-refresh every 10 seconds
            this.startAutoRefresh();
        },

        startAutoRefresh() {
            this.autoRefreshInterval = setInterval(() => {
                this.fetchStatuses();
            }, 10000); // 10 seconds
        },

        stopAutoRefresh() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
            }
        },

        async fetchStatuses() {
            try {
                const response = await fetch('/admin/gateway/statuses');
                const data = await response.json();
                
                if (data.success) {
                    this.gatewayStatuses = data.statuses;
                    this.lastUpdated = new Date().toLocaleTimeString('id-ID');
                    
                    // Update DOM dynamically
                    this.updateGatewayCards(data.statuses);
                }
            } catch (error) {
                console.error('Failed to fetch statuses:', error);
            }
        },

        updateGatewayCards(statuses) {
            // Update each gateway card with new data
            Object.keys(statuses).forEach(key => {
                const gateway = statuses[key];
                const cardHeader = document.querySelector(`#gateway-${key} .card-header`);
                const statusBadge = document.querySelector(`#gateway-${key} .status-badge`);
                const statusText = document.querySelector(`#gateway-${key} .status-text`);
                const statusIcon = document.querySelector(`#gateway-${key} .status-icon`);
                
                if (cardHeader && statusBadge && statusText && statusIcon) {
                    // Update header background
                    cardHeader.className = `card-header border-0 ${gateway.online ? 'bg-success' : 'bg-danger'} text-white`;
                    
                    // Update badge
                    const badgeClass = gateway.online ? 'bg-success' : 'bg-danger';
                    const badgeIcon = gateway.online ? 'link' : 'qrcode';
                    const statusLabel = gateway.status?.status ? gateway.status.status.charAt(0).toUpperCase() + gateway.status.status.slice(1) : 'Unknown';
                    
                    statusBadge.innerHTML = `<span class="badge ${badgeClass} fs-6">
                        <i class="fas fa-${badgeIcon} me-1"></i>
                        ${statusLabel}
                    </span>`;
                    
                    // Update icon
                    statusIcon.className = `fas fa-${gateway.online ? 'check-circle' : 'times-circle'}`;
                    
                    // Update text
                    statusText.textContent = gateway.online ? 'Online' : 'Offline';
                }
            });
        },

        viewQR(gateway) {
            this.currentGateway = gateway;
            this.loadingQR = true;
            this.qrCode = null;
            this.qrError = null;
            
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();

            fetch(`/admin/gateway/${gateway}/qr`)
                .then(res => res.json())
                .then(data => {
                    this.loadingQR = false;
                    if (data.success) {
                        this.qrCode = data.qr;
                    } else {
                        this.qrError = data.message || 'Gateway sedang connected atau QR tidak tersedia';
                    }
                })
                .catch(err => {
                    this.loadingQR = false;
                    this.qrError = 'Failed to load QR code: ' + err.message;
                });
        },

        restart(gateway) {
            this.currentGateway = gateway;
            const modal = new bootstrap.Modal(document.getElementById('restartConfirmModal'));
            modal.show();
        },

        confirmRestart() {
            const gateway = this.currentGateway;
            const modal = bootstrap.Modal.getInstance(document.getElementById('restartConfirmModal'));
            modal.hide();

            // Show loading in result modal
            this.resetSuccess = true;
            this.resetMessage = 'Restarting gateway...';
            this.countdown = 8;
            const resultModal = new bootstrap.Modal(document.getElementById('resetResultModal'));
            resultModal.show();

            fetch(`/admin/gateway/${gateway}/restart`, { 
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.resetSuccess = true;
                        this.resetMessage = data.message;
                        this.countdown = 8;
                        this.startCountdown(resultModal);
                    } else {
                        this.resetSuccess = false;
                        this.resetMessage = data.message;
                    }
                })
                .catch(err => {
                    this.resetSuccess = false;
                    this.resetMessage = 'Failed to restart: ' + err.message;
                });
        },

        logout(gateway) {
            this.currentGateway = gateway;
            const modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
            modal.show();
        },

        confirmLogout() {
            const gateway = this.currentGateway;
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutConfirmModal'));
            modal.hide();

            // Show loading in result modal
            this.resetSuccess = true;
            this.resetMessage = 'Logging out...';
            this.countdown = 5;
            const resultModal = new bootstrap.Modal(document.getElementById('resetResultModal'));
            resultModal.show();

            fetch(`/admin/gateway/${gateway}/logout`, { 
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.resetSuccess = true;
                        this.resetMessage = data.message;
                        this.countdown = 5;
                        this.startCountdown(resultModal);
                    } else {
                        this.resetSuccess = false;
                        this.resetMessage = data.message;
                    }
                })
                .catch(err => {
                    this.resetSuccess = false;
                    this.resetMessage = 'Failed to logout: ' + err.message;
                });
        },

        viewLogs(gateway) {
            this.currentGateway = gateway;
            this.logs = 'Loading logs...';
            
            const modal = new bootstrap.Modal(document.getElementById('logsModal'));
            modal.show();

            fetch(`/admin/gateway/${gateway}/logs`)
                .then(res => res.json())
                .then(data => {
                    this.logs = data.success ? data.logs : ('Error: ' + data.message);
                })
                .catch(err => {
                    this.logs = 'Failed to load logs: ' + err.message;
                });
        },

        toggleFailover(enabled) {
            this.failoverEnabled = enabled;

            fetch('/admin/gateway/toggle-failover', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ enabled: enabled })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Show success notification (optional)
                        console.log('✅ Failover setting updated:', enabled);
                    } else {
                        alert('❌ ' + data.message);
                        // Revert toggle state on error
                        this.failoverEnabled = !enabled;
                        document.getElementById('failoverEnabled').checked = !enabled;
                    }
                })
                .catch(err => {
                    alert('❌ Failed to update setting: ' + err.message);
                    // Revert toggle state on error
                    this.failoverEnabled = !enabled;
                    document.getElementById('failoverEnabled').checked = !enabled;
                });
        },

        resetGateway(gateway) {
            this.currentGateway = gateway;
            const modal = new bootstrap.Modal(document.getElementById('resetConfirmModal'));
            modal.show();
        },

        confirmReset() {
            // Close confirmation modal
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('resetConfirmModal'));
            confirmModal.hide();

            // Find the button that triggered this
            const gatewayName = this.currentGateway === 'spmb' ? 'SPMB' : 'Absensi';
            
            // Show loading state on button
            const buttons = document.querySelectorAll(`button[onclick*="resetGateway('${this.currentGateway}')"]`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
            });

            fetch(`/admin/gateway/${this.currentGateway}/reset`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
                .then(res => res.json())
                .then(data => {
                    this.resetSuccess = data.success;
                    this.resetMessage = data.message;
                    
                    // Show result modal
                    const resultModal = new bootstrap.Modal(document.getElementById('resetResultModal'));
                    resultModal.show();

                    if (data.success) {
                        // Start countdown
                        this.countdown = 15;
                        this.countdownInterval = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(this.countdownInterval);
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        // Reset button state on error
                        buttons.forEach(btn => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-power-off"></i> <span>Reset & Reconnect (Hard Reset)</span>';
                        });
                    }
                })
                .catch(err => {
                    this.resetSuccess = false;
                    this.resetMessage = 'Failed to reset: ' + err.message;
                    
                    const resultModal = new bootstrap.Modal(document.getElementById('resetResultModal'));
                    resultModal.show();

                    // Reset button state
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-power-off"></i> <span>Reset & Reconnect (Hard Reset)</span>';
                    });
                });
        },

        refreshAll() {
            this.isRefreshing = true;
            this.fetchStatuses().finally(() => {
                this.isRefreshing = false;
            });
        }
    }
}
</script>
@endsection
