@extends('layouts.admin')

@section('title', 'Backup Activity Logs')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="admin-page-title">
                <i class="fas fa-history me-2"></i>Backup Activity Logs
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Monitor dan audit semua aktivitas backup dan restore system
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Backups
                </a>
                <button class="btn btn-outline-secondary" onclick="exportLogs()" title="Export logs ke CSV">
                    <i class="fas fa-download me-2"></i>Export
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
                            <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="fas fa-list fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Logs</h6>
                            <h3 class="mb-0">{{ $logs->total() }}</h3>
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
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Successful</h6>
                            <h3 class="mb-0">{{ $logs->where('status', 'success')->count() }}</h3>
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
                            <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Failed</h6>
                            <h3 class="mb-0">{{ $logs->where('status', 'failed')->count() }}</h3>
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
                                <i class="fas fa-spinner fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">In Progress</h6>
                            <h3 class="mb-0">{{ $logs->where('status', 'in_progress')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter & Search Options
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.backups.activity-logs') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tasks me-1"></i>Operation Type
                    </label>
                    <select name="operation" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('operation') == 'all' ? 'selected' : '' }}>All Operations</option>
                        <option value="backup_created" {{ request('operation') == 'backup_created' ? 'selected' : '' }}>
                            📦 Backup Created
                        </option>
                        <option value="backup_deleted" {{ request('operation') == 'backup_deleted' ? 'selected' : '' }}>
                            🗑️ Backup Deleted
                        </option>
                        <option value="restore_started" {{ request('operation') == 'restore_started' ? 'selected' : '' }}>
                            ⏳ Restore Started
                        </option>
                        <option value="restore_completed" {{ request('operation') == 'restore_completed' ? 'selected' : '' }}>
                            ✅ Restore Completed
                        </option>
                        <option value="restore_failed" {{ request('operation') == 'restore_failed' ? 'selected' : '' }}>
                            ❌ Restore Failed
                        </option>
                        <option value="integrity_check" {{ request('operation') == 'integrity_check' ? 'selected' : '' }}>
                            🔒 Integrity Check
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-info-circle me-1"></i>Status
                    </label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>✅ Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>❌ Failed</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>⏳ In Progress</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-alt me-1"></i>From Date
                    </label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-check me-1"></i>To Date
                    </label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-list-ol me-1"></i>Per Page
                    </label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" title="Apply filters">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            @if(request()->hasAny(['operation', 'status', 'from_date', 'to_date', 'per_page']))
            <div class="mt-3">
                <a href="{{ route('admin.backups.activity-logs') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Clear All Filters
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Activity Logs 
                    <span class="badge bg-primary">{{ $logs->total() }}</span>
                </h5>
                <div class="text-muted">
                    <small>Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }}</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="15%">Operation</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="20%">Backup File</th>
                            <th width="15%">User</th>
                            <th width="12%">Date/Time</th>
                            <th width="8%" class="text-center">Duration</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="log-row">
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $logs->firstItem() + $loop->index }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="operation-icon-box me-2">
                                        <i class="fas {{ $log->operation_icon }} fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $log->operation_label }}</div>
                                        <small class="text-muted">{{ ucfirst($log->operation_type) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{!! $log->status_badge !!}</td>
                            <td>
                                @if($log->backup)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-archive text-primary me-2"></i>
                                    <div>
                                        <small class="fw-bold text-truncate d-block" style="max-width: 200px;" title="{{ $log->backup->filename }}">
                                            {{ Str::limit($log->backup->filename, 30) }}
                                        </small>
                                        <small class="text-muted">{{ $log->backup->size_human }}</small>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted fst-italic">No backup file</span>
                                @endif
                            </td>
                            <td>
                                @if($log->user)
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $log->user->name }}</div>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                    </div>
                                </div>
                                @else
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-sm bg-secondary bg-opacity-10 text-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                    <span class="text-muted">System</span>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <small class="fw-bold">{{ $log->created_at->format('d M Y') }}</small>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $log->created_at->format('H:i:s') }}
                                    </small>
                                </div>
                                <div>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $log->duration_seconds > 60 ? 'bg-warning' : 'bg-light text-dark' }}">
                                    {{ $log->duration_human }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($log->error_message)
                                <button class="btn btn-sm btn-danger" onclick="showError('{{ addslashes($log->error_message) }}')" title="View error details">
                                    <i class="fas fa-exclamation-circle me-1"></i>Error
                                </button>
                                @elseif($log->details)
                                <button class="btn btn-sm btn-info" onclick="showDetails({{ $log->id }})" title="View operation details">
                                    <i class="fas fa-info-circle me-1"></i>Details
                                </button>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-history fa-4x text-muted mb-3 opacity-25"></i>
                                    <h5 class="text-muted">No Activity Logs Found</h5>
                                    <p class="text-muted">Try adjusting your filters or check back later</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <small>Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries</small>
                </div>
                <div>
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info bg-opacity-10">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2 text-info"></i>Activity Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <pre id="detailsContent" class="mb-0" style="max-height: 500px; overflow-y: auto;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="copyDetails()">
                    <i class="fas fa-copy me-2"></i>Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-circle me-2"></i>Error Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger border-danger">
                    <h6 class="alert-heading">
                        <i class="fas fa-bug me-2"></i>Error Message
                    </h6>
                    <hr>
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <pre id="errorContent" class="mb-0 text-danger" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-danger" onclick="copyError()">
                    <i class="fas fa-copy me-2"></i>Copy Error
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showDetails(logId) {
    // Find log in current page data
    const logs = @json($logs->items());
    const log = logs.find(l => l.id === logId);
    
    if (log && log.details) {
        try {
            const details = typeof log.details === 'string' ? JSON.parse(log.details) : log.details;
            document.getElementById('detailsContent').textContent = JSON.stringify(details, null, 2);
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        } catch (e) {
            document.getElementById('detailsContent').textContent = log.details;
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }
    }
}

function showError(errorMessage) {
    document.getElementById('errorContent').textContent = errorMessage;
    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
}

function copyDetails() {
    const content = document.getElementById('detailsContent').textContent;
    navigator.clipboard.writeText(content).then(() => {
        showToast('Copied', 'Details copied to clipboard', 'success');
    }).catch(() => {
        showToast('Error', 'Failed to copy to clipboard', 'error');
    });
}

function copyError() {
    const content = document.getElementById('errorContent').textContent;
    navigator.clipboard.writeText(content).then(() => {
        showToast('Copied', 'Error message copied to clipboard', 'success');
    }).catch(() => {
        showToast('Error', 'Failed to copy to clipboard', 'error');
    });
}

function exportLogs() {
    // Get current filters
    const params = new URLSearchParams(window.location.search);
    const url = '{{ route("admin.backups.activity-logs") }}?' + params.toString() + '&export=csv';
    
    showToast('Export', 'Exporting logs to CSV...', 'info');
    
    // For now, just show a message
    // In production, you'd implement the actual export endpoint
    setTimeout(() => {
        showToast('Info', 'Export feature coming soon!', 'info');
    }, 1000);
}

// Toast notification helper (same as backup page)
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
</script>
@endpush

@push('styles')
<style>
/* Activity Logs Custom Styles */
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

.log-row {
    transition: all 0.2s ease;
}

.log-row:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: scale(1.005);
}

.operation-icon-box {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.log-row:hover .operation-icon-box {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.2) 0%, rgba(13, 110, 253, 0.1) 100%);
    transform: scale(1.1);
}

.user-avatar-sm {
    font-size: 0.75rem;
}

.empty-state {
    padding: 2rem;
}

.empty-state i {
    display: block;
}

/* Badge enhancements */
.badge {
    font-weight: 500;
    padding: 0.35rem 0.65rem;
}

/* Modal improvements */
.modal-content {
    border: none;
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

pre {
    font-size: 0.875rem;
    line-height: 1.5;
    color: #212529;
}

/* Button hover effects */
.btn {
    transition: all 0.15s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
}

/* Card header */
.card-header {
    border-bottom: 2px solid rgba(0, 0, 0, 0.05);
}

/* Form improvements */
.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

/* Icon box hover */
.card:hover .icon-box {
    transform: scale(1.1);
}

.icon-box {
    transition: transform 0.2s ease;
}

/* Toast styles */
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

/* Responsive */
@media (max-width: 768px) {
    .icon-box {
        min-width: 50px;
        min-height: 50px;
    }
    
    .card-body h3 {
        font-size: 1.5rem;
    }
    
    .operation-icon-box {
        width: 32px;
        height: 32px;
    }
}
</style>
@endpush

@endsection
