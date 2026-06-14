<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use App\Models\SettingSystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BackupService
{
    protected $activityLogger;

    public function __construct()
    {
        $this->activityLogger = app(ActivityLoggerService::class);
    }

    /**
     * Create database backup
     * 
     * @param string|null $notes Optional backup notes
     * @param string $sourceType Type: manual, auto, scheduled, pre_operation
     * @param string|null $sourceContext Context description
     * @return DatabaseBackup
     * @throws Exception
     */
    public function createBackup(?string $notes = null, string $sourceType = 'manual', ?string $sourceContext = null): DatabaseBackup
    {
        $startTime = microtime(true);

        try {
            // 1. Validate environment
            $this->validateEnvironment();

            // 2. Get database config
            $dbName = config('database.connections.mysql.database');
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // 3. Generate filename
            $timestamp = now()->format('Y-m-d_His');
            $filename = "backup_{$dbName}_{$timestamp}.sql";
            $compressedFilename = "{$filename}.gz";
            $backupDir = storage_path('app/backups');
            $tempPath = "{$backupDir}/{$filename}";
            $finalPath = "{$backupDir}/{$compressedFilename}";

            // 4. Ensure directory exists
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // 5. Execute mysqldump
            $mysqldumpPath = $this->getMysqldumpPath();
            $command = sprintf(
                '"%s" --host=%s --user=%s --password=%s --single-transaction --quick --lock-tables=false %s > "%s" 2>&1',
                $mysqldumpPath,
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                $tempPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('mysqldump failed: ' . implode("\n", $output));
            }

            // 6. Verify temp file
            if (!file_exists($tempPath) || filesize($tempPath) < 1000) {
                throw new Exception('Backup file invalid or too small');
            }

            // 7. Compress with gzip
            $this->compressFile($tempPath, $finalPath);
            @unlink($tempPath); // Remove uncompressed file

            // 8. Calculate MD5
            $md5Hash = md5_file($finalPath);
            $sizeBytes = filesize($finalPath);

            // 9. Extract metadata
            $metadata = $this->extractMetadata($finalPath);

            // 10. Get active tahun ajaran
            $activeTahunAjaran = SettingSystem::get('active_tahun_ajaran', null);

            // 11. Create database record
            $backup = DatabaseBackup::create([
                'filename' => $compressedFilename,
                'path' => $finalPath,
                'size_bytes' => $sizeBytes,
                'md5_hash' => $md5Hash,
                'database_name' => $dbName,
                'source_type' => $sourceType,
                'source_context' => $sourceContext,
                'tahun_ajaran_context' => $activeTahunAjaran,
                'total_tables' => $metadata['total_tables'],
                'estimated_records' => $metadata['estimated_records'],
                'backup_notes' => $notes,
                'created_by' => auth()->id() ?? 1, // Default to user 1 if no auth
            ]);

            $duration = microtime(true) - $startTime;

            // 12. Log activity
            $this->activityLogger->logBackupCreated($backup, $duration);

            // 13. Cleanup old backups based on source type
            if ($sourceType === 'auto' || $sourceType === 'pre_operation') {
                $this->cleanupOldBackups($sourceType);
            }

            Log::info('Backup created successfully', [
                'backup_id' => $backup->id,
                'filename' => $compressedFilename,
                'size' => $backup->size_human,
                'duration' => round($duration, 2) . 's',
                'source_type' => $sourceType,
            ]);

            return $backup;

        } catch (Exception $e) {
            $duration = microtime(true) - $startTime;
            
            // Log failure
            $this->activityLogger->logBackupFailed($e->getMessage(), $duration, $sourceType);

            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'source_type' => $sourceType,
                'duration' => round($duration, 2) . 's',
            ]);

            throw $e;
        }
    }

    /**
     * Compress SQL file to .gz
     */
    private function compressFile(string $sourcePath, string $destPath): void
    {
        $sourceHandle = fopen($sourcePath, 'rb');
        $destHandle = gzopen($destPath, 'wb9'); // wb9 = maximum compression

        if (!$sourceHandle || !$destHandle) {
            throw new Exception('Failed to open files for compression');
        }

        while (!feof($sourceHandle)) {
            $chunk = fread($sourceHandle, 8192); // 8KB chunks
            gzwrite($destHandle, $chunk);
        }

        fclose($sourceHandle);
        gzclose($destHandle);
    }

    /**
     * Extract metadata from compressed backup
     */
    private function extractMetadata(string $compressedPath): array
    {
        $handle = gzopen($compressedPath, 'rb');
        if (!$handle) {
            throw new Exception('Failed to open compressed backup for metadata extraction');
        }

        $totalTables = 0;
        $estimatedRecords = 0;
        $bytesRead = 0;
        $maxBytesToRead = 5 * 1024 * 1024; // Read first 5MB only for performance

        while (!gzeof($handle) && $bytesRead < $maxBytesToRead) {
            $line = gzgets($handle);
            $bytesRead += strlen($line);

            // Count CREATE TABLE statements
            if (preg_match('/^CREATE TABLE/i', $line)) {
                $totalTables++;
            }

            // Count INSERT statements (approximate record count)
            if (preg_match('/^INSERT INTO/i', $line)) {
                // Count number of value sets: (...),(...),(...)
                $valueCount = substr_count($line, '),(') + 1;
                $estimatedRecords += $valueCount;
            }
        }

        gzclose($handle);

        return [
            'total_tables' => $totalTables,
            'estimated_records' => $estimatedRecords,
        ];
    }

    /**
     * Validate that mysqldump is available
     */
    private function validateEnvironment(): void
    {
        // Try to find mysqldump
        $mysqldumpPath = $this->findMysqldump();
        
        if (!$mysqldumpPath) {
            throw new Exception(
                'mysqldump is not installed or not in PATH. ' .
                'Please install MySQL client tools. ' .
                'On Windows, you can find mysqldump in your MySQL installation folder (e.g., C:\Program Files\MySQL\MySQL Server X.X\bin\mysqldump.exe)'
            );
        }
    }
    
    /**
     * Find mysqldump executable
     */
    private function findMysqldump(): ?string
    {
        // Try standard command
        exec('mysqldump --version 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            return 'mysqldump';
        }
        
        // Try common Windows paths
        $possiblePaths = [
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 9.0\bin\mysqldump.exe',
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
            'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Get mysqldump path
     */
    private function getMysqldumpPath(): string
    {
        $path = $this->findMysqldump();
        return $path ?: 'mysqldump';
    }
    
    /**
     * Get mysql path
     */
    private function getMysqlPath(): string
    {
        // Similar to mysqldump
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

    /**
     * Cleanup old backups based on retention policy
     */
    public function cleanupOldBackups(string $sourceType = 'auto', int $keepCount = 10): int
    {
        // Get old backups for this source type
        $backups = DatabaseBackup::where('source_type', $sourceType)
            ->orderBy('created_at', 'desc')
            ->skip($keepCount)
            ->take(100) // Limit to prevent memory issues
            ->get();

        $deleted = 0;

        foreach ($backups as $backup) {
            try {
                // Delete files
                $backup->deleteFiles();
                
                // Delete record
                $backup->delete();
                
                $deleted++;

                Log::info('Old backup deleted', [
                    'backup_id' => $backup->id,
                    'filename' => $backup->filename,
                    'age_days' => $backup->age_in_days,
                ]);

            } catch (Exception $e) {
                Log::warning('Failed to delete old backup', [
                    'backup_id' => $backup->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $deleted;
    }

    /**
     * Get backup statistics
     */
    public function getStatistics(): array
    {
        $totalBackups = DatabaseBackup::count();
        $totalSize = DatabaseBackup::sum('size_bytes');
        $manualBackups = DatabaseBackup::where('source_type', 'manual')->count();
        $autoBackups = DatabaseBackup::where('source_type', 'auto')->count();
        $oldestBackup = DatabaseBackup::oldest()->first();
        $newestBackup = DatabaseBackup::newest()->first();

        return [
            'total_backups' => $totalBackups,
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'manual_backups' => $manualBackups,
            'auto_backups' => $autoBackups,
            'oldest_backup' => $oldestBackup,
            'newest_backup' => $newestBackup,
            'average_size' => $totalBackups > 0 ? $totalSize / $totalBackups : 0,
            'average_size_human' => $totalBackups > 0 ? $this->formatBytes($totalSize / $totalBackups) : '0 B',
        ];
    }

    /**
     * Verify backup integrity
     */
    public function verifyIntegrity(DatabaseBackup $backup): array
    {
        $startTime = microtime(true);

        try {
            // Check if file exists
            if (!$backup->fileExists()) {
                throw new Exception('Backup file not found on disk');
            }

            // Verify MD5 hash
            $currentHash = md5_file($backup->path);
            $md5Match = $currentHash === $backup->md5_hash;

            if (!$md5Match) {
                throw new Exception('MD5 hash mismatch - backup file may be corrupted');
            }

            $duration = microtime(true) - $startTime;

            // Log success
            $this->activityLogger->logIntegrityCheck($backup, true, $duration);

            return [
                'valid' => true,
                'file_exists' => true,
                'md5_match' => true,
                'message' => 'Backup integrity verified successfully',
            ];

        } catch (Exception $e) {
            $duration = microtime(true) - $startTime;

            // Log failure
            $this->activityLogger->logIntegrityCheck($backup, false, $duration, $e->getMessage());

            return [
                'valid' => false,
                'file_exists' => $backup->fileExists(),
                'md5_match' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete backup
     */
    public function deleteBackup(DatabaseBackup $backup): void
    {
        $startTime = microtime(true);

        try {
            // Delete files
            $backup->deleteFiles();

            // Log before delete
            $this->activityLogger->logBackupDeleted($backup, microtime(true) - $startTime);

            // Delete record
            $backup->delete();

            Log::info('Backup deleted', [
                'backup_id' => $backup->id,
                'filename' => $backup->filename,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to delete backup', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
