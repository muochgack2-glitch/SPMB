<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoCaptureService
{
    /**
     * Maximum photo file size in bytes (500KB).
     */
    private const MAX_FILE_SIZE = 500 * 1024;

    /**
     * JPEG compression quality (0-100).
     */
    private const JPEG_QUALITY = 85;

    /**
     * Save photo from base64 string with compression.
     *
     * @param string $base64 Base64 encoded photo data
     * @param string $nis Student NIS
     * @param string $type Photo type: 'check_in' or 'check_out'
     * @return string Path to saved photo file
     * @throws \Exception If photo processing fails
     */
    public function savePhoto(string $base64, string $nis, string $type): string
    {
        // Remove data:image/jpeg;base64, prefix if present
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64)[1];
        }

        // Decode base64
        $imageData = base64_decode($base64);
        
        if ($imageData === false) {
            throw new \Exception('Failed to decode base64 photo data');
        }

        // Create image resource from decoded data
        $image = imagecreatefromstring($imageData);
        
        if ($image === false) {
            throw new \Exception('Failed to create image from data');
        }

        // Compress image
        $compressedImage = $this->compressImage($image);

        // Generate filename
        $date = date('Y-m-d');
        $timestamp = date('His');
        $filename = "{$type}_{$timestamp}.jpg";
        
        // Define storage path: attendance/photos/{NIS}/{date}/{filename}
        $path = "attendance/photos/{$nis}/{$date}/{$filename}";

        // Save to storage (public disk so files are accessible via web)
        Storage::disk('public')->put($path, $compressedImage);

        // Free memory
        imagedestroy($image);

        return $path;
    }

    /**
     * Compress image to meet size requirements.
     *
     * @param resource|\GdImage $image GD image resource
     * @return string Compressed JPEG image data
     */
    private function compressImage($image): string
    {
        // Start output buffering
        ob_start();
        
        // Output as JPEG with compression quality
        imagejpeg($image, null, self::JPEG_QUALITY);
        
        // Get compressed image data
        $compressedData = ob_get_clean();

        // If still too large, reduce quality further
        $quality = self::JPEG_QUALITY;
        while (strlen($compressedData) > self::MAX_FILE_SIZE && $quality > 50) {
            $quality -= 10;
            
            ob_start();
            imagejpeg($image, null, $quality);
            $compressedData = ob_get_clean();
        }

        return $compressedData;
    }

    /**
     * Get public URL for photo.
     *
     * @param string $path Storage path to photo
     * @return string|null Public URL or null if not found
     */
    public function getPhotoUrl(string $path): ?string
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }
        
        return Storage::disk('public')->url($path);
    }

    /**
     * Delete photo file.
     *
     * @param string $path Storage path to photo
     * @return bool Success status
     */
    public function deletePhoto(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return true; // Already deleted
    }

    /**
     * Delete all photos for a student on a specific date.
     *
     * @param string $nis Student NIS
     * @param string $date Date in Y-m-d format
     * @return bool Success status
     */
    public function deletePhotosForDate(string $nis, string $date): bool
    {
        $directory = "attendance/photos/{$nis}/{$date}";
        
        if (Storage::disk('public')->exists($directory)) {
            return Storage::disk('public')->deleteDirectory($directory);
        }
        
        return true;
    }

    /**
     * Get total storage size for student's photos in bytes.
     *
     * @param string $nis Student NIS
     * @return int Total size in bytes
     */
    public function getStudentPhotoSize(string $nis): int
    {
        $directory = "attendance/photos/{$nis}";
        
        if (!Storage::disk('public')->exists($directory)) {
            return 0;
        }

        $files = Storage::disk('public')->allFiles($directory);
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += Storage::disk('public')->size($file);
        }

        return $totalSize;
    }

    /**
     * Validate base64 photo data.
     *
     * @param string $base64 Base64 encoded photo data
     * @return bool True if valid
     */
    public function validatePhotoData(string $base64): bool
    {
        // Remove data URL prefix if present
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64)[1];
        }

        // Decode
        $imageData = base64_decode($base64, true);
        
        if ($imageData === false) {
            return false;
        }

        // Try to create image
        $image = @imagecreatefromstring($imageData);
        
        if ($image === false) {
            return false;
        }

        imagedestroy($image);
        return true;
    }
}
