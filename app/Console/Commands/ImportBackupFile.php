<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackup;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportBackupFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:import 
                            {file : Path to backup file (.sql or .sql.gz)}
                            {--notes= : Optional notes for this backup}
                            {--source=manual : Source type (manual, auto, pre_operation)}
                            {--user=1 : User ID who created this backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import existing backup file into backup system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceFile = $this->argument('file');
        $notes = $this->option('notes');
        $sourceType = $this->option('source');
        $userId = $this->option('user');

        // Validate file exists
        if (!file_exists($sourceFile)) {
            $this->error("File not found: {$sourceFile}");
            return 1;
        }

        // Validate user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("User not found with ID: {$userId}");
            return 1;
        }

        $this->info("Importing backup file...");
        $this->line("Source: {$sourceFile}");
        $this->line("Size: " . $this->formatBytes(filesize($sourceFile)));

        // Determine if file is compressed
        $isCompressed = pathinfo($sourceFile, PATHINFO_EXTENSION) === 'gz';
        $this->line("Compressed: " . ($isCompressed ? 'Yes' : 'No'));

        // Copy file to backups directory
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $dbName = config('database.connections.mysql.database');
        
        // Generate filename
        if ($isCompressed) {
            $filename = "imported_{$dbName}_{$timestamp}.sql.gz";
        } else {
            // If not compressed, compress it
            $this->info("Compressing backup file...");
            $filename = "imported_{$dbName}_{$timestamp}.sql.gz";
            $tempFile = $sourceFile;
            $sourceFile = $backupDir . '/' . $filename;
            
            // Compress
            $this->compressFile($tempFile, $sourceFile);
            $this->info("Compression complete!");
        }

        $destinationPath = $backupDir . '/' . $filename;

        // Copy file
        if (!$isCompressed) {
            // Already compressed above
        } else {
            $this->info("Copying file to backup directory...");
            File::copy($sourceFile, $destinationPath);
        }

        // Calculate MD5
        $this->info("Calculating MD5 hash...");
        $md5Hash = md5_file($destinationPath);

        // Extract metadata
        $this->info("Extracting metadata...");
        $metadata = $this->extractMetadata($destinationPath);

        // Create database record
        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'path' => $destinationPath,
            'size_bytes' => filesize($destinationPath),
            'md5_hash' => $md5Hash,
            'database_name' => $dbName,
            'source_type' => $sourceType,
            'source_context' => 'imported_from_production',
            'tahun_ajaran_context' => null,
            'total_tables' => $metadata['total_tables'],
            'estimated_records' => $metadata['estimated_records'],
            'backup_notes' => $notes ?: 'Imported from production',
            'created_by' => $userId,
        ]);

        $this->info("");
        $this->info("✓ Backup imported successfully!");
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $backup->id],
                ['Filename', $backup->filename],
                ['Size', $backup->size_human],
                ['MD5', $backup->md5_hash],
                ['Tables', $backup->total_tables],
                ['Estimated Records', number_format($backup->estimated_records)],
                ['Created By', $user->name],
            ]
        );

        $this->info("");
        $this->info("You can now restore this backup from the admin panel:");
        $this->line("URL: " . url('/admin/backups'));

        return 0;
    }

    /**
     * Compress SQL file to .gz
     */
    private function compressFile(string $sourcePath, string $destPath): void
    {
        $sourceHandle = fopen($sourcePath, 'rb');
        $destHandle = gzopen($destPath, 'wb9');

        if (!$sourceHandle || !$destHandle) {
            throw new \Exception('Failed to open files for compression');
        }

        $bar = $this->output->createProgressBar();
        $bar->start();

        while (!feof($sourceHandle)) {
            $chunk = fread($sourceHandle, 8192);
            gzwrite($destHandle, $chunk);
            $bar->advance();
        }

        $bar->finish();
        $this->line("");

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
            return ['total_tables' => 0, 'estimated_records' => 0];
        }

        $totalTables = 0;
        $estimatedRecords = 0;
        $bytesRead = 0;
        $maxBytesToRead = 5 * 1024 * 1024; // 5MB

        while (!gzeof($handle) && $bytesRead < $maxBytesToRead) {
            $line = gzgets($handle);
            $bytesRead += strlen($line);

            if (preg_match('/^CREATE TABLE/i', $line)) {
                $totalTables++;
            }

            if (preg_match('/^INSERT INTO/i', $line)) {
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
     * Format bytes
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
