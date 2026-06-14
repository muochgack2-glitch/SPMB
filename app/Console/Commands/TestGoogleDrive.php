<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class TestGoogleDrive extends Command
{
    protected $signature = 'backup:test-google-drive';
    protected $description = 'Test Google Drive connection and upload capability';

    public function handle()
    {
        $this->info('🔄 Testing Google Drive connection...');
        $this->newLine();

        try {
            $googleDrive = new GoogleDriveService();

            // Test 1: Connection
            $this->info('📡 Test 1: Testing connection...');
            $result = $googleDrive->testConnection();
            
            if ($result['success']) {
                $this->info('✅ Connection successful!');
                $this->line('   Folder: ' . $result['folder_name']);
                $this->line('   Folder ID: ' . $result['folder_id']);
            } else {
                $this->error('❌ Connection failed: ' . $result['message']);
                return 1;
            }
            
            $this->newLine();

            // Test 2: Storage Info
            $this->info('💾 Test 2: Getting storage info...');
            $storageInfo = $googleDrive->getStorageInfo();
            
            if ($storageInfo['success']) {
                $this->info('✅ Storage info retrieved!');
                if (isset($storageInfo['limit'])) {
                    $this->line('   Limit: ' . $this->formatBytes($storageInfo['limit']));
                    $this->line('   Used: ' . $this->formatBytes($storageInfo['usage']));
                    $this->line('   Available: ' . $this->formatBytes($storageInfo['limit'] - $storageInfo['usage']));
                }
            } else {
                $this->warn('⚠️  Could not retrieve storage info (might be service account limitation)');
            }
            
            $this->newLine();

            // Test 3: List Files
            $this->info('📂 Test 3: Listing files in backup folder...');
            $filesResult = $googleDrive->listFiles(10);
            
            if ($filesResult['success']) {
                $this->info('✅ Successfully listed files!');
                $this->line('   Total files: ' . $filesResult['count']);
                
                if ($filesResult['count'] > 0) {
                    $this->line('   Recent files:');
                    foreach (array_slice($filesResult['files'], 0, 5) as $file) {
                        $this->line('     - ' . $file['name'] . ' (' . $this->formatBytes($file['size']) . ')');
                    }
                } else {
                    $this->line('   No files found in folder');
                }
            } else {
                $this->error('❌ Failed to list files: ' . $filesResult['message']);
            }
            
            $this->newLine();

            // Test 4: Upload Test File
            if ($this->confirm('Do you want to test upload? (will create test file)', true)) {
                $this->info('📤 Test 4: Uploading test file...');
                
                // Create test file
                $testContent = "Test backup file created at " . now()->toDateTimeString();
                $testPath = storage_path('app/test-backup-' . time() . '.txt');
                file_put_contents($testPath, $testContent);
                
                $uploadResult = $googleDrive->uploadFile(
                    $testPath,
                    basename($testPath),
                    ['description' => 'Test upload from SPMB Backup System']
                );
                
                if ($uploadResult['success']) {
                    $this->info('✅ Test file uploaded successfully!');
                    $this->line('   File ID: ' . $uploadResult['file_id']);
                    $this->line('   File Name: ' . $uploadResult['file_name']);
                    $this->line('   Size: ' . $this->formatBytes($uploadResult['size']));
                    $this->line('   Link: ' . $uploadResult['web_view_link']);
                    
                    // Clean up test file locally
                    @unlink($testPath);
                    
                    // Ask to delete from Google Drive
                    if ($this->confirm('Delete test file from Google Drive?', true)) {
                        if ($googleDrive->deleteFile($uploadResult['file_id'])) {
                            $this->info('✅ Test file deleted from Google Drive');
                        }
                    }
                } else {
                    $this->error('❌ Upload failed: ' . $uploadResult['message']);
                    @unlink($testPath);
                    return 1;
                }
            }

            $this->newLine();
            $this->info('🎉 All tests passed! Google Drive is ready to use.');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with exception: ' . $e->getMessage());
            $this->line('   File: ' . $e->getFile());
            $this->line('   Line: ' . $e->getLine());
            return 1;
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes == 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), $precision) . ' ' . $sizes[$i];
    }
}
