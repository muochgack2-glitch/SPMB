<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatabaseBackup extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'size_bytes',
        'md5_hash',
        'google_drive_file_id',
        'google_drive_web_link',
        'uploaded_to_drive_at',
        'database_name',
        'source_type',
        'source_context',
        'tahun_ajaran_context',
        'total_tables',
        'estimated_records',
        'backup_notes',
        'cloud_storage_provider',
        'cloud_storage_url',
        'cloud_upload_status',
        'cloud_uploaded_at',
        'cloud_error_message',
        'created_by',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'total_tables' => 'integer',
        'estimated_records' => 'integer',
        'cloud_uploaded_at' => 'datetime',
        'uploaded_to_drive_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(BackupActivityLog::class, 'backup_id');
    }

    /**
     * Accessors
     */
    public function getSizeHumanAttribute(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getAgeInDaysAttribute(): int
    {
        return now()->diffInDays($this->created_at);
    }

    public function getIsOldAttribute(): bool
    {
        return $this->age_in_days > 30;
    }

    public function getSourceBadgeColorAttribute(): string
    {
        return match($this->source_type) {
            'manual' => 'primary',
            'auto' => 'success',
            'scheduled' => 'info',
            'pre_operation' => 'warning',
            default => 'secondary',
        };
    }

    public function getCloudStatusBadgeAttribute(): string
    {
        if (!$this->cloud_upload_status) {
            return '<span class="badge bg-secondary">Local Only</span>';
        }

        return match($this->cloud_upload_status) {
            'completed' => '<span class="badge bg-success"><i class="fas fa-cloud"></i> Cloud Stored</span>',
            'pending' => '<span class="badge bg-warning"><i class="fas fa-clock"></i> Pending Upload</span>',
            'uploading' => '<span class="badge bg-info"><i class="fas fa-spinner fa-spin"></i> Uploading</span>',
            'failed' => '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Upload Failed</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Methods
     */
    public function verifyIntegrity(): bool
    {
        if (!file_exists($this->path)) {
            return false;
        }

        $currentHash = md5_file($this->path);
        return $currentHash === $this->md5_hash;
    }

    public function deleteFiles(): void
    {
        // Delete local file
        if (file_exists($this->path)) {
            @unlink($this->path);
        }

        // Delete from Google Drive if exists
        if ($this->google_drive_file_id) {
            try {
                $googleDrive = app(\App\Services\GoogleDriveService::class);
                $googleDrive->deleteFile($this->google_drive_file_id);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete file from Google Drive', [
                    'file_id' => $this->google_drive_file_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function fileExists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Check if backup is stored in Google Drive
     */
    public function isInGoogleDrive(): bool
    {
        return !is_null($this->google_drive_file_id);
    }

    /**
     * Get Google Drive status badge
     */
    public function getGoogleDriveStatusBadgeAttribute(): string
    {
        if ($this->isInGoogleDrive()) {
            return '<span class="badge bg-success" title="Stored in Google Drive"><i class="fab fa-google-drive me-1"></i>In Drive</span>';
        }
        return '<span class="badge bg-secondary" title="Not in Google Drive">Local Only</span>';
    }

    /**
     * Scopes
     */
    public function scopeManual($query)
    {
        return $query->where('source_type', 'manual');
    }

    public function scopeAuto($query)
    {
        return $query->where('source_type', 'auto');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
