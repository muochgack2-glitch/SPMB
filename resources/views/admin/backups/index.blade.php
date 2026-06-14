@extends('layouts.admin')

@section('title', 'Backup & Restore Database')

@section('content')
<div class="container-fluid">
    <!-- Success Alert (for after restore) -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Sukses!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Perhatian!</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="admin-page-title">
                <i class="fas fa-database me-2"></i>Backup & Restore Database
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Kelola backup database dan restore system dengan aman
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.backups.google-drive.settings') }}" class="btn btn-outline-info" title="Google Drive Settings">
                    <i class="fab fa-google-drive me-2"></i>Drive Settings
                </a>
                <button class="btn btn-outline-success" id="uploadBackupBtn" title="Upload backup dari file external">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload
                </button>
                <button class="btn btn-primary" id="createBackupBtn" title="Buat backup baru dari database saat ini">
                    <i class="fas fa-plus-circle me-2"></i>Create Backup
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="fas fa-database fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Backups</h6>
                            <h3 class="mb-0">{{ $statistics['total_backups'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="fas fa-hdd fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Size</h6>
                            <h3 class="mb-0">{{ $statistics['total_size_human'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="fas fa-hand-pointer fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Manual Backups</h6>
                            <h3 class="mb-0">{{ $statistics['manual_backups'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="fas fa-robot fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Auto Backups</h6>
                            <h3 class="mb-0">{{ $statistics['auto_backups'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.backups.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Source Type</label>
                    <select name="source" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('source') == 'all' ? 'selected' : '' }}>All Sources</option>
                        <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="auto" {{ request('source') == 'auto' ? 'selected' : '' }}>Auto</option>
                        <option value="scheduled" {{ request('source') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="pre_operation" {{ request('source') == 'pre_operation' ? 'selected' : '' }}>Pre-Operation</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search filename or notes..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date</option>
                        <option value="size_bytes" {{ request('sort') == 'size_bytes' ? 'selected' : '' }}>Size</option>
                        <option value="filename" {{ request('sort') == 'filename' ? 'selected' : '' }}>Filename</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <select name="order" class="form-select" onchange="this.form.submit()">
                        <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Backup List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Backup List ({{ $backups->total() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="20%">Filename</th>
                            <th width="8%">Size</th>
                            <th width="10%">Source</th>
                            <th width="10%">Google Drive</th>
                            <th width="10%">Created</th>
                            <th width="12%">Created By</th>
                            <th width="8%">Age</th>
                            <th width="17%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                        <tr>
                            <td class="text-center">{{ $backups->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-archive text-primary me-2"></i>
                                    <div>
                                        <div class="fw-bold text-truncate" style="max-width: 250px;" title="{{ $backup->filename }}">
                                            {{ Str::limit($backup->filename, 35) }}
                                        </div>
                                        @if($backup->backup_notes)
                                        <small class="text-muted">{{ Str::limit($backup->backup_notes, 40) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $backup->size_human }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $backup->source_badge_color }}">
                                    {{ ucfirst($backup->source_type) }}
                                </span>
                            </td>
                            <td>
                                {!! $backup->google_drive_status_badge !!}
                                @if($backup->isInGoogleDrive())
                                <br>
                                <small class="text-muted">{{ $backup->uploaded_to_drive_at?->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $backup->created_at->format('d M Y') }}</small><br>
                                <small class="text-muted">{{ $backup->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold">{{ $backup->creator->name ?? 'System' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($backup->is_old)
                                <span class="badge bg-warning">{{ $backup->age_in_days }} days</span>
                                @else
                                <span class="badge bg-light text-dark">{{ $backup->age_in_days }} days</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-info" onclick="previewBackup({{ $backup->id }})" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($backup->isInGoogleDrive())
                                    <a href="{{ $backup->google_drive_web_link }}" target="_blank" class="btn btn-success" title="View in Google Drive">
                                        <i class="fab fa-google-drive"></i>
                                    </a>
                                    @else
                                    <button type="button" class="btn btn-success" onclick="uploadToDrive({{ $backup->id }})" title="Upload to Google Drive">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('admin.backups.download', $backup->id) }}" class="btn btn-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-warning" onclick="verifyBackup({{ $backup->id }})" title="Verify">
                                        <i class="fas fa-shield-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="restoreBackup({{ $backup->id }})" title="Restore">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteBackup({{ $backup->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No backups found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($backups->hasPages())
        <div class="card-footer bg-white">
            {{ $backups->links() }}
        </div>
        @endif
    </div>

    <!-- Activity Logs Link -->
    <div class="mt-4 text-center">
        <a href="{{ route('admin.backups.activity-logs') }}" class="btn btn-outline-secondary">
            <i class="fas fa-history me-2"></i>View Activity Logs
        </a>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="fas fa-eye me-2 text-primary"></i>Backup Preview & Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-3">Analyzing backup file...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Restore Database - Confirmation Required
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="restoreContent">
                <!-- Will be populated dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Manual Backup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createBackupForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Backup Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter backup description..."></textarea>
                        <small class="text-muted">Describe why you're creating this backup</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Backup akan dibuat dengan kompresi gzip untuk menghemat ruang penyimpanan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Backup Modal -->
<div class="modal fade" id="uploadBackupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload Backup File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadBackupForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Upload Instructions</h6>
                        <ul class="mb-0">
                            <li>Supported formats: <code>.sql</code> or <code>.sql.gz</code></li>
                            <li>Maximum file size: <strong>500 MB</strong></li>
                            <li>File akan otomatis di-compress jika belum compressed</li>
                            <li>Recommended: Upload file backup dari production server</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Backup File</label>
                        <input type="file" name="backup_file" id="backupFileInput" class="form-control" accept=".sql,.gz" required>
                        <small class="text-muted">Choose .sql or .sql.gz file from your computer</small>
                    </div>
                    
                    <div class="mb-3" id="fileInfoBox" style="display:none;">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Selected File:</h6>
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td width="30%"><strong>Filename:</strong></td>
                                        <td id="selectedFileName">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Size:</strong></td>
                                        <td id="selectedFileSize">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td id="selectedFileType">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Backup Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Production backup from 14 June 2026"></textarea>
                        <small class="text-muted">Describe this backup for future reference</small>
                    </div>
                    
                    <div class="progress" id="uploadProgress" style="display:none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="uploadSubmitBtn">
                        <i class="fas fa-upload me-2"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Success Modal -->
<div class="modal fade" id="uploadSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Backup Uploaded Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success border-success">
                    <h5 class="alert-heading">
                        <i class="fas fa-rocket me-2"></i>Upload Complete!
                    </h5>
                    <p class="mb-0">Your backup file has been successfully uploaded and imported into the system.</p>
                </div>
                
                <div class="card border-success">
                    <div class="card-header bg-success bg-opacity-10">
                        <h6 class="mb-0 text-success">
                            <i class="fas fa-file-archive me-2"></i>Backup Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="30%" class="text-muted"><i class="fas fa-file me-2"></i>Filename:</td>
                                <td class="fw-bold" id="successFilename">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-hdd me-2"></i>Size:</td>
                                <td><span class="badge bg-secondary" id="successSize">-</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-table me-2"></i>Tables:</td>
                                <td><span class="badge bg-info" id="successTables">-</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-database me-2"></i>Total Records:</td>
                                <td><span class="badge bg-primary" id="successRecords">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Next Steps:</strong> You can now preview this backup or restore it to replace your current database.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="location.reload()">
                    <i class="fas fa-sync me-2"></i>Refresh Page
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Verify Confirmation Modal -->
<div class="modal fade" id="verifyConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt me-2"></i>Verify Backup Integrity
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sistem akan memverifikasi integritas backup file dengan memeriksa:
                </div>
                <ul class="mb-0">
                    <li>File exists and accessible</li>
                    <li>MD5 hash matches original</li>
                    <li>File is not corrupted</li>
                    <li>File size is valid</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="verifyConfirmBtn">
                    <i class="fas fa-check me-2"></i>Verify Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash-alt me-2"></i>Delete Backup
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Warning!
                    </h6>
                    <p class="mb-0">Are you sure you want to delete this backup? This action cannot be undone!</p>
                </div>
                
                <div class="card border-warning">
                    <div class="card-body">
                        <label class="form-label fw-bold">Type <code class="text-danger">DELETE</code> to confirm:</label>
                        <input type="text" class="form-control form-control-lg" id="deleteConfirmInput" placeholder="DELETE" autocomplete="off">
                        <small class="text-muted">Must type in UPPERCASE</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">
                    <i class="fas fa-trash me-2"></i>Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Create Backup
document.getElementById('createBackupBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('createBackupModal'));
    modal.show();
});

document.getElementById('createBackupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.backups.create") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to create backup', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Upload Backup
document.getElementById('uploadBackupBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('uploadBackupModal'));
    modal.show();
});

// Show file info when selected
document.getElementById('backupFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileInfoBox').style.display = 'block';
        document.getElementById('selectedFileName').textContent = file.name;
        document.getElementById('selectedFileSize').textContent = formatFileSize(file.size);
        
        const extension = file.name.split('.').pop().toLowerCase();
        if (extension === 'gz' || file.name.endsWith('.sql.gz')) {
            document.getElementById('selectedFileType').innerHTML = '<span class="badge bg-success">Compressed (.sql.gz)</span>';
        } else if (extension === 'sql') {
            document.getElementById('selectedFileType').innerHTML = '<span class="badge bg-info">SQL File (will be compressed)</span>';
        } else {
            document.getElementById('selectedFileType').innerHTML = '<span class="badge bg-warning">Unknown format</span>';
        }
    }
});

// Upload form submission
document.getElementById('uploadBackupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const file = document.getElementById('backupFileInput').files[0];
    if (!file) {
        showToast('Error', 'Please select a file', 'error');
        return;
    }
    
    // Check file size (500MB max)
    const maxSize = 500 * 1024 * 1024; // 500MB in bytes
    if (file.size > maxSize) {
        showToast('Error', 'File size exceeds 500MB limit', 'error');
        return;
    }
    
    const btn = document.getElementById('uploadSubmitBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    
    const progressBar = document.getElementById('uploadProgress');
    const progressBarInner = progressBar.querySelector('.progress-bar');
    progressBar.style.display = 'block';
    
    const formData = new FormData(this);
    
    // Use XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            progressBarInner.style.width = percentComplete + '%';
            progressBarInner.textContent = percentComplete + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    
                    // Show uploaded file info in beautiful modal
                    if (data.backup) {
                        showUploadSuccess(data.backup);
                    } else {
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    showToast('Error', data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    progressBar.style.display = 'none';
                }
            } catch (error) {
                showToast('Error', 'Failed to parse response', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                progressBar.style.display = 'none';
            }
        } else {
            showToast('Error', 'Upload failed with status: ' + xhr.status, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
            progressBar.style.display = 'none';
        }
    });
    
    xhr.addEventListener('error', function() {
        showToast('Error', 'Network error during upload', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
        progressBar.style.display = 'none';
    });
    
    xhr.open('POST', '{{ route("admin.backups.upload") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(formData);
});

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Show upload success modal with details
function showUploadSuccess(backup) {
    // Close upload modal first
    const uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadBackupModal'));
    if (uploadModal) {
        uploadModal.hide();
    }
    
    // Populate data
    document.getElementById('successFilename').textContent = backup.filename;
    document.getElementById('successSize').textContent = backup.size;
    document.getElementById('successTables').textContent = backup.tables + ' tables';
    document.getElementById('successRecords').textContent = backup.records + ' records';
    
    // Show success modal
    const successModal = new bootstrap.Modal(document.getElementById('uploadSuccessModal'));
    successModal.show();
}

// Preview Backup
function previewBackup(backupId) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
    
    fetch(`/admin/backups/${backupId}/preview`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preview = data.preview;
                const backup = preview.backup;
                const current = preview.current_state;
                const comparison = preview.comparison;
                const backupMeta = preview.backup_metadata;
                
                let html = `
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary bg-opacity-10">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-archive me-2"></i>Backup Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td width="40%"><strong>Filename:</strong></td>
                                            <td class="text-truncate">${backup.filename}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Size:</strong></td>
                                            <td><span class="badge bg-secondary">${backup.size_human}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>${new Date(backup.created_at).toLocaleString('id-ID')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Age:</strong></td>
                                            <td>
                                                ${preview.age_in_days > 30 
                                                    ? `<span class="badge bg-warning">${preview.age_in_days} days old</span>`
                                                    : `<span class="badge bg-success">${preview.age_in_days} days old</span>`
                                                }
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tables:</strong></td>
                                            <td><span class="badge bg-info">${backup.total_tables} tables</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Records:</strong></td>
                                            <td><strong class="text-primary">${backup.estimated_records.toLocaleString()}</strong></td>
                                        </tr>
                                        <tr class="table-light">
                                            <td><strong><i class="fas fa-users text-success me-1"></i>Pendaftar:</strong></td>
                                            <td><strong class="text-success fs-5">${backupMeta.pendaftar_count || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong><i class="fas fa-user-shield text-primary me-1"></i>Users:</strong></td>
                                            <td>${backupMeta.users_count || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong><i class="fas fa-graduation-cap text-info me-1"></i>Jurusan:</strong></td>
                                            <td>${backupMeta.jurusan_count || 'N/A'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger h-100">
                                <div class="card-header bg-danger bg-opacity-10">
                                    <h6 class="mb-0 text-danger">
                                        <i class="fas fa-database me-2"></i>Current Database (Will be replaced)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td width="40%"><strong>Total Tables:</strong></td>
                                            <td><span class="badge bg-info">${current.total_tables} tables</span></td>
                                        </tr>
                                        <tr class="table-light">
                                            <td><strong><i class="fas fa-users text-danger me-1"></i>Pendaftar:</strong></td>
                                            <td><strong class="text-danger fs-5">${current.pendaftar_count}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong><i class="fas fa-calendar text-warning me-1"></i>Tahun Ajaran:</strong></td>
                                            <td>${current.tahun_ajaran_count}</td>
                                        </tr>
                                        <tr>
                                            <td><strong><i class="fas fa-user-shield text-primary me-1"></i>Users:</strong></td>
                                            <td>${current.users_count}</td>
                                        </tr>
                                        <tr>
                                            <td><strong><i class="fas fa-comments text-success me-1"></i>WA Logs:</strong></td>
                                            <td>${current.whatsapp_logs_count}</td>
                                        </tr>
                                    </table>
                                    
                                    ${current.pendaftar_count > 0 ? `
                                        <div class="alert alert-danger mt-3 mb-0">
                                            <small>
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>Warning:</strong> Current database has <strong>${current.pendaftar_count}</strong> pendaftar records that will be replaced!
                                            </small>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${preview.warnings.length > 0 ? `
                        <div class="alert alert-warning border-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Important Warnings
                            </h6>
                            <ul class="mb-0">
                                ${preview.warnings.map(w => `
                                    <li class="mb-2">
                                        <i class="fas ${w.icon} me-2"></i>${w.message}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-table me-2"></i>Detailed Table Comparison
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="30%">Table Name</th>
                                            <th width="35%">Current Records</th>
                                            <th width="35%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${Object.entries(comparison).map(([table, data]) => `
                                            <tr>
                                                <td><strong><i class="fas fa-table me-2 text-muted"></i>${table}</strong></td>
                                                <td>
                                                    <span class="badge ${data.current > 0 ? 'bg-danger' : 'bg-secondary'}">
                                                        ${data.current} records
                                                    </span>
                                                    ${data.current > 0 ? '<small class="text-danger ms-2">(will be replaced)</small>' : ''}
                                                </td>
                                                <td>
                                                    ${data.table_exists 
                                                        ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Exists</span>' 
                                                        : '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Missing</span>'}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Setelah restore, sistem akan otomatis menjalankan migrasi untuk memastikan semua tabel terbaru tersedia.
                    </div>
                `;
                
                document.getElementById('previewContent').innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('previewContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error:</strong> Failed to load preview. ${error.message}
                </div>
            `;
        });
}

// Verify Backup
let currentVerifyId = null;

function verifyBackup(backupId) {
    currentVerifyId = backupId;
    const modal = new bootstrap.Modal(document.getElementById('verifyConfirmModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const verifyBtn = document.getElementById('verifyConfirmBtn');
    if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
            if (!currentVerifyId) return;
            
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
            
            fetch(`/admin/backups/${currentVerifyId}/verify`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('verifyConfirmModal'));
                modal.hide();
                
                if (data.success) {
                    showToast('Success', 'Backup integrity verified successfully', 'success');
                } else {
                    showToast('Error', data.result?.message || 'Verification failed', 'error');
                }
                
                btn.disabled = false;
                btn.innerHTML = originalText;
                currentVerifyId = null;
            })
            .catch(error => {
                console.error('Error:', error);
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('verifyConfirmModal'));
                modal.hide();
                
                showToast('Error', 'Failed to verify backup', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                currentVerifyId = null;
            });
        });
    }
});

// Restore Backup
function restoreBackup(backupId) {
    // First show preview
    fetch(`/admin/backups/${backupId}/preview`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preview = data.preview;
                const backup = preview.backup;
                
                const html = `
                    <div class="alert alert-danger border-danger">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>⚠️ PERINGATAN KRITIS!
                        </h5>
                        <p class="mb-2">Restore akan <strong>MENGHAPUS SEMUA DATA SAAT INI</strong> dan menggantinya dengan data dari backup.</p>
                        <p class="mb-0"><strong>Operasi ini TIDAK BISA dibatalkan!</strong></p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success bg-opacity-10">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-archive me-2"></i>Backup yang akan di-restore
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-file text-primary me-2"></i>
                                            <strong>File:</strong> ${backup.filename}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-calendar text-info me-2"></i>
                                            <strong>Created:</strong> ${new Date(backup.created_at).toLocaleString('id-ID')}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-clock text-warning me-2"></i>
                                            <strong>Age:</strong> <span class="badge ${preview.age_in_days > 30 ? 'bg-warning' : 'bg-success'}">${preview.age_in_days} days old</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-hdd text-secondary me-2"></i>
                                            <strong>Size:</strong> ${backup.size_human}
                                        </li>
                                        <li>
                                            <i class="fas fa-users text-success me-2"></i>
                                            <strong>Pendaftar:</strong> <span class="badge bg-success">${preview.backup_metadata.pendaftar_count || 'N/A'}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="card-header bg-danger bg-opacity-10">
                                    <h6 class="mb-0 text-danger">
                                        <i class="fas fa-trash-alt me-2"></i>Data yang akan dihapus
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-users text-danger me-2"></i>
                                            <strong>Pendaftar:</strong> <span class="badge bg-danger">${preview.current_state.pendaftar_count}</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-calendar text-danger me-2"></i>
                                            <strong>Tahun Ajaran:</strong> <span class="badge bg-danger">${preview.current_state.tahun_ajaran_count}</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-user-shield text-danger me-2"></i>
                                            <strong>Users:</strong> <span class="badge bg-danger">${preview.current_state.users_count}</span>
                                        </li>
                                        <li>
                                            <i class="fas fa-comments text-danger me-2"></i>
                                            <strong>WA Logs:</strong> <span class="badge bg-danger">${preview.current_state.whatsapp_logs_count}</span>
                                        </li>
                                    </ul>
                                    ${preview.current_state.pendaftar_count > 0 ? `
                                        <div class="alert alert-danger mt-3 mb-0 py-2">
                                            <small>
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <strong>${preview.current_state.pendaftar_count}</strong> pendaftar akan dihapus!
                                            </small>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-info mb-3">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="createPreRestoreBackup" checked>
                                <label class="form-check-label" for="createPreRestoreBackup">
                                    <i class="fas fa-shield-alt text-success me-2"></i>
                                    <strong>Buat backup otomatis sebelum restore (Sangat Direkomendasikan)</strong>
                                    <br>
                                    <small class="text-muted">Backup saat ini akan disimpan sehingga Anda dapat kembali jika terjadi masalah</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-warning">
                        <div class="card-body">
                            <label class="form-label fw-bold">
                                <i class="fas fa-keyboard me-2"></i>Ketik nama database untuk konfirmasi:
                            </label>
                            <input type="text" class="form-control form-control-lg" id="restoreConfirmation" placeholder="{{ config('database.connections.mysql.database') }}" autocomplete="off">
                            <small class="text-muted">
                                Ketik: <code class="text-danger fw-bold">{{ config('database.connections.mysql.database') }}</code>
                            </small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" onclick="executeRestore(${backupId})">
                            <i class="fas fa-history me-2"></i>Restore Sekarang
                        </button>
                    </div>
                `;
                
                document.getElementById('restoreContent').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to load restore preview: ' + error.message, 'error');
        });
}

function executeRestore(backupId) {
    const confirmation = document.getElementById('restoreConfirmation').value;
    const createPreRestoreBackup = document.getElementById('createPreRestoreBackup').checked;
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Restoring...';
    
    fetch(`/admin/backups/${backupId}/restore`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            confirmation: confirmation,
            create_pre_restore_backup: createPreRestoreBackup
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Error', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to restore backup', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Delete Backup
let currentDeleteId = null;

function deleteBackup(backupId) {
    currentDeleteId = backupId;
    // Clear previous input
    document.getElementById('deleteConfirmInput').value = '';
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Upload to Google Drive
function uploadToDrive(backupId) {
    if (!confirm('Upload backup ini ke Google Drive?')) return;
    
    showToast('Upload', 'Starting upload to Google Drive...', 'info');
    
    fetch(`/admin/backups/${backupId}/upload-to-drive`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to upload to Google Drive', 'error');
    });
}

// Add event listener for delete confirmation
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById('deleteConfirmBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!currentDeleteId) return;
            
            const confirmation = document.getElementById('deleteConfirmInput').value;
            
            if (confirmation !== 'DELETE') {
                showToast('Error', 'You must type DELETE to confirm', 'warning');
                return;
            }
            
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
            
            fetch(`/admin/backups/${currentDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    confirmation: 'DELETE'
                })
            })
            .then(response => response.json())
            .then(data => {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
                currentDeleteId = null;
            })
            .catch(error => {
                console.error('Error:', error);
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                
                showToast('Error', 'Failed to delete backup', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                currentDeleteId = null;
            });
        });
    }
});

// Toast notification helper
function showToast(title, message, type) {
    // Create toast container if not exists
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Icon mapping
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-times-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const bgColors = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    };
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgColors[type]} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icons[type]} me-2"></i>
                    <strong>${title}</strong>: ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'error' ? 6000 : 4000
    });
    
    toast.show();
    
    // Remove from DOM after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endpush

@push('styles')
<style>
/* Custom Backup Page Styles */
.admin-page-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.icon-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
    min-height: 60px;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table tbody tr {
    transition: background-color 0.15s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}

.btn-group-sm .btn:hover {
    transform: scale(1.1);
    z-index: 1;
}

/* Badge colors */
.badge {
    font-weight: 500;
    padding: 0.35rem 0.65rem;
}

/* Modal animations */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}

/* Progress bar animation */
.progress {
    height: 1.5rem;
    background-color: #e9ecef;
    border-radius: 0.375rem;
    overflow: hidden;
}

.progress-bar {
    font-size: 0.875rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

/* Alert styles */
.alert {
    border-left: 4px solid;
    animation: slideIn 0.3s ease-out;
}

.alert-success {
    border-left-color: #198754;
}

.alert-warning {
    border-left-color: #ffc107;
}

.alert-danger {
    border-left-color: #dc3545;
}

.alert-info {
    border-left-color: #0dcaf0;
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

/* File info box */
#fileInfoBox .card {
    border: 2px dashed #0d6efd;
    background-color: #f8f9fa;
}

/* User avatar */
.user-avatar-sm {
    font-size: 0.75rem;
}

/* Table responsive improvements */
@media (max-width: 768px) {
    .btn-group-sm .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
    }
    
    .icon-box {
        min-width: 50px;
        min-height: 50px;
    }
    
    .card-body h3 {
        font-size: 1.5rem;
    }
}

/* Empty state */
.table tbody td i.fa-3x {
    opacity: 0.3;
}

/* Toast container */
.toast-container {
    z-index: 9999 !important;
}

.toast {
    min-width: 300px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.25);
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Improved button styles */
.btn {
    font-weight: 500;
    transition: all 0.15s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
}

/* Card header improvements */
.card-header {
    border-bottom: 2px solid rgba(0, 0, 0, 0.05);
}

/* Form improvements */
.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

/* Statistics card hover effect */
.icon-box {
    transition: transform 0.2s ease;
}

.card:hover .icon-box {
    transform: scale(1.1);
}
</style>
@endpush

@endsection
