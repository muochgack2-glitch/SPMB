<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Upload to Google Drive...\n\n";

try {
    $backupId = 8; // Change this to your backup ID
    
    $backup = App\Models\DatabaseBackup::findOrFail($backupId);
    echo "Found backup: {$backup->filename}\n";
    echo "File path: {$backup->path}\n";
    echo "File exists: " . ($backup->fileExists() ? 'Yes' : 'No') . "\n\n";
    
    if (!$backup->fileExists()) {
        echo "❌ File not found!\n";
        exit(1);
    }
    
    echo "Starting upload...\n";
    $googleDrive = new App\Services\GoogleDriveService();
    
    $result = $googleDrive->uploadFile(
        $backup->path,
        $backup->filename,
        ['description' => $backup->backup_notes ?? 'SPMB Database Backup']
    );
    
    echo "\nResult:\n";
    print_r($result);
    
    if ($result['success']) {
        echo "\n✅ UPLOAD SUCCESS!\n";
        echo "File ID: " . $result['file_id'] . "\n";
        echo "Web Link: " . $result['web_view_link'] . "\n";
        
        // Update database
        $backup->update([
            'google_drive_file_id' => $result['file_id'],
            'google_drive_web_link' => $result['web_view_link'],
            'uploaded_to_drive_at' => now(),
        ]);
        echo "\n✅ Database updated!\n";
    } else {
        echo "\n❌ UPLOAD FAILED!\n";
        echo "Error: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ EXCEPTION!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
