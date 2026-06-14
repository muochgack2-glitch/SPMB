@extends('layouts.admin')

@section('title', 'Google Drive Settings')

@section('content')
<div class="container-fluid" id="googleDriveSettingsPage">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="admin-page-title">
                <i class="fab fa-google-drive me-2"></i>Google Drive Settings
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Configure automatic backup upload to Google Drive
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Backups
            </a>
        </div>
    </div>

    <!-- Connection Status Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="background-color: var(--bg-primary);">
                <div class="card-header {{ $settings['credentials_uploaded'] && $settings['folder_id'] ? 'bg-success' : 'bg-warning' }}">
                    <h5 class="mb-0" style="color: {{ $settings['credentials_uploaded'] && $settings['folder_id'] ? '#fff' : '#000' }};">
                        <i class="fas fa-{{ $settings['credentials_uploaded'] && $settings['folder_id'] ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                        Connection Status
                    </h5>
                </div>
                <div class="card-body" style="background-color: var(--bg-primary);">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="status-item">
                                <i class="fas fa-file-code fa-2x {{ $settings['credentials_uploaded'] ? 'text-success' : 'text-danger' }} mb-2"></i>
                                <h6>Credentials</h6>
                                <p class="mb-0">
                                    @if($settings['credentials_uploaded'])
                                        <span class="badge bg-success">Uploaded</span>
                                    @else
                                        <span class="badge bg-danger">Not Uploaded</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-item">
                                <i class="fas fa-folder fa-2x {{ $settings['folder_id'] ? 'text-success' : 'text-danger' }} mb-2"></i>
                                <h6>Folder ID</h6>
                                <p class="mb-0">
                                    @if($settings['folder_id'])
                                        <span class="badge bg-success">Configured</span>
                                    @else
                                        <span class="badge bg-danger">Not Set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-item">
                                <i class="fas fa-cloud-upload-alt fa-2x {{ $settings['auto_upload_enabled'] ? 'text-success' : 'text-secondary' }} mb-2"></i>
                                <h6>Auto Upload</h6>
                                <p class="mb-0">
                                    @if($settings['auto_upload_enabled'])
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($settings['credentials_uploaded'] && $settings['folder_id'])
                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-primary" onclick="testConnection()">
                                <i class="fas fa-plug me-2"></i>Test Connection
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="background-color: var(--bg-primary);">
                <div class="card-header" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-light);">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Configuration
                    </h5>
                </div>
                <div class="card-body" style="background-color: var(--bg-primary);">
                    <form id="googleDriveSettingsForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Step 1: Upload Credentials -->
                        <div class="mb-4">
                            <h6 class="text-primary">
                                <span class="badge bg-primary me-2">1</span>
                                Upload Service Account Credentials
                            </h6>
                            <p class="text-muted small">Download JSON credentials dari Google Cloud Console</p>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Credentials File (JSON)</label>
                                <input type="file" name="credentials_file" id="credentialsFile" class="form-control" accept=".json">
                                <small class="text-muted">
                                    Current file: 
                                    @if($settings['credentials_uploaded'])
                                        <span class="text-success">✓ {{ $settings['service_account_json'] }}</span>
                                    @else
                                        <span class="text-danger">Not uploaded</span>
                                    @endif
                                </small>
                            </div>
                        </div>

                        <hr>

                        <!-- Step 2: Folder ID -->
                        <div class="mb-4">
                            <h6 class="text-primary">
                                <span class="badge bg-primary me-2">2</span>
                                Google Drive Folder ID
                            </h6>
                            <p class="text-muted small">ID folder tempat backup akan disimpan</p>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Folder ID</label>
                                <input type="text" name="folder_id" class="form-control form-control-lg" value="{{ $settings['folder_id'] }}" placeholder="1ABC...XYZ" required>
                                <small class="text-muted">
                                    Copy dari URL folder: <code>https://drive.google.com/drive/folders/[FOLDER_ID]</code>
                                </small>
                            </div>
                        </div>

                        <hr>

                        <!-- Step 3: Auto Upload Settings -->
                        <div class="mb-4">
                            <h6 class="text-primary">
                                <span class="badge bg-primary me-2">3</span>
                                Auto Upload Settings
                            </h6>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="auto_upload_enabled" id="autoUploadEnabled" value="1" {{ $settings['auto_upload_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="autoUploadEnabled">
                                    <strong>Enable Auto Upload to Google Drive</strong>
                                    <br>
                                    <small class="text-muted">Backup akan otomatis diupload ke Google Drive setelah dibuat</small>
                                </label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="keep_local_copy" id="keepLocalCopy" value="1" {{ $settings['keep_local_copy'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="keepLocalCopy">
                                    <strong>Keep Local Copy</strong>
                                    <br>
                                    <small class="text-muted">Simpan file di local storage setelah upload ke Google Drive</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Storage Info Card -->
            @if($storageInfo && $storageInfo['success'])
            <div class="card border-0 shadow-sm mb-3" style="background-color: var(--bg-primary); border: 1px solid var(--border-light) !important;">
                <div class="card-header" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-light);">
                    <h6 class="mb-0" style="color: #0dcaf0;">
                        <i class="fas fa-hdd me-2"></i>Storage Information
                    </h6>
                </div>
                <div class="card-body" style="background-color: var(--bg-primary);">
                    @if(isset($storageInfo['limit']) && $storageInfo['limit'] > 0)
                    <div class="mb-3">
                        <small class="text-muted">Used</small>
                        <h5 class="mb-0">{{ \App\Helpers\FormatHelper::formatBytes($storageInfo['usage']) }}</h5>
                    </div>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-info" style="width: {{ ($storageInfo['usage'] / $storageInfo['limit']) * 100 }}%"></div>
                    </div>
                    <small class="text-muted">
                        {{ number_format(($storageInfo['usage'] / $storageInfo['limit']) * 100, 1) }}% of {{ \App\Helpers\FormatHelper::formatBytes($storageInfo['limit']) }}
                    </small>
                    @else
                    <p class="text-muted mb-0">Storage info not available for service accounts</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Setup Guide Card -->
            <div class="card border-0 shadow-sm" style="background-color: var(--bg-primary); border: 1px solid var(--border-light) !important;">
                <div class="card-header" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-light);">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Setup Guide
                    </h6>
                </div>
                <div class="card-body" style="background-color: var(--bg-primary);">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">
                            <strong>Google Cloud Console</strong>
                            <ul class="small text-muted">
                                <li>Create project</li>
                                <li>Enable Google Drive API</li>
                                <li>Create Service Account</li>
                                <li>Download JSON key</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>Google Drive</strong>
                            <ul class="small text-muted">
                                <li>Create backup folder</li>
                                <li>Share with service account</li>
                                <li>Copy folder ID</li>
                            </ul>
                        </li>
                        <li>
                            <strong>Configure Laravel</strong>
                            <ul class="small text-muted">
                                <li>Upload credentials</li>
                                <li>Set folder ID</li>
                                <li>Test connection</li>
                            </ul>
                        </li>
                    </ol>
                    
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-outline-primary w-100" onclick="showFullGuide(); return false;">
                            <i class="fas fa-external-link-alt me-2"></i>View Full Guide
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Files -->
    @if(count($recentFiles) > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="background-color: var(--bg-primary);">
                <div class="card-header" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-light);">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Recent Files in Google Drive
                    </h5>
                </div>
                <div class="card-body p-0" style="background-color: var(--bg-primary);">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFiles as $file)
                                <tr>
                                    <td>
                                        <i class="fas fa-file-archive text-primary me-2"></i>
                                        {{ $file['name'] }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $this->formatBytes($file['size']) }}</span>
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($file['created_time'])->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ $file['web_view_link'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plug me-2"></i>Test Connection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="testConnectionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Testing...</span>
                    </div>
                    <p class="mt-3">Testing connection to Google Drive...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Save settings
document.getElementById('googleDriveSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    
    const formData = new FormData(this);
    
    // Ensure checkbox values are sent as 0 or 1
    const autoUploadCheckbox = document.getElementById('autoUploadEnabled');
    const keepLocalCheckbox = document.getElementById('keepLocalCopy');
    
    formData.set('auto_upload_enabled', autoUploadCheckbox.checked ? '1' : '0');
    formData.set('keep_local_copy', keepLocalCheckbox.checked ? '1' : '0');
    
    // Debug: Check if file is included
    const fileInput = document.getElementById('credentialsFile');
    if (fileInput.files.length > 0) {
        console.log('File selected:', fileInput.files[0].name);
        // Make sure file is in FormData
        formData.set('credentials_file', fileInput.files[0]);
    }
    
    fetch('{{ route("admin.backups.google-drive.save-settings") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            // DO NOT set Content-Type - let browser set it automatically with boundary
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Show test result if available
            if (data.test_result) {
                if (data.test_result.success) {
                    showToast('Connection Test', 'Connection successful!', 'success');
                } else {
                    showToast('Connection Test', 'Warning: ' + data.test_result.message, 'warning');
                }
            }
            
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Error', data.message, 'error');
        }
        
        btn.disabled = false;
        btn.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to save settings', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Test connection
function testConnection() {
    const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
    modal.show();
    
    fetch('{{ route("admin.backups.google-drive.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        let html = '';
        
        if (data.success) {
            html = `
                <div class="alert alert-success">
                    <h5 class="alert-heading">
                        <i class="fas fa-check-circle me-2"></i>Connection Successful!
                    </h5>
                    <hr>
                    <p class="mb-0">
                        <strong>Folder:</strong> ${data.data.folder_name}<br>
                        <strong>Folder ID:</strong> ${data.data.folder_id}
                    </p>
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger">
                    <h5 class="alert-heading">
                        <i class="fas fa-times-circle me-2"></i>Connection Failed
                    </h5>
                    <hr>
                    <p class="mb-0">${data.message}</p>
                </div>
            `;
        }
        
        document.getElementById('testConnectionContent').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('testConnectionContent').innerHTML = `
            <div class="alert alert-danger">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-circle me-2"></i>Error
                </h5>
                <hr>
                <p class="mb-0">${error.message}</p>
            </div>
        `;
    });
}

// Show full guide
function showFullGuide() {
    const guideContent = `
        <h5>Complete Setup Guide</h5>
        <p>Follow these steps to setup Google Drive integration:</p>
        
        <h6 class="mt-3">1. Google Cloud Console</h6>
        <ol>
            <li>Go to: <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
            <li>Create new project or select existing</li>
            <li>Enable Google Drive API</li>
            <li>Create Service Account with Editor role</li>
            <li>Download JSON credentials</li>
        </ol>
        
        <h6 class="mt-3">2. Google Drive</h6>
        <ol>
            <li>Create folder "SPMB Backups"</li>
            <li>Share folder with service account email</li>
            <li>Copy folder ID from URL</li>
        </ol>
        
        <h6 class="mt-3">3. Laravel Configuration</h6>
        <ol>
            <li>Upload credentials JSON file</li>
            <li>Enter folder ID</li>
            <li>Enable auto upload (optional)</li>
            <li>Click "Save Settings"</li>
            <li>Test connection</li>
        </ol>
        
        <div class="alert alert-info mt-3">
            <strong>Need detailed help?</strong> Check the documentation files in project root:
            <ul class="mb-0 mt-2">
                <li>GOOGLE_DRIVE_README.md</li>
                <li>GOOGLE_DRIVE_QUICK_START.md</li>
                <li>GOOGLE_DRIVE_CHECKLIST.md</li>
            </ul>
        </div>
    `;
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Setup Guide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${guideContent}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// Toast helper
function showToast(title, message, type) {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-times-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center border-0" role="alert" style="background-color: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#17a2b8'}; color: ${type === 'warning' ? '#000' : '#fff'};">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icons[type]} me-2"></i>
                    <strong>${title}</strong>: ${message}
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast"></button>
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
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}
</script>
@endpush

@push('styles')
<style>
.status-item {
    text-align: center;
    padding: 1rem;
}

.status-item h6 {
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
</style>
@endpush

@endsection

@php
// Helper function for formatting bytes
function formatBytes($bytes, $precision = 2) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), $precision) . ' ' . $sizes[$i];
}
@endphp
