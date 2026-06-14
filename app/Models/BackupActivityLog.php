<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupActivityLog extends Model
{
    // Only created_at, no updated_at (logs are immutable)
    const UPDATED_AT = null;

    protected $fillable = [
        'operation_type',
        'backup_id',
        'user_id',
        'status',
        'details',
        'error_message',
        'duration_seconds',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
        'duration_seconds' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function backup(): BelongsTo
    {
        return $this->belongsTo(DatabaseBackup::class, 'backup_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessors
     */
    public function getOperationLabelAttribute(): string
    {
        return match($this->operation_type) {
            'backup_created' => 'Backup Created',
            'backup_deleted' => 'Backup Deleted',
            'restore_started' => 'Restore Started',
            'restore_completed' => 'Restore Completed',
            'restore_failed' => 'Restore Failed',
            'cloud_upload' => 'Cloud Upload',
            'cloud_download' => 'Cloud Download',
            'integrity_check' => 'Integrity Check',
            default => ucfirst(str_replace('_', ' ', $this->operation_type)),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'success' => '<span class="badge bg-success">Success</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'in_progress' => '<span class="badge bg-info">In Progress</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getOperationIconAttribute(): string
    {
        return match($this->operation_type) {
            'backup_created' => 'fa-plus-circle',
            'backup_deleted' => 'fa-trash',
            'restore_started' => 'fa-history',
            'restore_completed' => 'fa-check-circle',
            'restore_failed' => 'fa-times-circle',
            'cloud_upload' => 'fa-cloud-upload',
            'cloud_download' => 'fa-cloud-download',
            'integrity_check' => 'fa-shield-alt',
            default => 'fa-info-circle',
        };
    }

    public function getDurationHumanAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '-';
        }

        if ($this->duration_seconds < 60) {
            return round($this->duration_seconds, 2) . 's';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return $minutes . 'm ' . round($seconds) . 's';
    }

    /**
     * Scopes
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByOperation($query, string $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
