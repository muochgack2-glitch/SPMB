@extends('layouts.admin')

@section('title', 'Data Pendaftar - SPMB (Sistem Penerimaan Murid Baru)')

@push('styles')
<style>
    .dashboard-content {
        animation: zoomFadeIn 0.35s ease-out;
    }

    @keyframes zoomFadeIn {
        from {
            opacity: 0;
            transform: scale(0.97);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    #pendaftarTable thead th {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-secondary);
        white-space: nowrap;
    }
    #pendaftarTable tbody td {
        vertical-align: middle;
        font-size: 13px;
    }
    #pendaftarTable tbody tr:hover {
        background: var(--bg-secondary);
    }
    .reg-code {
        font-weight: 800;
        color: var(--primary);
        font-variant-numeric: tabular-nums;
    }
    .jurusan-pill,
    .size-pill,
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
    }
    .jurusan-pill { background: #e0e7ff; color: #3730a3; }
    .size-pill { background: #e0f2fe; color: #075985; }
    .status-red {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    .status-green {
        background-color: #dcfce7;
        color: #166534;
    }
    
    /* Dark Mode Support */
    .admin-dark .jurusan-pill {
        background: rgba(129, 140, 248, 0.2) !important;
        color: #a5b4fc !important;
    }
    .admin-dark .size-pill {
        background: rgba(56, 189, 248, 0.2) !important;
        color: #7dd3fc !important;
    }
    .admin-dark .status-red {
        background-color: rgba(239, 68, 68, 0.2) !important;
        color: #fca5a5 !important;
    }
    .admin-dark .status-green {
        background-color: rgba(34, 197, 94, 0.2) !important;
        color: #86efac !important;
    }
    .admin-dark #bulkActionsBar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    .admin-dark .empty-state {
        color: #94a3b8 !important;
    }
    
    /* Fix pagination button size */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        min-width: 38px;
        text-align: center;
    }
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pagination .page-item.active .page-link {
        z-index: 3;
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
@php
    // Get all statistics from database (FILTERED BY SELECTED YEAR)
    $totalPendaftar = \App\Models\Pendaftar::where('tahun_ajaran', $selectedTahun)->count();
    $totalDiterima = \App\Models\Pendaftar::where('tahun_ajaran', $selectedTahun)->where('status_siswa', 'Diterima')->count();
    $totalBelumDaftarUlang = \App\Models\Pendaftar::where('tahun_ajaran', $selectedTahun)->where('status_siswa', '!=', 'Diterima')->count();
    $totalDataAwal = \App\Models\Pendaftar::where('tahun_ajaran', $selectedTahun)->where('status_data', 'awal')->count();
@endphp

<div class="dashboard-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-2">
                Data Pendaftar
                @if($selectedTahun !== $activeTahun)
                    <span class="badge bg-secondary ms-2">Tahun {{ $selectedTahun }}</span>
                @else
                    <span class="badge bg-success ms-2">Tahun Aktif: {{ $activeTahun }}</span>
                @endif
            </h2>
            <p class="text-muted mb-0">
                Kelola daftar calon siswa, status pendaftaran, dan akses cepat ke dokumen penting.
                @if($selectedTahun !== $activeTahun)
                    <a href="{{ route('pendaftar.index') }}" class="text-decoration-none ms-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke tahun aktif
                    </a>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-2"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('pendaftar.export.excel') }}">
                            <i class="fas fa-file-excel text-success me-2"></i> Export ke Excel (.xlsx)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('pendaftar.export.pdf') }}" target="_blank">
                            <i class="fas fa-file-pdf text-danger me-2"></i> Export ke PDF
                        </a>
                    </li>
                </ul>
            </div>
            <x-button variant="primary" icon="fas fa-plus" href="{{ route('pendaftar.create') }}">
                Tambah Pendaftar
            </x-button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <x-stat-card 
                icon="fas fa-users" 
                label="Total Pendaftar" 
                value="{{ $totalPendaftar }}"
                color="blue"
                description="Data pada halaman ini"
            />
        </div>
        <div class="col-md-3 col-sm-6">
            <x-stat-card 
                icon="fas fa-hourglass-half" 
                label="Belum Daftar Ulang" 
                value="{{ $totalBelumDaftarUlang }}"
                color="red"
                description="Perlu ditindaklanjuti"
            />
        </div>
        <div class="col-md-3 col-sm-6">
            <x-stat-card 
                icon="fas fa-check-circle" 
                label="Diterima" 
                value="{{ $totalDiterima }}"
                color="green"
                description="Sudah diverifikasi"
            />
        </div>
        <div class="col-md-3 col-sm-6">
            <x-stat-card 
                icon="fas fa-clipboard-list" 
                label="Data Awal" 
                value="{{ $totalDataAwal }}"
                color="yellow"
                description="Perlu biodata lengkap"
            />
        </div>
    </div>

    @if (Session::has('success'))
        <x-alert type="success" dismissible="true">
            {{ Session::get('success') }}
            
            @if (Session::has('wa_sent'))
                <div class="mt-2 pt-2 border-top border-success-subtle">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    <strong>WhatsApp berhasil dikirim</strong> ke 
                    @if (Session::has('wa_phone_type'))
                        <span class="badge bg-info">{{ Session::get('wa_phone_type') }}</span>
                    @endif
                    <code>{{ Session::get('wa_phone') }}</code>
                </div>
            @endif
            
            @if (Session::has('wa_failed'))
                <div class="mt-2 pt-2 border-top border-warning-subtle">
                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                    <strong>WhatsApp gagal dikirim:</strong> {{ Session::get('wa_error') }}
                </div>
            @endif
        </x-alert>
    @endif

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pendaftar.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">
                            <i class="fas fa-search me-1"></i> Cari
                        </label>
                        <input type="text" name="search" class="form-control" placeholder="Nama, No. Reg, NISN..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">
                            <i class="fas fa-graduation-cap me-1"></i> Jurusan
                        </label>
                        <select name="jurusan" class="form-select">
                            <option value="">Semua Jurusan</option>
                            @foreach(\App\Models\Jurusan::where('aktif', true)->orderBy('kode')->get() as $jur)
                            <option value="{{ $jur->kode }}" {{ request('jurusan') == $jur->kode ? 'selected' : '' }}>
                                {{ $jur->kode }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">
                            <i class="fas fa-calendar me-1"></i> Gelombang
                        </label>
                        <select name="gelombang" class="form-select">
                            <option value="">Semua Gelombang</option>
                            @foreach(\App\Models\Pendaftar::select('gelombang')->distinct()->orderBy('gelombang')->pluck('gelombang') as $gel)
                            <option value="{{ $gel }}" {{ request('gelombang') == $gel ? 'selected' : '' }}>
                                {{ $gel }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">
                            <i class="fas fa-check-circle me-1"></i> Status Siswa
                        </label>
                        <select name="status_siswa" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Diterima" {{ request('status_siswa') == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="Belum Daftar Ulang" {{ request('status_siswa') == 'Belum Daftar Ulang' ? 'selected' : '' }}>Belum Daftar Ulang</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">
                            <i class="fas fa-database me-1"></i> Status Data
                        </label>
                        <select name="status_data" class="form-select">
                            <option value="">Semua</option>
                            <option value="awal" {{ request('status_data') == 'awal' ? 'selected' : '' }}>Data Awal</option>
                            <option value="lengkap" {{ request('status_data') == 'lengkap' ? 'selected' : '' }}>Data Lengkap</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">
                            <i class="fas fa-network-wired me-1"></i> Jaringan
                        </label>
                        <select name="jaringan" class="form-select">
                            <option value="">Semua Jaringan</option>
                            @foreach(\App\Models\Pendaftar::select('nama_jaringan')->whereNotNull('nama_jaringan')->where('nama_jaringan', '!=', '')->distinct()->orderBy('nama_jaringan')->pluck('nama_jaringan') as $jar)
                            <option value="{{ $jar }}" {{ request('jaringan') == $jar ? 'selected' : '' }}>
                                {{ $jar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9 d-flex align-items-end justify-content-end gap-2">
                        @if(request()->hasAny(['search', 'jurusan', 'gelombang', 'status_siswa', 'status_data', 'jaringan']))
                        <a href="{{ route('pendaftar.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Reset Filter
                        </a>
                        @endif
                        <span class="text-muted small align-self-center">
                            {{ $pendaftars->total() }} data ditemukan
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <x-section-card title="Daftar Pendaftar" icon="fas fa-list">
        <x-slot:actions>
            <small class="text-muted">Gunakan aksi cepat untuk edit biodata, cetak bukti, atau lanjut ke formulir lengkap</small>
        </x-slot:actions>
                <div class="table-responsive">
                    <!-- Bulk Actions Bar -->
                    <div id="bulkActionsBar" style="display: none; padding: 1rem; background: var(--bg-secondary); border-radius: 0.5rem; margin-bottom: 1rem; border: 2px solid var(--border-light);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div style="color: var(--text-primary);">
                                <i class="fas fa-check-square text-primary me-2"></i>
                                <strong><span id="selectedCount">0</span> pendaftar dipilih</strong>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                                    <i class="fas fa-trash me-1"></i> Hapus Terpilih
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelection()">
                                    <i class="fas fa-times me-1"></i> Batal
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-hover" id="pendaftarTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" title="Pilih Semua">
                                </th>
                                <th style="width: 80px;">No. Registrasi</th>
                                <th>Nama Lengkap</th>
                                <th style="width: 100px;">Jurusan</th>
                                <th style="width: 100px;">Gelombang</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 120px;">Keterangan</th>
                                <th style="width: 80px;">Ukuran Kaos</th>
                                <th style="width: 220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendaftars as $p)
                                @php
                                    $logistik = $p->logistik;
                                    if ($p->status_siswa === 'Diterima') {
                                        $statusClass = 'status-green';
                                        $statusText = 'Diterima';
                                    } else {
                                        $statusClass = 'status-red';
                                        $statusText = 'Belum Daftar Ulang';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-item" value="{{ $p->id_pendaftar }}" onchange="updateBulkActionsBar()">
                                    </td>
                                    <td><span class="reg-code">{{ $p->no_registrasi }}</span></td>
                                    <td>
                                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $p->nama_lengkap }}</div>
                                        <div class="small text-muted">Status data: {{ ucfirst($p->status_data ?? 'awal') }}</div>
                                    </td>
                                    <td><span class="jurusan-pill">{{ $p->jurusan }}</span></td>
                                    <td>{{ $p->gelombang }}</td>
                                    <td>
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($logistik && $logistik->status_bayar === 'Belum')
                                            <span class="text-muted">-</span>
                                        @elseif($logistik)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" data-bs-toggle="tooltip" data-bs-placement="top" title="Kaos otomatis dipesankan setelah verifikasi daftar ulang.">Kaos dipesankan</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($logistik && $logistik->ukuran_kaos)
                                            <span class="size-pill">{{ $logistik->ukuran_kaos }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-table-actions align="start">
                                            <x-icon-button 
                                                icon="fas fa-edit" 
                                                variant="info" 
                                                size="sm"
                                                href="{{ route('pendaftar.edit', $p->id_pendaftar) }}"
                                                tooltip="Edit Biodata"
                                            />
                                            <x-icon-button 
                                                icon="fas fa-id-card" 
                                                variant="primary" 
                                                size="sm"
                                                href="{{ route('pendaftar.print.registrasi', $p->id_pendaftar) }}"
                                                target="_blank"
                                                tooltip="Cetak Bukti Registrasi"
                                            />
                                            <x-icon-button 
                                                icon="fas fa-file-lines" 
                                                variant="success" 
                                                size="sm"
                                                href="{{ route('pendaftar.print.formulir', $p->id_pendaftar) }}"
                                                target="_blank"
                                                tooltip="Cetak Formulir Lengkap"
                                            />
                                            @if(auth()->user()->role === 'administrator')
                                            <button 
                                                type="button"
                                                class="btn btn-sm btn-danger btn-delete-pendaftar" 
                                                data-id="{{ $p->id_pendaftar }}"
                                                data-nama="{{ $p->nama_lengkap }}"
                                                data-noreg="{{ $p->no_registrasi }}"
                                                data-bs-toggle="tooltip"
                                                title="Hapus Pendaftar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </x-table-actions>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted empty-state">
                                        <i class="fas fa-inbox" style="font-size: 24px;"></i>
                                        <p class="mt-2 mb-0">Belum ada data pendaftar</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-section-card>

            <!-- Pagination -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-muted small">
                            Menampilkan {{ $pendaftars->firstItem() ?? 0 }} - {{ $pendaftars->lastItem() ?? 0 }} dari {{ $pendaftars->total() }} data
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-muted small mb-0">Tampilkan:</label>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            {{-- Previous Page Link --}}
                            @if ($pendaftars->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $pendaftars->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($pendaftars->links()->elements[0] as $page => $url)
                                @if ($page == $pendaftars->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($pendaftars->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $pendaftars->nextPageUrl() }}" rel="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
</div>
@endsection

@push('scripts')
<script>
        // Change per page
        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if (Session::has('created_pendaftar_id'))
                Modal.confirm(
                    'Pendaftar <strong>{{ addslashes(Session::get('created_pendaftar_name')) }}</strong><br>No. Registrasi: <strong>{{ Session::get('created_pendaftar_no') }}</strong><br><br>Apakah ingin melengkapi biodata sekarang?',
                    function() {
                        window.location.href = '{{ route('pendaftar.edit', Session::get('created_pendaftar_id')) }}';
                    },
                    {
                        title: 'Data Sudah Dibuat',
                        confirmText: 'Edit Data Lengkap',
                        cancelText: 'Tidak, nanti saja',
                        type: 'success'
                    }
                );
            @elseif (Session::has('rollback_success'))
                Modal.alert(
                    '{!! addslashes(Session::get('success')) !!}',
                    'Rollback Berhasil',
                    'success'
                );
            @elseif (Session::has('success'))
                Modal.alert(
                    '{{ addslashes(Session::get('success')) }}',
                    'Berhasil',
                    'success'
                );
            @endif

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Select All functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-item');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionsBar();
        });

        // Update bulk actions bar
        function updateBulkActionsBar() {
            const selected = document.querySelectorAll('.select-item:checked');
            const count = selected.length;
            const selectAll = document.getElementById('selectAll');
            const totalCheckboxes = document.querySelectorAll('.select-item').length;
            
            // Update "Select All" checkbox state
            if (count === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (count === totalCheckboxes) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
            
            // Show/hide bulk actions bar
            if (count > 0) {
                document.getElementById('bulkActionsBar').style.display = 'block';
                document.getElementById('selectedCount').textContent = count;
            } else {
                document.getElementById('bulkActionsBar').style.display = 'none';
            }
        }

        // Clear selection
        function clearSelection() {
            document.querySelectorAll('.select-item').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
            updateBulkActionsBar();
        }

        // Bulk delete
        function bulkDelete() {
            const selected = Array.from(document.querySelectorAll('.select-item:checked'))
                                  .map(cb => cb.value);
            
            if (selected.length === 0) {
                Modal.alert('Tidak ada pendaftar yang dipilih', 'Peringatan', 'warning');
                return;
            }
            
            Modal.confirm(
                `Yakin ingin menghapus <strong>${selected.length} pendaftar</strong>?<br><small class="text-muted">Data yang dihapus tidak dapat dikembalikan.</small>`,
                function() {
                    // Show loading
                    const bulkActionsBar = document.getElementById('bulkActionsBar');
                    bulkActionsBar.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Menghapus...</div>';
                    
                    // Send request to server
                    fetch('{{ route('pendaftar.bulk-delete') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ ids: selected })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Modal.alert(
                                `Berhasil menghapus ${data.count} pendaftar`,
                                'Sukses',
                                'success'
                            );
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Modal.alert(data.message || 'Terjadi kesalahan', 'Error', 'danger');
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Modal.alert('Terjadi kesalahan saat menghapus data', 'Error', 'danger');
                        location.reload();
                    });
                },
                {
                    title: 'Konfirmasi Hapus',
                    confirmText: 'Ya, Hapus',
                    cancelText: 'Batal',
                    type: 'danger'
                }
            );
        }

        // Delete single pendaftar (Administrator only)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete-pendaftar')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-delete-pendaftar');
                const id = btn.dataset.id;
                const nama = btn.dataset.nama;
                const noReg = btn.dataset.noreg;
                
                console.log('Delete button clicked:', { id, nama, noReg });
                deletePendaftar(id, nama, noReg);
            }
        });

        function deletePendaftar(id, nama, noReg) {
            console.log('deletePendaftar called with:', { id, nama, noReg });
            
            const confirmOptions = {
                title: 'Konfirmasi Hapus',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                type: 'danger'
            };
            
            console.log('Calling Modal.confirm with options:', confirmOptions);
            
            Modal.confirm(
                `Yakin ingin menghapus pendaftar:<br><br><strong>${nama}</strong><br>No. Registrasi: <strong>${noReg}</strong><br><br><small class="text-muted">Data akan di-soft delete dan bisa dipulihkan kembali melalui menu "Data Terhapus".</small>`,
                function() {
                    console.log('Confirm callback executed');
                    
                    // Get CSRF token
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfMeta) {
                        console.error('CSRF token not found!');
                        alert('Error: CSRF token tidak ditemukan. Refresh halaman dan coba lagi.');
                        return;
                    }
                    
                    // Create form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/pendaftar/${id}`;
                    
                    // Add CSRF token
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = csrfMeta.content;
                    form.appendChild(csrf);
                    
                    // Add method spoofing for DELETE
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);
                    
                    // Submit form
                    document.body.appendChild(form);
                    console.log('Submitting form:', form);
                    form.submit();
                },
                confirmOptions
            );
        }
    </script>
@endpush
