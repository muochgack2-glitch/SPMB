<?php

namespace App\Services;

use App\Models\BackupActivityLog;
use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Log;

class ActivityLoggerService
{
    /**
     * Log backup creation
     */
    public function logBackupCreated(DatabaseBackup $backup, float $duration): BackupActivityLog
    {
        $log = BackupActivityLog::create([
            'operation_type' => 'backup_created',
            'backup_id' => $backup->id,
            'user_id' => auth()->id(),
            'status' => 'success',
            'details' => json_encode([
                'filename' => $backup->filename,
                'size_bytes' => $backup->size_bytes,
                'size_human' => $backup->size_human,
                'source_type' => $backup->source_type,
                'source_context' => $backup->source_context,
                'total_tables' => $backup->total_tables,
                'estimated_records' => $backup->estimated_records,
            ]),
            'duration_seconds' => $duration,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::info('Activity logged: backup_created', [
            'log_id' => $log->id,
            'backup_id' => $backup->id,
        ]);

        return $log;
    }

    /**
     * Log backup creation failure
     */
    public function logBackupFailed(string $errorMessage, float $duration, string $sourceType): BackupActivityLog
    {
        $log = BackupActivityLog::create([
            'operation_type' => 'backup_created',
            'backup_id' => null,
            'user_id' => auth()->id(),
            'status' => 'failed',
            'error_message' => $errorMessage,
            'details' => json_encode([
                'source_type' => $sourceType,
            ]),
            'duration_seconds' => $duration,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::error('Activity logged: backup_failed', [
            'log_id' => $log->id,
            'error' => $errorMessage,
        ]);

        return $log;
    }

    /**
     * Log backup deletion
     */
    public function logBackupDeleted(DatabaseBackup $backup, float $duration): BackupActivityLog
    {
        $log = BackupActivityLog::create([
            'operation_type' => 'backup_deleted',
            'backup_id' => $backup->id,
            'user_id' => auth()->id(),
            'status' => 'success',
            'details' => json_encode([
                'filename' => $backup->filename,
                'size_bytes' => $backup->size_bytes,
                'age_days' => $backup->age_in_days,
            ]),
            'duration_seconds' => $duration,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $log;
    }

    /**
     * Log restore started
     */
    public function logRestoreStarted(DatabaseBackup $backup): BackupActivityLog
    {
        $log = BackupActivityLog::create([
            'operation_type' => 'restore_started',
            'backup_id' => $backup->id,
            'user_id' => auth()->id(),
            'status' => 'in_progress',
            'details' => json_encode([
                'filename' => $backup->filename,
                'backup_created_at' => $backup->created_at->toDateTimeString(),
                'backup_age_days' => $backup->age_in_days,
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::info('Activity logged: restore_started', [
            'log_id' => $log->id,
            'backup_id' => $backup->id,
        ]);

        return $log;
    }

    /**
     * Log restore completed
     */
    public function logRestoreCompleted(BackupActivityLog $activityLog, float $duration, ?int $preRestoreBackupId = null): void
    {
        $details = json_decode($activityLog->details, true) ?? [];
        $details['pre_restore_backup_id'] = $preRestoreBackupId;

        $activityLog->update([
            'status' => 'success',
            'operation_type' => 'restore_completed',
            'duration_seconds' => $duration,
            'details' => json_encode($details),
        ]);

        Log::info('Activity logged: restore_completed', [
            'log_id' => $activityLog->id,
            'duration' => $duration,
        ]);
    }

    /**
     * Log restore failed
     */
    public function logRestoreFailed(BackupActivityLog $activityLog, \Exception $exception): void
    {
        $activityLog->update([
            'status' => 'failed',
            'operation_type' => 'restore_failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::error('Activity logged: restore_failed', [
            'log_id' => $activityLog->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Log integrity check
     */
    public function logIntegrityCheck(DatabaseBackup $backup, bool $success, float $duration, ?string $errorMessage = null): BackupActivityLog
    {
        $log = BackupActivityLog::create([
            'operation_type' => 'integrity_check',
            'backup_id' => $backup->id,
            'user_id' => auth()->id(),
            'status' => $success ? 'success' : 'failed',
            'details' => json_encode([
                'filename' => $backup->filename,
                'file_exists' => $backup->fileExists(),
                'md5_hash' => $backup->md5_hash,
            ]),
            'error_message' => $errorMessage,
            'duration_seconds' => $duration,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $log;
    }

    /**
     * Generic log method for custom operations
     */
    public function log(string $operationType, string $status, ?int $backupId = null, ?int $userId = null, array $details = []): BackupActivityLog
    {
        $log = BackupActivityLog::create([
            'operation_type' => $operationType,
            'backup_id' => $backupId,
            'user_id' => $userId ?: auth()->id(),
            'status' => $status,
            'details' => json_encode($details),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Log::info("Activity logged: {$operationType}", [
            'log_id' => $log->id,
            'backup_id' => $backupId,
            'status' => $status,
        ]);

        return $log;
    }

    /**
     * Get recent activity logs
     */
    public function getRecentActivity(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return BackupActivityLog::with(['backup', 'user'])
            ->recent($limit)
            ->get();
    }

    /**
     * Get activity logs with filters
     */
    public function getActivityLogs(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = BackupActivityLog::with(['backup', 'user']);

        // Filter by operation type
        if (!empty($filters['operation_type']) && $filters['operation_type'] !== 'all') {
            $query->where('operation_type', $filters['operation_type']);
        }

        // Filter by status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 50);
    }
}
