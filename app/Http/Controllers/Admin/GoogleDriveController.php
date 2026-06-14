<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseBackup;
use App\Models\SettingSystem;
use App\Services\GoogleDriveService;
use App\Services\ActivityLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleDriveController extends Controller
{
    protected $activityLogger;

    public function __construct(ActivityLoggerService $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Show Google Drive settings page
     */
    public function settings()
    {
        $settings = [
            'folder_id' => SettingSystem::get('google_drive_folder_id', ''),
            'service_account_json' => SettingSystem::get('google_drive_service_account_json', 'google/google-drive-credentials.json'),
            'auto_upload_enabled' => SettingSystem::get('google_drive_auto_upload', false),
            'keep_local_copy' => SettingSystem::get('google_drive_keep_local', true),
            'credentials_uploaded' => file_exists(storage_path('app/' . SettingSystem::get('google_drive_service_account_json', 'google/google-drive-credentials.json'))),
        ];

        // Get storage info if connected
        $storageInfo = null;
        $recentFiles = [];
        
        try {
            if ($settings['folder_id'] && $settings['credentials_uploaded']) {
                $googleDrive = new GoogleDriveService();
                $storageInfo = $googleDrive->getStorageInfo();
                $filesResult = $googleDrive->listFiles(5);
                if ($filesResult['success']) {
                    $recentFiles = $filesResult['files'];
                }
            }
        } catch (Exception $e) {
            // Silent fail, will show in UI that connection is not ready
        }

        return view('admin.backups.google-drive-settings', compact('settings', 'storageInfo', 'recentFiles'));
    }

    /**
     * Test Google Drive connection
     */
    public function testConnection(Request $request)
    {
        try {
            $googleDrive = new GoogleDriveService();
            $result = $googleDrive->testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi berhasil!',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Google Drive test connection failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Google Drive settings
     */
    public function saveSettings(Request $request)
    {
        try {
            // Debug: Log what we received
            Log::info('Save settings called', [
                'has_file' => $request->hasFile('credentials_file'),
                'all_files' => $request->allFiles(),
                'folder_id' => $request->folder_id,
            ]);

            $request->validate([
                'folder_id' => 'required|string',
                'auto_upload_enabled' => 'nullable|boolean',
                'keep_local_copy' => 'nullable|boolean',
                'credentials_file' => 'nullable|file|mimes:json|max:2048',
            ]);

            // Save folder ID
            SettingSystem::set('google_drive_folder_id', $request->folder_id);
            SettingSystem::set('google_drive_auto_upload', $request->boolean('auto_upload_enabled'));
            SettingSystem::set('google_drive_keep_local', $request->boolean('keep_local_copy'));

            // Handle credentials file upload
            if ($request->hasFile('credentials_file')) {
                $file = $request->file('credentials_file');
                
                Log::info('Processing credentials file', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ]);
                
                // Validate JSON structure
                $json = json_decode(file_get_contents($file->getRealPath()), true);
                if (!isset($json['client_email']) || !isset($json['private_key'])) {
                    Log::error('Invalid credentials file structure');
                    return response()->json([
                        'success' => false,
                        'message' => 'File credentials tidak valid. Pastikan file JSON berisi client_email dan private_key.',
                    ], 422);
                }

                Log::info('Credentials file validated, storing...');
                
                // Save file to 'credentials' disk (storage/app)
                $path = $file->storeAs('google', 'google-drive-credentials.json', 'credentials');
                
                $fullPath = storage_path('app/' . $path);
                
                Log::info('Credentials file stored', [
                    'path' => $path,
                    'full_path' => $fullPath,
                    'file_exists' => file_exists($fullPath),
                ]);
                
                if (!file_exists($fullPath)) {
                    throw new \Exception('File was not saved successfully. Path: ' . $fullPath);
                }
                
                SettingSystem::set('google_drive_service_account_json', $path);

                // Set file permissions (Linux/Mac)
                if (PHP_OS_FAMILY !== 'Windows') {
                    chmod(storage_path('app/' . $path), 0600);
                }
            }

            // Test connection after save (only if credentials exist)
            $testResult = null;
            $credentialsPath = SettingSystem::get('google_drive_service_account_json');
            if ($credentialsPath && file_exists(storage_path('app/' . $credentialsPath))) {
                try {
                    $googleDrive = new GoogleDriveService();
                    $testResult = $googleDrive->testConnection();
                } catch (Exception $e) {
                    $testResult = ['success' => false, 'message' => $e->getMessage()];
                }
            }

            $message = 'Settings berhasil disimpan!';
            if (!$credentialsPath || !file_exists(storage_path('app/' . $credentialsPath))) {
                $message .= ' Silakan upload credentials file untuk menyelesaikan konfigurasi.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'test_result' => $testResult,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to save Google Drive settings', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload backup to Google Drive
     */
    public function uploadToDrive(Request $request, $id)
    {
        try {
            $backup = DatabaseBackup::findOrFail($id);

            if (!$backup->fileExists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File backup tidak ditemukan.',
                ], 404);
            }

            $googleDrive = new GoogleDriveService();
            
            $result = $googleDrive->uploadFile(
                $backup->path,
                $backup->filename,
                [
                    'description' => $backup->backup_notes ?? 'SPMB Database Backup',
                ]
            );

            if ($result['success']) {
                // Update backup record with Google Drive info
                $backup->update([
                    'google_drive_file_id' => $result['file_id'],
                    'google_drive_web_link' => $result['web_view_link'],
                    'uploaded_to_drive_at' => now(),
                ]);

                // Log activity
                $this->activityLogger->log(
                    'google_drive_upload',
                    'success',
                    $backup->id,
                    0,
                    ['file_id' => $result['file_id']]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil diupload ke Google Drive!',
                    'data' => $result,
                ]);
            } else {
                $this->activityLogger->log(
                    'google_drive_upload',
                    'failed',
                    $backup->id,
                    0,
                    ['error' => $result['message']]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Upload gagal: ' . $result['message'],
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Failed to upload backup to Google Drive', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List files in Google Drive
     */
    public function listFiles()
    {
        try {
            $googleDrive = new GoogleDriveService();
            $result = $googleDrive->listFiles(50);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'files' => $result['files'],
                    'count' => $result['count'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list files: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete file from Google Drive
     */
    public function deleteFromDrive($fileId)
    {
        try {
            $googleDrive = new GoogleDriveService();
            
            if ($googleDrive->deleteFile($fileId)) {
                // Update backup record
                DatabaseBackup::where('google_drive_file_id', $fileId)->update([
                    'google_drive_file_id' => null,
                    'google_drive_web_link' => null,
                    'uploaded_to_drive_at' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'File berhasil dihapus dari Google Drive!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus file dari Google Drive.',
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
