@extends('layouts.admin')

@section('title', 'Rekap Nomor HP Pendaftar')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">📱 Rekap Nomor HP & Status Pesan</h1>
            <p class="text-muted mb-0">Daftar nomor HP pendaftar untuk broadcast WhatsApp</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="sendBroadcast()">
                <i class="fas fa-paper-plane me-2"></i>Kirim Broadcast
            </button>
            <button class="btn btn-primary" onclick="exportPhones()">
                <i class="fas fa-download me-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Message Status Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="phone-list-tabs-wrapper">
                <ul class="nav nav-tabs nav-fill border-0 phone-list-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'all' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'all'])) }}">
                            <i class="fas fa-list"></i>
                            <span class="tab-text">Semua</span>
                            <span class="badge badge-tab bg-primary">{{ $tabCounts['all'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'sent' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'sent'])) }}">
                            <i class="fas fa-check-circle"></i>
                            <span class="tab-text">Terkirim</span>
                            <span class="badge badge-tab bg-success">{{ $tabCounts['sent'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'not-sent' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'not-sent'])) }}">
                            <i class="fas fa-clock"></i>
                            <span class="tab-text">Belum Dikirim</span>
                            <span class="badge badge-tab bg-secondary">{{ $tabCounts['not-sent'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'failed' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'failed'])) }}">
                            <i class="fas fa-times-circle"></i>
                            <span class="tab-text">Gagal</span>
                            <span class="badge badge-tab bg-danger">{{ $tabCounts['failed'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'accepted' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'accepted'])) }}">
                            <i class="fas fa-user-check"></i>
                            <span class="tab-text">Diterima</span>
                            <span class="badge badge-tab bg-success">{{ $tabCounts['accepted'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'no-phone' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'no-phone'])) }}">
                            <i class="fas fa-phone-slash"></i>
                            <span class="tab-text">Tidak Ada Nomor</span>
                            <span class="badge badge-tab bg-dark">{{ $tabCounts['no-phone'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'external' ? 'active' : '' }}" 
                           href="{{ route('whatsapp.phone-list', array_merge(request()->except('tab'), ['tab' => 'external'])) }}">
                            <i class="fas fa-external-link-alt"></i>
                            <span class="tab-text">Eksternal</span>
                            <span class="badge badge-tab bg-info">{{ $tabCounts['external'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Search Box -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('whatsapp.phone-list') }}" id="searchForm" class="row g-3">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="col-md-10">
                    <input type="text" name="search" id="searchInput" class="form-control" 
                           placeholder="Cari berdasarkan nama, NISN, atau nomor registrasi..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>Ketik untuk mencari otomatis...
                    </small>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Phone List Table -->
    @if($activeTab === 'external')
        <!-- EXTERNAL TAB CONTENT (Tasks 8.2-8.5) -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="showDuplicatesOnly" 
                           {{ request('show_duplicates_only') === 'true' ? 'checked' : '' }}
                           onchange="toggleDuplicateFilter(this)">
                    <label class="form-check-label" for="showDuplicatesOnly">
                        🔄 Tampilkan hanya duplikat SPMB
                    </label>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar Recipient Eksternal
                </h5>
            </div>
            <div class="card-body p-0" style="background: var(--bg-primary);">
                @if(isset($externalRecipients) && $externalRecipients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Nomor HP</th>
                                <th>Batch</th>
                                <th>Notes</th>
                                <th>Pesan</th>
                                <th>Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($externalRecipients as $recipient)
                            <tr>
                                <td style="color: var(--text-primary);">
                                    {{ $recipient->name }}
                                    @if($recipient->is_duplicate_spmb)
                                        <span class="badge bg-warning text-dark ms-1" 
                                              title="Duplikat dengan database SPMB"
                                              style="cursor: pointer;"
                                              onclick="window.location.href='{{ route('pendaftar.show', $recipient->matched_pendaftar_id) }}'">
                                            🔄 Duplikat
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="https://wa.me/{{ $recipient->phone_normalized }}" target="_blank" class="text-decoration-none">
                                        <i class="fab fa-whatsapp text-success me-1"></i>
                                        {{ $recipient->phone }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $recipient->batch->batch_name }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $recipient->notes ?? '-' }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewExternalMessages({{ $recipient->id }})"
                                            title="Lihat riwayat pesan">
                                        <i class="fas fa-eye me-1"></i>Lihat
                                    </button>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $recipient->created_at->diffForHumans() }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-3">
                    <x-custom-pagination :paginator="$externalRecipients" :showPerPage="true" />
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada data eksternal</p>
                    <a href="{{ route('whatsapp.broadcast.external') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Buat Broadcast Eksternal
                    </a>
                </div>
                @endif
            </div>
        </div>
    @else
        <!-- REGULAR SPMB TABLE -->
        <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar Nomor HP
                </h5>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    <label class="form-check-label" for="selectAll">
                        Pilih Semua (<span id="selectedCount">0</span>)
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body p-0" style="background: var(--bg-primary);">
            @if($pendaftars->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAllDisabled" disabled>
                            </th>
                            <th>No. Pendaftaran</th>
                            <th>Nama Lengkap</th>
                            <th>NISN</th>
                            <th>Jurusan</th>
                            <th>Gelombang</th>
                            <th>Nomor HP</th>
                            <th>Tipe</th>
                            <th>Status Pesan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendaftars as $pendaftar)
                        @php
                            $phoneData = $pendaftar->phone_data;
                            $msgStatus = $pendaftar->message_status;
                        @endphp
                        <tr>
                            <td>
                                @if($phoneData['phone'])
                                <input type="checkbox" class="form-check-input phone-checkbox" 
                                       value="{{ $phoneData['phone'] }}"
                                       data-id="{{ $pendaftar->id_pendaftar }}"
                                       data-name="{{ $pendaftar->nama_lengkap }}"
                                       data-no-reg="{{ $pendaftar->no_registrasi }}"
                                       data-jurusan="{{ $pendaftar->masterJurusan->nama_jurusan ?? $pendaftar->jurusan }}"
                                       data-nisn="{{ $pendaftar->nisn ?? '-' }}"
                                       data-asal-sekolah="{{ $pendaftar->asal_sekolah ?? '-' }}"
                                       onchange="updateSelectedCount()">
                                @else
                                <i class="fas fa-minus text-muted"></i>
                                @endif
                            </td>
                            <td>
                                <strong style="color: var(--text-primary);">{{ $pendaftar->no_registrasi }}</strong>
                            </td>
                            <td style="color: var(--text-primary);">{{ $pendaftar->nama_lengkap }}</td>
                            <td style="color: var(--text-primary);">{{ $pendaftar->nisn }}</td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $pendaftar->masterJurusan->nama_jurusan ?? $pendaftar->jurusan }}
                                </span>
                            </td>
                            <td>{{ $pendaftar->gelombang }}</td>
                            <td>
                                @if($phoneData['phone'])
                                    <a href="https://wa.me/{{ $phoneData['phone'] }}" target="_blank" class="text-decoration-none">
                                        <i class="fab fa-whatsapp text-success me-1"></i>
                                        {{ $phoneData['formatted'] }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($phoneData['type'])
                                    <span class="badge bg-secondary">{{ $phoneData['type'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $msgStatus['badge'] }}" 
                                      title="{{ $msgStatus['last_message']['date'] ?? 'Belum ada pesan' }}">
                                    {{ $msgStatus['icon'] }} {{ $msgStatus['label'] }}
                                </span>
                                @if($msgStatus['total'] > 0)
                                    <button class="btn btn-sm btn-link p-0 ms-2" 
                                            onclick="viewMessages({{ $pendaftar->id_pendaftar }})"
                                            title="Lihat riwayat pesan">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $pendaftar->status_siswa == 'Diterima' ? 'success' : ($pendaftar->status_siswa == 'Sudah Daftar Ulang' ? 'info' : 'warning') }}">
                                    {{ $pendaftar->status_siswa }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-3">
                <x-custom-pagination :paginator="$pendaftars" :showPerPage="true" />
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada data pendaftar dengan filter yang dipilih</p>
                <a href="{{ route('whatsapp.phone-list') }}" class="btn btn-outline-primary">
                    <i class="fas fa-redo me-2"></i>Reset Filter
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- View Messages Modal -->
<div class="modal fade" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messagesModalLabel">
                    <i class="fas fa-comment-dots me-2"></i>Riwayat Pesan WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="messagesContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-labelledby="broadcastModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="broadcastModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Broadcast WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Penerima:</strong> <span id="recipientCount">0</span> nomor HP terpilih
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Template (Opsional)</label>
                    <select class="form-select" id="broadcastTemplate" onchange="loadTemplate()">
                        <option value="">-- Tulis Manual --</option>
                        @foreach($templates as $template)
                        <option value="{{ $template->id }}" 
                                data-message="{{ $template->message }}"
                                data-variables="{{ json_encode($template->getAvailableVariables()) }}">
                            {{ $template->label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="templateVariables" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Template ini menggunakan variabel. Variabel akan diganti otomatis untuk setiap penerima.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pesan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="broadcastMessage" rows="8" placeholder="Ketik pesan Anda di sini..." required></textarea>
                    <small class="text-muted">Karakter: <span id="broadcastCharCount">0</span></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Preview Pesan</label>
                    <div class="border rounded p-3 message-preview" id="broadcastPreview" style="white-space: pre-wrap; min-height: 100px;">
                        <span class="text-muted">Preview akan muncul di sini...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="submitBroadcast()">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Broadcast
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Broadcast Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-spinner fa-spin me-2"></i>Mengirim Broadcast...
                </h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Progress:</span>
                        <span><strong id="progressText">0 / 0</strong></span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             id="progressBar" 
                             role="progressbar" 
                             style="width: 0%">
                            0%
                        </div>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-3" style="background-color: rgba(40, 167, 69, 0.1);">
                            <h3 class="text-success mb-0" id="successCount">0</h3>
                            <small class="text-muted">Berhasil</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3" style="background-color: rgba(220, 53, 69, 0.1);">
                            <h3 class="text-danger mb-0" id="failedCount">0</h3>
                            <small class="text-muted">Gagal</small>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light mt-3 mb-0">
                    <small>
                        <i class="fas fa-info-circle me-2"></i>
                        Mohon tunggu, proses broadcast sedang berjalan...
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Broadcast Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Broadcast Selesai
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row text-center mb-4">
                    <div class="col-4">
                        <div class="p-3 border rounded">
                            <h2 class="mb-0" id="resultTotal">0</h2>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded bg-success bg-opacity-10">
                            <h2 class="text-success mb-0" id="resultSuccess">0</h2>
                            <small class="text-muted">Berhasil</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded bg-danger bg-opacity-10">
                            <h2 class="text-danger mb-0" id="resultFailed">0</h2>
                            <small class="text-muted">Gagal</small>
                        </div>
                    </div>
                </div>
                
                <div id="resultDetails" style="max-height: 300px; overflow-y: auto;">
                    <!-- Details akan diisi via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* Phone List Tabs Wrapper - Enable Horizontal Scroll on Mobile */
.phone-list-tabs-wrapper {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}

.phone-list-tabs-wrapper::-webkit-scrollbar {
    height: 4px;
}

.phone-list-tabs-wrapper::-webkit-scrollbar-track {
    background: var(--bg-secondary);
}

.phone-list-tabs-wrapper::-webkit-scrollbar-thumb {
    background: var(--bs-primary);
    border-radius: 2px;
}

/* Phone List Tabs Styling */
.phone-list-tabs {
    background: var(--bg-secondary);
    border-radius: 8px 8px 0 0;
    padding: 0.5rem 0;
    flex-wrap: nowrap;
}

.phone-list-tabs .nav-item {
    margin: 0;
    flex: 0 0 auto;
    white-space: nowrap;
}

.phone-list-tabs .nav-link {
    color: var(--text-secondary);
    border: none;
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.25rem;
    min-height: 60px;
}

.phone-list-tabs .nav-link:hover {
    color: var(--text-primary);
    background: rgba(var(--bs-primary-rgb), 0.1);
}

.phone-list-tabs .nav-link.active {
    color: var(--bs-primary);
    background: var(--bg-primary);
    border-bottom: 3px solid var(--bs-primary);
    font-weight: 600;
}

.phone-list-tabs .nav-link i {
    font-size: 1.1rem;
}

.phone-list-tabs .badge-tab {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    font-weight: 600;
    border-radius: 12px;
}

/* Responsive: Tablet (1200px and below) */
@media (max-width: 1200px) {
    .phone-list-tabs .nav-link {
        font-size: 0.85rem;
        padding: 0.6rem 0.8rem;
    }
    
    .phone-list-tabs .tab-text {
        font-size: 0.8rem;
    }
    
    .phone-list-tabs .nav-link i {
        font-size: 1rem;
    }
}

/* Responsive: Mobile (768px and below) */
@media (max-width: 768px) {
    .phone-list-tabs {
        padding: 0.25rem 0;
    }
    
    .phone-list-tabs .nav-item {
        min-width: 80px;
    }
    
    .phone-list-tabs .nav-link {
        padding: 0.5rem 0.6rem;
        min-height: 65px;
        flex-direction: column;
        gap: 0.15rem;
    }
    
    .phone-list-tabs .nav-link i {
        font-size: 1.2rem;
        margin: 0;
    }
    
    .phone-list-tabs .tab-text {
        font-size: 0.7rem;
        line-height: 1.2;
        text-align: center;
    }
    
    .phone-list-tabs .badge-tab {
        font-size: 0.65rem;
        padding: 0.15rem 0.35rem;
        margin: 0;
    }
}

/* Responsive: Small Mobile (576px and below) */
@media (max-width: 576px) {
    .phone-list-tabs .nav-item {
        min-width: 70px;
    }
    
    .phone-list-tabs .nav-link {
        padding: 0.4rem 0.5rem;
    }
    
    .phone-list-tabs .nav-link i {
        font-size: 1.1rem;
    }
    
    .phone-list-tabs .tab-text {
        font-size: 0.65rem;
    }
}

/* Message Preview Styling */
.message-preview {
    background-color: #f8f9fa;
    color: #212529;
    border-color: #dee2e6 !important;
}

/* Message Text Styling */
.message-text {
    background-color: #f8f9fa !important;
    color: #212529 !important;
    white-space: pre-wrap;
}

/* Dark Mode Support */
.admin-dark .message-preview {
    background-color: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}

.admin-dark .message-preview .text-muted {
    color: #94a3b8 !important;
}

.admin-dark .message-text {
    background-color: #1e293b !important;
    color: #e5e7eb !important;
}

[data-bs-theme="dark"] .message-text {
    background-color: rgba(30, 41, 59, 0.5) !important;
    color: #e5e7eb !important;
}
</style>
<script>
let selectedPhones = [];

// View messages for pendaftar
function viewMessages(pendaftarId) {
    const modal = new bootstrap.Modal(document.getElementById('messagesModal'));
    modal.show();
    
    // Load messages
    fetch(`/whatsapp/pendaftar/${pendaftarId}/messages`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.pendaftar, data.messages, data.statistics);
            } else {
                document.getElementById('messagesContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${data.message || 'Gagal memuat data'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('messagesContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Terjadi kesalahan saat memuat data
                </div>
            `;
        });
}

// Display messages
function displayMessages(pendaftar, messages, statistics) {
    let html = `
        <div class="mb-3 pb-3 border-bottom">
            <div class="row">
                <div class="col-md-6">
                    <strong>Nama:</strong> ${pendaftar.nama_lengkap}<br>
                    <strong>No. Registrasi:</strong> ${pendaftar.no_registrasi}<br>
                    <strong>NISN:</strong> ${pendaftar.nisn || '-'}
                </div>
                <div class="col-md-6">
                    <strong>Jurusan:</strong> ${pendaftar.jurusan}<br>
                    <strong>Nomor HP:</strong> ${pendaftar.phone || '-'}<br>
                    <strong>Total Pesan:</strong> ${messages.length}
                </div>
            </div>
        </div>
    `;
    
    // Add statistics if available
    if (statistics) {
        html += `
            <div class="alert alert-info mb-3">
                <div class="row text-center">
                    <div class="col-3">
                        <h5 class="mb-0">${statistics.total}</h5>
                        <small>Total</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0">🏫 ${statistics.spmb}</h5>
                        <small>SPMB</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0">📤 ${statistics.external}</h5>
                        <small>Eksternal</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0 text-success">${statistics.sent}</h5>
                        <small>Terkirim</small>
                    </div>
                </div>
            </div>
        `;
    }
    
    if (messages.length === 0) {
        html += `
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>Belum ada riwayat pesan</p>
            </div>
        `;
    } else {
        html += '<div class="messages-list">';
        messages.forEach((msg, index) => {
            const statusBadge = msg.status === 'sent' ? 'success' : (msg.status === 'failed' ? 'danger' : 'warning');
            const statusIcon = msg.status === 'sent' ? 'check-circle' : (msg.status === 'failed' ? 'times-circle' : 'clock');
            const statusText = msg.status === 'sent' ? 'Terkirim' : (msg.status === 'failed' ? 'Gagal' : 'Pending');
            
            // Source badge
            const sourceBadge = msg.source_badge || (msg.source === 'external' ? '📤 Eksternal' : '🏫 SPMB');
            const sourceBadgeColor = msg.source === 'external' ? 'warning' : 'primary';
            
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-${statusBadge}">
                                    <i class="fas fa-${statusIcon} me-1"></i>${statusText}
                                </span>
                                <span class="badge bg-${sourceBadgeColor} ms-2">${sourceBadge}</span>
                                ${msg.template ? `<span class="badge bg-info ms-2">${msg.template}</span>` : ''}
                                ${msg.batch_name ? `<span class="badge bg-secondary ms-2" title="Batch">${msg.batch_name}</span>` : ''}
                            </div>
                            <small class="text-muted">${msg.date}</small>
                        </div>
                        <div class="message-text p-3 rounded">
                            ${msg.message || '-'}
                        </div>
                        ${msg.error_message ? `
                            <div class="alert alert-danger mt-2 mb-0">
                                <small><i class="fas fa-exclamation-triangle me-1"></i>${msg.error_message}</small>
                            </div>
                        ` : ''}
                        ${msg.sent_by ? `
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>Dikirim oleh: ${msg.sent_by}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }
    
    document.getElementById('messagesContent').innerHTML = html;
}

// Toggle select all
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.phone-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

// Update selected count
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.phone-checkbox:checked');
    selectedPhones = Array.from(checkboxes).map(cb => ({
        phone: cb.value,
        id: cb.dataset.id,
        name: cb.dataset.name || '-',
        no_reg: cb.dataset.noReg || '-',
        jurusan: cb.dataset.jurusan || '-',
        nisn: cb.dataset.nisn || '-',
        asal_sekolah: cb.dataset.asalSekolah || '-'
    }));
    
    // Null checks for elements that might not exist in all tabs
    const selectedCountEl = document.getElementById('selectedCount');
    const selectAllEl = document.getElementById('selectAll');
    
    if (selectedCountEl) {
        selectedCountEl.textContent = selectedPhones.length;
    }
    
    if (selectAllEl) {
        selectAllEl.checked = checkboxes.length > 0 && checkboxes.length === document.querySelectorAll('.phone-checkbox').length;
    }
}

// Character counter
document.getElementById('broadcastMessage')?.addEventListener('input', function() {
    document.getElementById('broadcastCharCount').textContent = this.value.length;
    updatePreview();
});

// Load template
function loadTemplate() {
    const select = document.getElementById('broadcastTemplate');
    const option = select.options[select.selectedIndex];
    const message = option.dataset.message;
    const variables = JSON.parse(option.dataset.variables || '[]');
    
    if (message) {
        document.getElementById('broadcastMessage').value = message;
        document.getElementById('broadcastCharCount').textContent = message.length;
        
        if (variables.length > 0) {
            document.getElementById('templateVariables').style.display = 'block';
        } else {
            document.getElementById('templateVariables').style.display = 'none';
        }
        
        updatePreview();
    } else {
        document.getElementById('broadcastMessage').value = '';
        document.getElementById('broadcastCharCount').textContent = '0';
        document.getElementById('templateVariables').style.display = 'none';
        updatePreview();
    }
}

// Update preview
function updatePreview() {
    const message = document.getElementById('broadcastMessage').value;
    const preview = document.getElementById('broadcastPreview');
    
    if (message) {
        // Replace common variables with example
        let previewText = message
            .replace(/{nama}/g, '[Nama Pendaftar]')
            .replace(/{no_pendaftaran}/g, '[No. Pendaftaran]')
            .replace(/{jurusan}/g, '[Jurusan]')
            .replace(/{gelombang}/g, '[Gelombang]')
            .replace(/{portal_url}/g, '{{ url("/") }}')
            .replace(/{sekolah}/g, '{{ config("app.name") }}')
            .replace(/{tanggal}/g, '[Tanggal]');
        
        preview.textContent = previewText;
    } else {
        preview.innerHTML = '<span class="text-muted">Preview akan muncul di sini...</span>';
    }
}

// Send broadcast
function sendBroadcast() {
    if (selectedPhones.length === 0) {
        // Sweet alert style
        showAlert('error', 'Tidak Ada Penerima', 'Pilih minimal 1 nomor HP untuk broadcast');
        return;
    }
    
    document.getElementById('recipientCount').textContent = selectedPhones.length;
    const modal = new bootstrap.Modal(document.getElementById('broadcastModal'));
    modal.show();
}

// Submit broadcast
function submitBroadcast() {
    const message = document.getElementById('broadcastMessage').value;
    const templateId = document.getElementById('broadcastTemplate').value;
    
    if (!message) {
        showAlert('warning', 'Pesan Kosong', 'Pesan broadcast tidak boleh kosong');
        return;
    }
    
    if (selectedPhones.length === 0) {
        showAlert('error', 'Tidak Ada Penerima', 'Tidak ada nomor HP yang dipilih');
        return;
    }
    
    // Show custom confirm dialog
    showConfirmDialog(
        'Kirim Broadcast?',
        `Kirim broadcast ke <strong>${selectedPhones.length} nomor HP</strong>?<br><small class="text-muted">Proses ini tidak dapat dibatalkan</small>`,
        () => {
            // On confirm
            executeBroadcast(message, templateId);
        }
    );
}

// Execute broadcast (separated for cleaner code)
function executeBroadcast(message, templateId) {
    // Close broadcast modal
    bootstrap.Modal.getInstance(document.getElementById('broadcastModal')).hide();
    
    // Show progress modal
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
    progressModal.show();
    
    // Initialize progress
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('progressBar').textContent = '0%';
    document.getElementById('progressText').textContent = '0 / ' + selectedPhones.length;
    document.getElementById('successCount').textContent = '0';
    document.getElementById('failedCount').textContent = '0';
    
    // Prepare data
    const data = {
        phones: selectedPhones,
        message: message,
        template_id: templateId || null,
        _token: '{{ csrf_token() }}'
    };
    
    // Send request
    fetch('{{ route("whatsapp.broadcast.send-bulk") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update progress to 100%
            updateBroadcastProgress(selectedPhones.length, selectedPhones.length, data.success_count, data.failed_count);
            
            // Hide progress modal and show result after short delay
            setTimeout(() => {
                progressModal.hide();
                showBroadcastResult(data);
                
                // Reset form
                document.getElementById('broadcastMessage').value = '';
                document.getElementById('broadcastTemplate').value = '';
                document.querySelectorAll('.phone-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
                
                // Reload page to refresh status
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }, 1000);
        } else {
            progressModal.hide();
            showAlert('error', 'Broadcast Gagal', data.message || 'Terjadi kesalahan saat mengirim broadcast');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        progressModal.hide();
        showAlert('error', 'Terjadi Kesalahan', error.message || 'Gagal menghubungi server');
    });
}

// Helper: Show alert (replaces alert())
function showAlert(type, title, message) {
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const colorMap = {
        success: 'success',
        error: 'danger',
        warning: 'warning',
        info: 'info'
    };
    
    const icon = iconMap[type] || 'fa-info-circle';
    const color = colorMap[type] || 'info';
    
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="alertModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="fas ${icon} fa-3x text-${color} mb-3"></i>
                        <h5 class="mb-2">${title}</h5>
                        <p class="text-muted mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-${color}" data-bs-dismiss="modal">Oke</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing alert modal if any
    const existingModal = document.getElementById('alertModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
    alertModal.show();
    
    // Remove from DOM after hidden
    document.getElementById('alertModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Helper: Show confirm dialog (replaces confirm())
function showConfirmDialog(title, message, onConfirm, onCancel) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                        <h5 class="mb-2">${title}</h5>
                        <p class="text-muted mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirmCancel">Batal</button>
                        <button type="button" class="btn btn-primary" id="confirmOk">Oke</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing confirm modal if any
    const existingModal = document.getElementById('confirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    confirmModal.show();
    
    // Handle confirm
    document.getElementById('confirmOk').addEventListener('click', function() {
        confirmModal.hide();
        if (onConfirm) onConfirm();
    });
    
    // Handle cancel
    if (onCancel) {
        document.getElementById('confirmCancel').addEventListener('click', function() {
            onCancel();
        });
    }
    
    // Remove from DOM after hidden
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Update broadcast progress
function updateBroadcastProgress(current, total, success, failed) {
    const percent = Math.round((current / total) * 100);
    
    document.getElementById('progressBar').style.width = percent + '%';
    document.getElementById('progressBar').textContent = percent + '%';
    document.getElementById('progressText').textContent = current + ' / ' + total;
    document.getElementById('successCount').textContent = success;
    document.getElementById('failedCount').textContent = failed;
}

// Show broadcast result
function showBroadcastResult(data) {
    // ============================================
    // HYBRID RESULT DISPLAY
    // ============================================
    
    if (data.method === 'detail_feedback') {
        // Method A: Show detailed results (≤30 phones)
        showDetailedBroadcastResult(data);
    } else if (data.method === 'bulk') {
        // Method B: Show bulk summary (>30 phones)
        showBulkBroadcastSummary(data);
    } else {
        // Fallback: Original display
        showOriginalBroadcastResult(data);
    }
}

// Method A: Detailed results for ≤30 phones
function showDetailedBroadcastResult(data) {
    document.getElementById('resultTotal').textContent = data.total;
    document.getElementById('resultSuccess').textContent = data.success_count;
    document.getElementById('resultFailed').textContent = data.failed_count;
    
    // Build detailed list
    let detailsHtml = '';
    if (data.results && data.results.length > 0) {
        detailsHtml = '<div class="alert alert-info mb-3">';
        detailsHtml += '<i class="fas fa-info-circle me-2"></i>';
        detailsHtml += '<strong>Detail Feedback:</strong> ' + data.note;
        detailsHtml += '</div>';
        
        detailsHtml += '<div class="list-group">';
        data.results.forEach(result => {
            const iconClass = result.success ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const statusText = result.success ? 'Terkirim' : 'Gagal';
            const errorMsg = result.message && !result.success ? `<br><small class="text-muted">${result.message}</small>` : '';
            
            detailsHtml += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <i class="fas ${iconClass} me-2"></i>
                            <strong>${result.name}</strong>
                            <span class="text-muted ms-2">(${result.no_reg})</span>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>${result.phone}
                                <span class="ms-2"><i class="fas fa-graduation-cap me-1"></i>${result.jurusan}</span>
                            </small>
                            ${errorMsg}
                        </div>
                        <span class="badge ${result.success ? 'bg-success' : 'bg-danger'}">${statusText}</span>
                    </div>
                </div>
            `;
        });
        detailsHtml += '</div>';
    }
    
    document.getElementById('resultDetails').innerHTML = detailsHtml;
    
    // Show result modal
    const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
    resultModal.show();
}

// Method B: Bulk summary for >30 phones
function showBulkBroadcastSummary(data) {
    // Use different modal or modify existing
    const modalHtml = `
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane me-2"></i>Broadcast Dalam Proses
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h5 class="alert-heading">
                        <i class="fas fa-clock me-2"></i>Proses Background
                    </h5>
                    <p class="mb-0">Broadcast untuk <strong>${data.total} nomor</strong> sedang diproses di background.</p>
                </div>
                
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h3 class="text-primary mb-0">${data.total}</h3>
                                <small class="text-muted">Total Nomor</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h3 class="text-success mb-0">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </h3>
                                <small class="text-muted">Sedang Kirim</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h3 class="text-info mb-0">
                                    <i class="fas fa-clock"></i>
                                </h3>
                                <small class="text-muted">Estimasi ~${Math.ceil(data.total / 10)} menit</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> ${data.note}
                </div>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('whatsapp.logs') }}" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Lihat Log WhatsApp
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Create temporary modal or use existing
    const resultModalEl = document.getElementById('resultModal');
    const originalContent = resultModalEl.querySelector('.modal-content').innerHTML;
    
    resultModalEl.querySelector('.modal-content').innerHTML = modalHtml;
    
    const resultModal = new bootstrap.Modal(resultModalEl);
    resultModal.show();
    
    // Restore original content when modal closes
    resultModalEl.addEventListener('hidden.bs.modal', function() {
        resultModalEl.querySelector('.modal-content').innerHTML = originalContent;
    }, { once: true });
}

// Fallback: Original display
function showOriginalBroadcastResult(data) {
    document.getElementById('resultTotal').textContent = data.total;
    document.getElementById('resultSuccess').textContent = data.success_count;
    document.getElementById('resultFailed').textContent = data.failed_count;
    
    // Build details
    let detailsHtml = '';
    if (data.results && data.results.length > 0) {
        detailsHtml = '<div class="list-group">';
        data.results.forEach(result => {
            const iconClass = result.success ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const statusText = result.success ? 'Terkirim' : 'Gagal';
            const errorMsg = result.message && !result.success ? `<br><small class="text-muted">${result.message}</small>` : '';
            
            detailsHtml += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <i class="fas ${iconClass} me-2"></i>
                            <strong>${result.name}</strong>
                            <br><small class="text-muted">${result.phone}</small>
                            ${errorMsg}
                        </div>
                        <span class="badge ${result.success ? 'bg-success' : 'bg-danger'}">${statusText}</span>
                    </div>
                </div>
            `;
        });
        detailsHtml += '</div>';
    }
    
    document.getElementById('resultDetails').innerHTML = detailsHtml;
    
    // Show result modal
    const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
    resultModal.show();
}

// Export phones
function exportPhones() {
    const checkboxes = document.querySelectorAll('.phone-checkbox:checked');
    
    if (checkboxes.length === 0) {
        showAlert('warning', 'Tidak Ada Data', 'Pilih minimal 1 nomor HP untuk export');
        return;
    }
    
    const phones = Array.from(checkboxes).map(cb => ({
        no_registrasi: cb.dataset.noReg,
        nama: cb.dataset.name,
        jurusan: cb.dataset.jurusan,
        phone: cb.value
    }));
    
    // Create CSV
    let csv = 'No. Pendaftaran,Nama,Jurusan,Nomor HP\n';
    phones.forEach(p => {
        csv += `"${p.no_registrasi}","${p.nama}","${p.jurusan}","${p.phone}"\n`;
    });
    
    // Download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'nomor-hp-pendaftar-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
    
    // Show success message
    showAlert('success', 'Export Berhasil', `${phones.length} nomor HP berhasil di-export ke file CSV`);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    
    // Fix modal focus issue - Remove focus from close button before hiding
    const messagesModal = document.getElementById('messagesModal');
    const broadcastModal = document.getElementById('broadcastModal');
    
    if (messagesModal) {
        messagesModal.addEventListener('hide.bs.modal', function() {
            // Remove focus from any focused element inside modal
            const focusedElement = messagesModal.querySelector(':focus');
            if (focusedElement) {
                focusedElement.blur();
            }
        });
        
        // Clean up backdrop when modal is fully hidden
        messagesModal.addEventListener('hidden.bs.modal', function() {
            // Remove any leftover backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body classes
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
    
    if (broadcastModal) {
        broadcastModal.addEventListener('hide.bs.modal', function() {
            // Remove focus from any focused element inside modal
            const focusedElement = broadcastModal.querySelector(':focus');
            if (focusedElement) {
                focusedElement.blur();
            }
        });
        
        // Clean up backdrop when modal is fully hidden
        broadcastModal.addEventListener('hidden.bs.modal', function() {
            // Remove any leftover backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body classes
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
    
    // Auto-search with debounce
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            // Show loading indicator
            searchInput.style.backgroundImage = 'linear-gradient(to right, #e9ecef 0%, #dee2e6 50%, #e9ecef 100%)';
            searchInput.style.backgroundSize = '200% 100%';
            searchInput.style.animation = 'shimmer 1s infinite';
            
            searchTimeout = setTimeout(function() {
                // Remove loading indicator
                searchInput.style.backgroundImage = '';
                searchInput.style.animation = '';
                
                // Submit form
                searchForm.submit();
            }, 500); // Wait 500ms after user stops typing
        });
    }
});

// Add shimmer animation for loading effect
const style = document.createElement('style');
style.textContent = `
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
`;
document.head.appendChild(style);

// ===== EXTERNAL TAB FUNCTIONS (Tasks 8.3, 8.4) =====

// Task 8.3: Toggle duplicate filter
function toggleDuplicateFilter(checkbox) {
    const url = new URL(window.location.href);
    if (checkbox.checked) {
        url.searchParams.set('show_duplicates_only', 'true');
    } else {
        url.searchParams.delete('show_duplicates_only');
    }
    window.location.href = url.toString();
}

// Task 8.4: View external messages
function viewExternalMessages(recipientId) {
    const modal = new bootstrap.Modal(document.getElementById('messagesModal'));
    modal.show();
    
    // Load messages
    fetch(`/whatsapp/external/${recipientId}/messages`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayExternalMessages(data.recipient, data.messages);
            } else {
                document.getElementById('messagesContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${data.message || 'Gagal memuat data'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('messagesContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Terjadi kesalahan saat memuat data
                </div>
            `;
        });
}

// Display external messages
function displayExternalMessages(recipient, messages) {
    let html = `
        <div class="mb-3 pb-3 border-bottom">
            <div class="row">
                <div class="col-md-6">
                    <strong>Nama:</strong> ${recipient.name}<br>
                    <strong>Nomor HP:</strong> ${recipient.phone}<br>
                    <strong>Batch:</strong> ${recipient.batch_name}
                </div>
                <div class="col-md-6">
                    <strong>Notes:</strong> ${recipient.notes || '-'}<br>
                    <strong>Status:</strong> ${recipient.is_duplicate_spmb 
                        ? '<span class="badge bg-warning text-dark">🔄 Duplikat SPMB</span>' 
                        : '<span class="badge bg-success">✓ Unik</span>'}<br>
                    <strong>Total Pesan:</strong> ${messages.length}
                </div>
            </div>
            ${recipient.matched_pendaftar ? `
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-link me-2"></i>
                    <strong>Duplikat dengan:</strong> 
                    <a href="javascript:void(0)" onclick="viewMessages(${recipient.matched_pendaftar.id})" class="text-primary">
                        ${recipient.matched_pendaftar.nama} (${recipient.matched_pendaftar.no_registrasi})
                    </a>
                </div>
            ` : ''}
        </div>
    `;
    
    if (messages.length === 0) {
        html += `
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>Belum ada riwayat pesan</p>
            </div>
        `;
    } else {
        html += '<div class="messages-list">';
        messages.forEach((msg) => {
            const statusBadge = msg.status === 'sent' ? 'success' : (msg.status === 'failed' ? 'danger' : 'warning');
            const statusIcon = msg.status === 'sent' ? 'check-circle' : (msg.status === 'failed' ? 'times-circle' : 'clock');
            const statusText = msg.status === 'sent' ? 'Terkirim' : (msg.status === 'failed' ? 'Gagal' : 'Pending');
            
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-${statusBadge}">
                                    <i class="fas fa-${statusIcon} me-1"></i>${statusText}
                                </span>
                                ${msg.template ? `<span class="badge bg-info ms-2">${msg.template}</span>` : ''}
                                <span class="badge bg-secondary ms-2">Eksternal</span>
                            </div>
                            <small class="text-muted">${msg.date}</small>
                        </div>
                        <div class="message-text p-3 rounded" style="background-color: var(--bg-secondary); white-space: pre-wrap;">
                            ${msg.message || '-'}
                        </div>
                        ${msg.error_message ? `
                            <div class="alert alert-danger mt-2 mb-0">
                                <small><i class="fas fa-exclamation-triangle me-1"></i>${msg.error_message}</small>
                            </div>
                        ` : ''}
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>Dikirim oleh: ${msg.sent_by}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }
    
    document.getElementById('messagesContent').innerHTML = html;
}
</script>
@endpush
@endsection
