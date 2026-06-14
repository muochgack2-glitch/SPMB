@extends('layouts.admin')

@section('title', 'Buat Tahun Pelajaran Baru')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="h3 mb-0" style="color: var(--text-primary);">📅 Buat Tahun Pelajaran Baru</h1>
        <p class="small mb-0" style="color: var(--text-secondary);">Wizard untuk membuat tahun pelajaran dengan nomor registrasi otomatis</p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Gagal!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Form Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Tahun Pelajaran</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}" id="createForm">
                        @csrf

                        <!-- Tahun Ajaran -->
                        <div class="mb-4">
                            <label for="tahun" class="form-label fw-bold">
                                Tahun Pelajaran <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('tahun') is-invalid @enderror" 
                                   id="tahun" 
                                   name="tahun" 
                                   value="{{ old('tahun', $suggestedYear) }}" 
                                   placeholder="2027/2028"
                                   pattern="\d{4}/\d{4}"
                                   required>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Format: YYYY/YYYY (contoh: 2027/2028)
                            </div>
                            @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Periode -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="started_at" class="form-label fw-bold">
                                    Periode Mulai
                                </label>
                                <input type="date" 
                                       class="form-control @error('started_at') is-invalid @enderror" 
                                       id="started_at" 
                                       name="started_at" 
                                       value="{{ old('started_at', date('Y-m-d')) }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Kosongkan untuk set hari ini
                                </div>
                                @error('started_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="closed_at" class="form-label fw-bold">
                                    Periode Selesai (Opsional)
                                </label>
                                <input type="date" 
                                       class="form-control @error('closed_at') is-invalid @enderror" 
                                       id="closed_at" 
                                       name="closed_at" 
                                       value="{{ old('closed_at') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Kosongkan jika belum selesai
                                </div>
                                @error('closed_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Archive Current Option -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="archive_current" 
                                       name="archive_current" 
                                       value="1"
                                       {{ old('archive_current', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="archive_current">
                                    <strong>Arsipkan tahun pelajaran saat ini</strong>
                                    @if($activeTA)
                                    <br><small style="color: var(--text-secondary);">Tahun pelajaran {{ $activeTA->tahun }} akan diarsipkan</small>
                                    @endif
                                </label>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Apa yang Akan Terjadi?</h6>
                            <ul class="mb-0 ps-3">
                                <li>Database akan di-backup otomatis</li>
                                <li>Tahun pelajaran baru akan dibuat dengan status "Aktif"</li>
                                <li>Counter nomor registrasi reset ke 0</li>
                                <li>Nomor registrasi dimulai dari <strong id="previewNoReg">SPMB-XXXX-0001</strong></li>
                                @if($activeTA)
                                <li>Tahun {{ $activeTA->tahun }} akan diarsipkan (data tetap aman)</li>
                                @endif
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="button" class="btn btn-primary btn-lg" onclick="confirmCreate()">
                                <i class="fas fa-plus-circle"></i> Buat Tahun Pelajaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Current Active Year -->
            @if($activeTA)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Tahun Pelajaran Aktif</h6>
                </div>
                <div class="card-body">
                    <h4 class="mb-2">{{ $activeTA->tahun }}</h4>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="mb-0 text-primary">{{ number_format($activeTA->total_pendaftar) }}</h5>
                                <small style="color: var(--text-secondary);">Pendaftar</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-0 text-success">{{ number_format($activeTA->reg_number_current) }}</h5>
                            <small style="color: var(--text-secondary);">Counter</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card shadow-sm">
                <div class="card-header" style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
                    <h6 class="mb-0" style="color: var(--text-primary);"><i class="fas fa-question-circle me-2"></i>Panduan</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold" style="color: var(--text-primary);">Format Nomor Registrasi</h6>
                    <p class="small mb-3" style="color: var(--text-secondary);">
                        Sistem akan generate nomor dengan format:<br>
                        <code style="color: var(--text-primary); background: var(--bg-tertiary);">SPMB-{TAHUN}-{NOMOR:4}</code>
                    </p>

                    <h6 class="fw-bold" style="color: var(--text-primary);">Contoh:</h6>
                    <ul class="small ps-3 mb-3" style="color: var(--text-secondary);">
                        <li>2026/2027 → <code style="color: var(--text-primary); background: var(--bg-tertiary);">SPMB-2026-0001</code></li>
                        <li>2026/2027 → <code style="color: var(--text-primary); background: var(--bg-tertiary);">SPMB-2026-0002</code></li>
                        <li>2027/2028 → <code style="color: var(--text-primary); background: var(--bg-tertiary);">SPMB-2027-0001</code> (reset)</li>
                    </ul>

                    <h6 class="fw-bold" style="color: var(--text-primary);">Keamanan Data</h6>
                    <ul class="small ps-3 mb-0" style="color: var(--text-secondary);">
                        <li>Data lama <strong>tidak akan dihapus</strong></li>
                        <li>Backup otomatis sebelum create</li>
                        <li>Historical data tetap bisa diakses</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Pembuatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">Anda akan membuat tahun pelajaran baru:</h6>
                <div class="alert alert-secondary" style="background-color: var(--bg-secondary); border-color: var(--border-light);">
                    <h4 class="mb-0 text-center" id="confirmTahun"></h4>
                </div>

                <h6 class="fw-bold mt-3">Yang Akan Dilakukan:</h6>
                <ul class="mb-0">
                    <li>✅ Backup database otomatis</li>
                    <li>✅ Buat tahun pelajaran baru (status: Aktif)</li>
                    <li>✅ Reset counter nomor registrasi ke 0</li>
                    @if($activeTA)
                    <li>📦 Arsipkan tahun {{ $activeTA->tahun }}</li>
                    @endif
                </ul>

                <div class="alert alert-info mt-3 mb-0" style="background-color: rgba(13, 202, 240, 0.1); border-color: var(--border-light); color: var(--text-primary);">
                    <small><i class="fas fa-shield-alt me-2"></i>Data existing tetap aman dan bisa diakses kapan saja</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-warning" onclick="submitForm()">
                    <i class="fas fa-check"></i> Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-update preview nomor registrasi
document.getElementById('tahun').addEventListener('input', function() {
    const tahun = this.value;
    const match = tahun.match(/(\d{4})\/\d{4}/);
    if (match) {
        const year = match[1];
        document.getElementById('previewNoReg').textContent = `SPMB-${year}-0001`;
    } else {
        document.getElementById('previewNoReg').textContent = 'SPMB-XXXX-0001';
    }
});

// Initial preview
document.getElementById('tahun').dispatchEvent(new Event('input'));

function confirmCreate() {
    const tahun = document.getElementById('tahun').value;
    
    // Validate format
    if (!/^\d{4}\/\d{4}$/.test(tahun)) {
        alert('❌ Format tahun pelajaran tidak valid!\n\nGunakan format: YYYY/YYYY (contoh: 2027/2028)');
        return;
    }
    
    // Validate sequence
    const parts = tahun.split('/');
    const year1 = parseInt(parts[0]);
    const year2 = parseInt(parts[1]);
    
    if (year2 !== year1 + 1) {
        alert('❌ Tahun kedua harus berurutan setelah tahun pertama!\n\nContoh yang benar: 2027/2028');
        return;
    }
    
    document.getElementById('confirmTahun').textContent = tahun;
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function submitForm() {
    document.getElementById('createForm').submit();
}
</script>
@endpush
