<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class GoogleDriveService
{
    protected $client;
    protected $drive;
    protected $folderId;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize Google Client with Service Account
     */
    private function initializeClient()
    {
        try {
            // Get settings from database
            $serviceAccountPath = \App\Models\SettingSystem::get('google_drive_service_account_json', 'google/google-drive-credentials.json');
            $credentialsPath = storage_path('app/' . $serviceAccountPath);
            
            if (!file_exists($credentialsPath)) {
                throw new Exception("Google Drive credentials file not found: {$credentialsPath}");
            }

            $this->client = new GoogleClient();
            $this->client->setAuthConfig($credentialsPath);
            $this->client->addScope(GoogleDrive::DRIVE); // Changed from DRIVE_FILE to DRIVE
            $this->client->setApplicationName('SPMB Backup System');
            
            // Disable SSL verification for development (Windows fix)
            // For production, download cacert.pem from https://curl.se/ca/cacert.pem
            $httpClient = new \GuzzleHttp\Client(['verify' => false]);
            $this->client->setHttpClient($httpClient);

            $this->drive = new GoogleDrive($this->client);
            
            // Get folder ID from database
            $this->folderId = \App\Models\SettingSystem::get('google_drive_folder_id', config('services.google_drive.folder_id'));

            Log::info('Google Drive client initialized successfully', [
                'folder_id' => $this->folderId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to initialize Google Drive client', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Test connection to Google Drive
     */
    public function testConnection(): array
    {
        try {
            // Check if folder ID is configured
            if (empty($this->folderId)) {
                return [
                    'success' => false,
                    'message' => 'Folder ID not configured',
                ];
            }

            // Try to get folder info
            $folder = $this->drive->files->get($this->folderId, [
                'fields' => 'id, name, mimeType, capabilities'
            ]);

            // Check if it's actually a folder
            if ($folder->getMimeType() !== 'application/vnd.google-apps.folder') {
                return [
                    'success' => false,
                    'message' => 'The specified ID is not a folder',
                ];
            }

            return [
                'success' => true,
                'message' => 'Connection successful',
                'folder_name' => $folder->getName(),
                'folder_id' => $folder->getId(),
            ];
        } catch (\Google\Service\Exception $e) {
            $errors = $e->getErrors();
            $message = $errors[0]['message'] ?? $e->getMessage();
            
            // Better error messages
            if (strpos($message, 'File not found') !== false) {
                return [
                    'success' => false,
                    'message' => 'Folder not found. Please check the Folder ID or make sure the folder is shared with the service account.',
                ];
            } elseif (strpos($message, 'insufficient permission') !== false || strpos($message, 'Permission denied') !== false) {
                return [
                    'success' => false,
                    'message' => 'Permission denied. Please share the folder with the service account email and grant Editor permission.',
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $message,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Upload file to Google Drive
     */
    public function uploadFile(string $localPath, string $fileName, array $metadata = []): array
    {
        try {
            if (!file_exists($localPath)) {
                throw new Exception("File not found: {$localPath}");
            }

            Log::info('Starting upload to Google Drive', [
                'file' => $fileName,
                'size' => filesize($localPath),
            ]);

            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$this->folderId],
                'description' => $metadata['description'] ?? 'SPMB Database Backup',
            ]);

            $content = file_get_contents($localPath);
            $mimeType = 'application/gzip';

            $file = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, size, createdTime, webViewLink'
            ]);

            Log::info('File uploaded to Google Drive successfully', [
                'file_id' => $file->getId(),
                'file_name' => $file->getName(),
                'size' => $file->getSize(),
            ]);

            return [
                'success' => true,
                'file_id' => $file->getId(),
                'file_name' => $file->getName(),
                'size' => $file->getSize(),
                'created_time' => $file->getCreatedTime(),
                'web_view_link' => $file->getWebViewLink(),
            ];

        } catch (Exception $e) {
            Log::error('Failed to upload file to Google Drive', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download file from Google Drive
     */
    public function downloadFile(string $fileId, string $destinationPath): bool
    {
        try {
            $response = $this->drive->files->get($fileId, [
                'alt' => 'media'
            ]);

            $content = $response->getBody()->getContents();
            file_put_contents($destinationPath, $content);

            Log::info('File downloaded from Google Drive', [
                'file_id' => $fileId,
                'destination' => $destinationPath,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to download file from Google Drive', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete file from Google Drive
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $this->drive->files->delete($fileId);

            Log::info('File deleted from Google Drive', [
                'file_id' => $fileId,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete file from Google Drive', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * List files in Google Drive folder
     */
    public function listFiles(int $maxResults = 100): array
    {
        try {
            $query = "'{$this->folderId}' in parents and trashed = false";
            
            $results = $this->drive->files->listFiles([
                'q' => $query,
                'pageSize' => $maxResults,
                'fields' => 'files(id, name, size, createdTime, modifiedTime, webViewLink)',
                'orderBy' => 'createdTime desc'
            ]);

            $files = [];
            foreach ($results->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'size' => $file->getSize(),
                    'created_time' => $file->getCreatedTime(),
                    'modified_time' => $file->getModifiedTime(),
                    'web_view_link' => $file->getWebViewLink(),
                ];
            }

            return [
                'success' => true,
                'files' => $files,
                'count' => count($files),
            ];
        } catch (Exception $e) {
            Log::error('Failed to list files from Google Drive', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'files' => [],
                'count' => 0,
            ];
        }
    }

    /**
     * Get storage quota information
     */
    public function getStorageInfo(): array
    {
        try {
            $about = $this->drive->about->get([
                'fields' => 'storageQuota, user'
            ]);

            $quota = $about->getStorageQuota();

            return [
                'success' => true,
                'limit' => $quota->getLimit(),
                'usage' => $quota->getUsage(),
                'usage_in_drive' => $quota->getUsageInDrive(),
                'usage_in_trash' => $quota->getUsageInDriveTrash(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search files by name
     */
    public function searchFiles(string $searchTerm): array
    {
        try {
            $query = "'{$this->folderId}' in parents and trashed = false and name contains '{$searchTerm}'";
            
            $results = $this->drive->files->listFiles([
                'q' => $query,
                'pageSize' => 50,
                'fields' => 'files(id, name, size, createdTime, webViewLink)',
                'orderBy' => 'createdTime desc'
            ]);

            $files = [];
            foreach ($results->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'size' => $file->getSize(),
                    'created_time' => $file->getCreatedTime(),
                    'web_view_link' => $file->getWebViewLink(),
                ];
            }

            return [
                'success' => true,
                'files' => $files,
                'count' => count($files),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'files' => [],
            ];
        }
    }
}
