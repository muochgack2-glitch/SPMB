<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseBackup;
use App\Services\BackupService;
use App\Services\RestoreService;
use App\Services\ActivityLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class BackupController extends Controller
{
    protected $backupService;
    protected $restoreService;
    protected $activityLogger;

    public function __construct(
        BackupService $backupService,
        RestoreService $restoreService,
        ActivityLoggerService $activityLogger
    ) {
        $this->backupService = $backupService;
        $this->restoreService = $restoreService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display backup list
     */
    public function index(Request $request)
    {
        $query = DatabaseBackup::with('creator')->orderBy('created_at', 'desc');

        // Filter by source type
        if ($request->has('source') && $request->source !== 'all') {
            $query->where('source_type', $request->source);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('filename', 'like', '%' . $request->search . '%')
                  ->orWhere('backup_notes', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $backups = $query->paginate(20);

        // Get statistics
        $statistics = $this->backupService->getStatistics();

        return view('admin.backups.index', compact('backups', 'statistics'));
    }

    /**
     * Create manual backup
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $backup = $this->backupService->createBackup(
                $request->notes,
                'manual',
                'manual_trigger_from_ui'
            );

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat!',
                'backup' => [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'size' => $backup->size_human,
                    'created_at' => $backup->created_at->format('Y-m-d H:i:s'),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Manual backup failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview backup before restore
     */
    public function preview($id)
    {
        try {
            $backup = DatabaseBackup::findOrFail($id);
            $preview = $this->restoreService->previewBackup($backup);

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal preview backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($id)
    {
        try {
            $backup = DatabaseBackup::findOrFail($id);

            if (!$backup->fileExists()) {
                abort(404, 'Backup file not found');
            }

            // Verify integrity before download
            if (!$backup->verifyIntegrity()) {
                abort(500, 'Backup file corrupted');
            }

            return response()->download($backup->path, $backup->filename);

        } catch (Exception $e) {
            Log::error('Download backup failed', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to download backup');
        }
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, $id)
    {
        try {
            $request->validate([
                'confirmation' => 'required|string',
                'create_pre_restore_backup' => 'boolean',
            ]);

            $backup = DatabaseBackup::findOrFail($id);

            // Verify confirmation (user must type database name)
            $dbName = config('database.connections.mysql.database');
            if ($request->confirmation !== $dbName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfirmasi tidak sesuai. Ketik nama database dengan benar.',
                ], 422);
            }

            $createPreRestoreBackup = $request->get('create_pre_restore_backup', true);

            $result = $this->restoreService->executeRestore($backup, $createPreRestoreBackup);

            // CRITICAL: Regenerate session after restore to prevent session conflicts
            // This ensures Laravel uses the newly created sessions table
            $request->session()->regenerate();
            Log::info('Session regenerated after restore');

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'duration_seconds' => $result['duration_seconds'],
                'pre_restore_backup_id' => $result['pre_restore_backup_id'],
            ]);

        } catch (Exception $e) {
            Log::error('Restore failed', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Restore gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete backup
     */
    public function delete(Request $request, $id)
    {
        try {
            $request->validate([
                'confirmation' => 'required|string|in:DELETE',
            ]);

            $backup = DatabaseBackup::findOrFail($id);

            // Prevent deleting the most recent backup
            $mostRecent = DatabaseBackup::newest()->first();
            if ($backup->id === $mostRecent->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus backup terbaru. Harus ada minimal 1 backup.',
                ], 422);
            }

            $this->backupService->deleteBackup($backup);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dihapus!',
            ]);

        } catch (Exception $e) {
            Log::error('Delete backup failed', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify backup integrity
     */
    public function verify($id)
    {
        try {
            $backup = DatabaseBackup::findOrFail($id);
            $result = $this->backupService->verifyIntegrity($backup);

            return response()->json([
                'success' => $result['valid'],
                'result' => $result,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show activity logs
     */
    public function activityLogs(Request $request)
    {
        $filters = [
            'operation_type' => $request->get('operation', 'all'),
            'status' => $request->get('status', 'all'),
            'user_id' => $request->get('user_id'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'per_page' => $request->get('per_page', 50),
        ];

        $logs = $this->activityLogger->getActivityLogs($filters);

        return view('admin.backups.activity-logs', compact('logs', 'filters'));
    }

    /**
     * Upload backup file
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|file|max:512000', // Max 500MB, remove mimes validation
                'notes' => 'nullable|string|max:500',
            ]);

            $file = $request->file('backup_file');
            
            // Manual validation for file extension
            $originalName = $file->getClientOriginalName();
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            // Check if it's .sql or .gz (for .sql.gz)
            $isValid = $extension === 'sql' || $extension === 'gz' || str_ends_with(strtolower($originalName), '.sql.gz');
            
            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file format. Only .sql or .sql.gz files are allowed.',
                ], 422);
            }

            $notes = $request->input('notes', 'Uploaded backup file');
            $isCompressed = $extension === 'gz' || str_ends_with(strtolower($originalName), '.sql.gz');

            // Generate filename
            $timestamp = now()->format('Y-m-d_His');
            $dbName = config('database.connections.mysql.database');
            
            if ($isCompressed) {
                $filename = "uploaded_{$dbName}_{$timestamp}.sql.gz";
            } else {
                $filename = "uploaded_{$dbName}_{$timestamp}.sql.gz";
            }

            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $destinationPath = $backupDir . '/' . $filename;

            // If file is not compressed, compress it
            if (!$isCompressed) {
                // Read uploaded file and compress it directly
                $uploadedFilePath = $file->getRealPath();
                
                Log::info('Compressing uploaded file', [
                    'source' => $uploadedFilePath,
                    'source_size' => filesize($uploadedFilePath),
                    'destination' => $destinationPath,
                ]);
                
                $this->compressFile($uploadedFilePath, $destinationPath);
                
                Log::info('Compression completed', [
                    'compressed_size' => filesize($destinationPath),
                ]);
            } else {
                $file->move($backupDir, $filename);
            }

            // Calculate MD5
            $md5Hash = md5_file($destinationPath);

            // Extract metadata
            $metadata = $this->extractMetadata($destinationPath);

            // Create database record
            $backup = DatabaseBackup::create([
                'filename' => $filename,
                'path' => $destinationPath,
                'size_bytes' => filesize($destinationPath),
                'md5_hash' => $md5Hash,
                'database_name' => $dbName,
                'source_type' => 'manual',
                'source_context' => 'uploaded_via_ui',
                'tahun_ajaran_context' => \App\Models\SettingSystem::get('active_tahun_ajaran', null),
                'total_tables' => $metadata['total_tables'],
                'estimated_records' => $metadata['estimated_records'],
                'backup_notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // Log success
            $this->activityLogger->logBackupCreated($backup, 0);

            return response()->json([
                'success' => true,
                'message' => 'Backup file uploaded successfully!',
                'backup' => [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'size' => $backup->size_human,
                    'tables' => $backup->total_tables,
                    'records' => number_format($backup->estimated_records),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Upload backup failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compress file helper
     */
    private function compressFile(string $sourcePath, string $destPath): void
    {
        $sourceHandle = fopen($sourcePath, 'rb');
        $destHandle = gzopen($destPath, 'wb9');

        if (!$sourceHandle || !$destHandle) {
            throw new \Exception('Failed to open files for compression');
        }

        while (!feof($sourceHandle)) {
            $chunk = fread($sourceHandle, 8192);
            gzwrite($destHandle, $chunk);
        }

        fclose($sourceHandle);
        gzclose($destHandle);
    }

    /**
     * Extract metadata helper
     * Reads entire SQL file to accurately count tables and records
     * Handles multi-line INSERT statements
     */
    private function extractMetadata(string $compressedPath): array
    {
        $handle = gzopen($compressedPath, 'rb');
        if (!$handle) {
            return ['total_tables' => 0, 'estimated_records' => 0];
        }

        $totalTables = 0;
        $estimatedRecords = 0;
        $tableCounts = []; // Track records per table
        $currentTable = null;
        $buffer = ''; // Buffer for multi-line statements

        Log::info('Extracting metadata from uploaded backup...');

        while (!gzeof($handle)) {
            $line = gzgets($handle);
            
            // Accumulate lines into buffer
            $buffer .= $line;

            // Count CREATE TABLE statements
            if (preg_match('/CREATE TABLE `?(\w+)`?/i', $line, $matches)) {
                $totalTables++;
                $currentTable = $matches[1];
                $tableCounts[$currentTable] = 0;
            }

            // Check if we have a complete INSERT statement (ends with );)
            if (preg_match('/;\s*$/m', $line)) {
                // Now count records in the buffer
                if (preg_match('/INSERT INTO `?(\w+)`?/i', $buffer, $matches)) {
                    $tableName = $matches[1];
                    
                    // Count how many value sets: ),(
                    $valueCount = substr_count($buffer, '),(') + 1;
                    $estimatedRecords += $valueCount;
                    
                    if (isset($tableCounts[$tableName])) {
                        $tableCounts[$tableName] += $valueCount;
                    } else {
                        $tableCounts[$tableName] = $valueCount;
                    }
                }
                
                // Clear buffer after processing complete statement
                $buffer = '';
            }
        }

        gzclose($handle);

        Log::info('Metadata extraction completed', [
            'total_tables' => $totalTables,
            'estimated_records' => $estimatedRecords,
            'top_tables' => array_slice($tableCounts, 0, 5, true),
        ]);

        return [
            'total_tables' => $totalTables,
            'estimated_records' => $estimatedRecords,
        ];
    }
}
