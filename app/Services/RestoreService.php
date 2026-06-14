<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RestoreService
{
    protected $backupService;
    protected $activityLogger;

    public function __construct()
    {
        $this->backupService = app(BackupService::class);
        $this->activityLogger = app(ActivityLoggerService::class);
    }

    /**
     * Preview backup before restore
     */
    public function previewBackup(DatabaseBackup $backup): array
    {
        try {
            // 1. Verify backup exists and valid
            if (!$backup->fileExists()) {
                throw new Exception('Backup file not found');
            }

            // 2. Get current database state
            $currentState = $this->getCurrentDatabaseState();

            // 3. Extract detailed backup metadata from file
            $backupDetails = $this->extractBackupDetails($backup->path);
            
            $backupMetadata = [
                'total_tables' => $backup->total_tables,
                'estimated_records' => $backup->estimated_records,
                'created_at' => $backup->created_at,
                'size' => $backup->size_human,
                'tahun_ajaran_context' => $backup->tahun_ajaran_context,
                // Add detailed counts from file
                'pendaftar_count' => $backupDetails['pendaftar'] ?? 0,
                'users_count' => $backupDetails['users'] ?? 0,
                'jurusan_count' => $backupDetails['jurusan'] ?? 0,
            ];

            // 4. Compare states
            $comparison = $this->compareStates($currentState, $backupMetadata);

            // 5. Calculate warnings
            $ageInDays = $backup->age_in_days;
            $showAgeWarning = $ageInDays > 30;

            return [
                'backup' => $backup,
                'current_state' => $currentState,
                'backup_metadata' => $backupMetadata,
                'comparison' => $comparison,
                'age_in_days' => $ageInDays,
                'show_age_warning' => $showAgeWarning,
                'warnings' => $this->generateWarnings($backup, $currentState, $comparison),
            ];

        } catch (Exception $e) {
            Log::error('Preview backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
    
    /**
     * Extract detailed record counts from backup file
     * Handles multi-line INSERT statements
     */
    private function extractBackupDetails(string $compressedPath): array
    {
        $handle = gzopen($compressedPath, 'rb');
        if (!$handle) {
            return [];
        }

        $tableCounts = [];
        $buffer = ''; // Buffer for multi-line statements

        while (!gzeof($handle)) {
            $line = gzgets($handle);
            
            // Accumulate lines
            $buffer .= $line;

            // Check if statement is complete (ends with ;)
            if (preg_match('/;\s*$/m', $line)) {
                // Extract table name and count records
                if (preg_match('/INSERT INTO `?(\w+)`?/i', $buffer, $matches)) {
                    $tableName = $matches[1];
                    if (!isset($tableCounts[$tableName])) {
                        $tableCounts[$tableName] = 0;
                    }
                    // Count records in buffer
                    $valueCount = substr_count($buffer, '),(') + 1;
                    $tableCounts[$tableName] += $valueCount;
                }
                
                // Clear buffer
                $buffer = '';
            }
        }

        gzclose($handle);

        Log::info('Backup details extracted', $tableCounts);

        return $tableCounts;
    }

    /**
     * Get current database state
     */
    private function getCurrentDatabaseState(): array
    {
        $tables = DB::select('SHOW TABLES');
        $tableStats = [];

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            
            try {
                $count = DB::table($tableName)->count();
                $tableStats[$tableName] = $count;
            } catch (Exception $e) {
                $tableStats[$tableName] = 0;
            }
        }

        return [
            'total_tables' => count($tables),
            'table_stats' => $tableStats,
            'pendaftar_count' => $tableStats['pendaftar'] ?? 0,
            'tahun_ajaran_count' => $tableStats['tahun_ajaran'] ?? 0,
            'whatsapp_logs_count' => $tableStats['whatsapp_logs'] ?? 0,
            'users_count' => $tableStats['users'] ?? 0,
        ];
    }

    /**
     * Compare current state with backup
     */
    private function compareStates(array $current, array $backup): array
    {
        $comparison = [];

        // Key tables to compare
        $keyTables = ['pendaftar', 'tahun_ajaran', 'whatsapp_logs', 'users', 'jurusan'];

        foreach ($keyTables as $table) {
            $currentCount = $current['table_stats'][$table] ?? 0;
            // We don't have per-table counts in backup, so we'll show generic warning
            
            $comparison[$table] = [
                'current' => $currentCount,
                'table_exists' => isset($current['table_stats'][$table]),
            ];
        }

        return $comparison;
    }

    /**
     * Generate warnings for restore
     */
    private function generateWarnings(DatabaseBackup $backup, array $currentState, array $comparison): array
    {
        $warnings = [];

        // Age warning
        if ($backup->age_in_days > 30) {
            $warnings[] = [
                'type' => 'warning',
                'icon' => 'fa-clock',
                'message' => "Backup ini berusia {$backup->age_in_days} hari. Data mungkin sudah outdated.",
            ];
        }

        // Data loss warning
        if ($currentState['pendaftar_count'] > 0) {
            $warnings[] = [
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'message' => "PERINGATAN: Database saat ini memiliki {$currentState['pendaftar_count']} pendaftar. Data ini akan diganti dengan data dari backup!",
            ];
        }

        // Different tahun ajaran context
        $currentTahunAjaran = \App\Models\SettingSystem::get('active_tahun_ajaran', null);
        if ($backup->tahun_ajaran_context && $backup->tahun_ajaran_context !== $currentTahunAjaran) {
            $warnings[] = [
                'type' => 'info',
                'icon' => 'fa-info-circle',
                'message' => "Backup dibuat saat tahun ajaran aktif: {$backup->tahun_ajaran_context}. Tahun ajaran saat ini: {$currentTahunAjaran}",
            ];
        }

        return $warnings;
    }

    /**
     * Execute restore operation
     */
    public function executeRestore(
        DatabaseBackup $backup,
        bool $createPreRestoreBackup = true
    ): array {
        $startTime = microtime(true);

        // Log restore started (but this record will be lost after restore drops tables)
        $activityLog = null;
        try {
            $activityLog = $this->activityLogger->logRestoreStarted($backup);
        } catch (\Exception $e) {
            Log::warning('Could not create activity log', ['error' => $e->getMessage()]);
        }

        // NOTE: We don't use DB::beginTransaction() here because we're running
        // external mysql command via exec(), which has its own transaction handling

        try {
            // 1. Verify backup file
            if (!$backup->fileExists()) {
                throw new Exception('Backup file not found');
            }

            $currentHash = md5_file($backup->path);
            if ($currentHash !== $backup->md5_hash) {
                throw new Exception('Backup file corrupted (MD5 mismatch)');
            }

            // 2. Create pre-restore backup
            $preRestoreBackup = null;
            if ($createPreRestoreBackup) {
                Log::info('Creating pre-restore backup');
                $preRestoreBackup = $this->backupService->createBackup(
                    "Auto backup before restore of backup #{$backup->id}",
                    'pre_operation',
                    "before_restore_{$backup->id}"
                );
            }

            // 3. Decompress backup
            $tempSqlPath = storage_path('app/backups/temp_restore_' . time() . '.sql');
            $this->decompressFile($backup->path, $tempSqlPath);
            
            // 3a. Remove CREATE DATABASE and USE DATABASE statements to prevent conflicts
            $this->sanitizeSqlFile($tempSqlPath);

            // 3b. PRESERVE backup records before dropping tables
            Log::info('Preserving backup records before restore');
            $backupRecords = \DB::table('database_backups')->get()->toArray();
            $activityLogRecords = \DB::table('backup_activity_logs')->get()->toArray();
            Log::info('Preserved records', [
                'backup_count' => count($backupRecords),
                'activity_log_count' => count($activityLogRecords),
            ]);

            // 4. Get database config
            $dbName = config('database.connections.mysql.database');
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // 5. Drop all existing tables
            Log::info('Dropping all existing tables');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            }

            // 6. Execute SQL restore
            Log::info('Executing SQL restore');
            $mysqlPath = $this->getMysqlPath();
            $command = sprintf(
                '"%s" --host=%s --user=%s --password=%s %s < "%s" 2>&1',
                $mysqlPath,
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                $tempSqlPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('MySQL restore failed: ' . implode("\n", $output));
            }

            // 7. Re-enable foreign keys
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // 8. Verify restore integrity
            Log::info('Verifying restore');
            $this->verifyRestoreIntegrity();

            // 9. Cleanup temp file
            @unlink($tempSqlPath);

            // NO COMMIT needed - exec() mysql command handles its own transaction

            // 10. IMMEDIATELY create Laravel system tables (BLOCKING, SYNCHRONOUS)
            // This MUST complete before any other Laravel operations
            Log::info('Ensuring Laravel system tables exist (BLOCKING operation)');
            $this->ensureSessionsTable();
            
            // 11. Run migrations to create any missing application tables
            // This handles cases where production backup doesn't have newer tables
            Log::info('Running migrations to ensure all application tables exist');
            try {
                \Artisan::call('migrate', ['--force' => true]);
                Log::info('Migrations completed successfully');
            } catch (\Exception $e) {
                Log::warning('Failed to run migrations (non-critical)', ['error' => $e->getMessage()]);
            }
            
            // 11b. RESTORE backup records (so backup list doesn't disappear!)
            Log::info('Restoring backup records');
            try {
                if (!empty($backupRecords)) {
                    foreach ($backupRecords as $record) {
                        \DB::table('database_backups')->insert((array)$record);
                    }
                    Log::info('Restored backup records', ['count' => count($backupRecords)]);
                }
                
                if (!empty($activityLogRecords)) {
                    foreach ($activityLogRecords as $record) {
                        \DB::table('backup_activity_logs')->insert((array)$record);
                    }
                    Log::info('Restored activity log records', ['count' => count($activityLogRecords)]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to restore backup records', ['error' => $e->getMessage()]);
            }

            $duration = microtime(true) - $startTime;

            // 12. Create NEW activity log after migrations (old log was dropped during restore)
            try {
                // Create fresh activity log with completion status
                $newActivityLog = \App\Models\BackupActivityLog::create([
                    'backup_id' => $backup->id,
                    'operation_type' => 'restore_completed',
                    'status' => 'success',
                    'duration_seconds' => $duration,
                    'performed_by' => auth()->id(),
                    'metadata' => json_encode([
                        'pre_restore_backup_id' => $preRestoreBackup?->id,
                        'backup_filename' => $backup->filename,
                        'restore_timestamp' => now()->toIso8601String(),
                    ]),
                ]);
                
                Log::info('Activity log created after restore', ['log_id' => $newActivityLog->id]);
            } catch (\Exception $e) {
                Log::warning('Failed to log restore completion', ['error' => $e->getMessage()]);
            }

            Log::info('Restore completed successfully', [
                'backup_id' => $backup->id,
                'duration' => round($duration, 2) . 's',
                'pre_restore_backup_id' => $preRestoreBackup?->id,
            ]);

            return [
                'success' => true,
                'duration_seconds' => round($duration, 2),
                'pre_restore_backup_id' => $preRestoreBackup?->id,
                'message' => 'Database berhasil di-restore!',
            ];

        } catch (Exception $e) {
            // NO ROLLBACK needed - exec() mysql command is atomic

            // Cleanup temp file
            if (isset($tempSqlPath) && file_exists($tempSqlPath)) {
                @unlink($tempSqlPath);
            }

            // Log failure (might fail if tables don't exist)
            try {
                $this->activityLogger->logRestoreFailed($activityLog, $e);
            } catch (\Exception $logError) {
                Log::warning('Failed to log restore failure', ['error' => $logError->getMessage()]);
            }

            Log::error('Restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Decompress .gz file to .sql
     */
    private function decompressFile(string $compressedPath, string $destPath): void
    {
        $sourceHandle = gzopen($compressedPath, 'rb');
        $destHandle = fopen($destPath, 'wb');

        if (!$sourceHandle || !$destHandle) {
            throw new Exception('Failed to open files for decompression');
        }

        while (!gzeof($sourceHandle)) {
            $chunk = gzread($sourceHandle, 8192); // 8KB chunks
            fwrite($destHandle, $chunk);
        }

        gzclose($sourceHandle);
        fclose($destHandle);
    }
    
    /**
     * Sanitize SQL file to remove problematic statements
     * - Remove CREATE DATABASE statements
     * - Remove USE DATABASE statements
     * - These will be handled by the mysql command with --database flag
     */
    private function sanitizeSqlFile(string $sqlPath): void
    {
        Log::info('Sanitizing SQL file to remove CREATE DATABASE statements');
        
        $tempPath = $sqlPath . '.tmp';
        $sourceHandle = fopen($sqlPath, 'r');
        $destHandle = fopen($tempPath, 'w');
        
        if (!$sourceHandle || !$destHandle) {
            throw new Exception('Failed to open files for sanitization');
        }
        
        $lineNumber = 0;
        $skippedLines = 0;
        
        while (($line = fgets($sourceHandle)) !== false) {
            $lineNumber++;
            $trimmedLine = trim($line);
            
            // Skip CREATE DATABASE statements
            if (preg_match('/^CREATE\s+DATABASE/i', $trimmedLine)) {
                Log::debug("Skipping CREATE DATABASE at line {$lineNumber}");
                $skippedLines++;
                continue;
            }
            
            // Skip USE database statements
            if (preg_match('/^USE\s+`?[\w-]+`?\s*;/i', $trimmedLine)) {
                Log::debug("Skipping USE DATABASE at line {$lineNumber}");
                $skippedLines++;
                continue;
            }
            
            // Write all other lines
            fwrite($destHandle, $line);
        }
        
        fclose($sourceHandle);
        fclose($destHandle);
        
        // Replace original with sanitized version
        unlink($sqlPath);
        rename($tempPath, $sqlPath);
        
        Log::info("SQL file sanitized: skipped {$skippedLines} problematic lines");
    }

    /**
     * Verify restore integrity
     * Only check CRITICAL tables that must exist
     */
    private function verifyRestoreIntegrity(): void
    {
        // Only check MOST CRITICAL tables - these MUST exist in any production backup
        $criticalTables = ['users', 'pendaftar'];

        foreach ($criticalTables as $table) {
            $exists = DB::select("SHOW TABLES LIKE '{$table}'");
            
            if (empty($exists)) {
                throw new Exception("Critical table '{$table}' not found after restore");
            }
        }

        // Check that we can query tables
        try {
            DB::table('users')->count();
            DB::table('pendaftar')->count();
        } catch (Exception $e) {
            throw new Exception('Failed to query tables after restore: ' . $e->getMessage());
        }
        
        Log::info('Restore integrity verified: critical tables exist and queryable');
    }
    
    /**
     * Ensure sessions table exists after restore
     * THIS IS A CRITICAL BLOCKING OPERATION - MUST COMPLETE BEFORE RETURNING
     */
    private function ensureSessionsTable(): void
    {
        Log::info('[CRITICAL] Starting session table recreation (BLOCKING)');
        
        // 1. Sessions table - HIGHEST PRIORITY (Laravel tries to write here immediately)
        try {
            $exists = DB::select("SHOW TABLES LIKE 'sessions'");
            
            if (empty($exists)) {
                Log::info('[CRITICAL] Sessions table not found, creating NOW...');
                
                DB::statement("
                    CREATE TABLE sessions (
                        id VARCHAR(255) NOT NULL PRIMARY KEY,
                        user_id BIGINT UNSIGNED NULL,
                        ip_address VARCHAR(45) NULL,
                        user_agent TEXT NULL,
                        payload LONGTEXT NOT NULL,
                        last_activity INT NOT NULL,
                        INDEX sessions_user_id_index (user_id),
                        INDEX sessions_last_activity_index (last_activity)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                Log::info('[CRITICAL] Sessions table created SUCCESSFULLY');
            } else {
                Log::info('[CRITICAL] Sessions table already exists, verifying structure...');
                
                // Truncate existing sessions to prevent conflicts
                try {
                    DB::statement("TRUNCATE TABLE sessions");
                    Log::info('[CRITICAL] Cleared existing sessions');
                } catch (\Exception $truncateError) {
                    Log::warning('[CRITICAL] Could not truncate sessions', ['error' => $truncateError->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Log::error('[CRITICAL] FAILED to create sessions table', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('CRITICAL: Failed to create sessions table: ' . $e->getMessage());
        }
        
        // 2. Cache table
        try {
            $cacheExists = DB::select("SHOW TABLES LIKE 'cache'");
            if (empty($cacheExists)) {
                Log::info('Cache table not found, creating it...');
                
                DB::statement("
                    CREATE TABLE cache (
                        `key` VARCHAR(255) NOT NULL PRIMARY KEY,
                        value MEDIUMTEXT NOT NULL,
                        expiration INT NOT NULL,
                        INDEX cache_expiration_index (expiration)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                DB::statement("
                    CREATE TABLE cache_locks (
                        `key` VARCHAR(255) NOT NULL PRIMARY KEY,
                        owner VARCHAR(255) NOT NULL,
                        expiration INT NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                Log::info('Cache tables created successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create cache tables (non-critical)', ['error' => $e->getMessage()]);
        }
        
        // 3. Jobs table
        try {
            $jobsExists = DB::select("SHOW TABLES LIKE 'jobs'");
            if (empty($jobsExists)) {
                Log::info('Jobs table not found, creating it...');
                
                DB::statement("
                    CREATE TABLE jobs (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        queue VARCHAR(255) NOT NULL,
                        payload LONGTEXT NOT NULL,
                        attempts TINYINT UNSIGNED NOT NULL,
                        reserved_at INT UNSIGNED NULL,
                        available_at INT UNSIGNED NOT NULL,
                        created_at INT UNSIGNED NOT NULL,
                        INDEX jobs_queue_index (queue)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                DB::statement("
                    CREATE TABLE job_batches (
                        id VARCHAR(255) NOT NULL PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        total_jobs INT NOT NULL,
                        pending_jobs INT NOT NULL,
                        failed_jobs INT NOT NULL,
                        failed_job_ids LONGTEXT NOT NULL,
                        options MEDIUMTEXT NULL,
                        cancelled_at INT NULL,
                        created_at INT NOT NULL,
                        finished_at INT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                DB::statement("
                    CREATE TABLE failed_jobs (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        uuid VARCHAR(255) NOT NULL UNIQUE,
                        connection TEXT NOT NULL,
                        queue TEXT NOT NULL,
                        payload LONGTEXT NOT NULL,
                        exception LONGTEXT NOT NULL,
                        failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                Log::info('Jobs tables created successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create jobs tables (non-critical)', ['error' => $e->getMessage()]);
        }
        
        // FINAL: Verify sessions table is truly ready
        $verifyExists = DB::select("SHOW TABLES LIKE 'sessions'");
        if (empty($verifyExists)) {
            throw new Exception('CRITICAL: Sessions table verification failed - table does not exist after creation!');
        }
        
        Log::info('[CRITICAL] Session table recreation completed and verified');
    }
    
    /**
     * Get mysql path (same logic as BackupService)
     */
    private function getMysqlPath(): string
    {
        // Try standard command
        exec('mysql --version 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            return 'mysql';
        }
        
        // Try common Windows paths
        $possiblePaths = [
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
            'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe',
            'C:\Program Files\MySQL\MySQL Server 9.0\bin\mysql.exe',
            'C:\xampp\mysql\bin\mysql.exe',
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe',
            'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return 'mysql';
    }
}
