<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR Code for a student and save to storage.
     *
     * @param string $nis Student NIS (Nomor Induk Siswa)
     * @return string Path to saved QR code file
     */
    public function generateQRCode(string $nis): string
    {
        // Generate QR Code content (just the NIS)
        $qrContent = $nis;
        
        // Generate QR Code image (SVG format, 300x300, high error correction)
        $qrImage = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrContent);
        
        // Define storage path (relative to storage/app/public)
        $path = "qrcodes/{$nis}.svg";
        
        // Save to public storage disk so it's web-accessible
        Storage::disk('public')->put($path, $qrImage);
        
        return $path;
    }

    /**
     * Regenerate QR Code for a student (delete old and create new).
     *
     * @param string $nis Student NIS
     * @return string Path to new QR code file
     */
    public function regenerateQRCode(string $nis): string
    {
        // Delete old QR Code if exists
        $oldPath = "qrcodes/{$nis}.svg";
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        
        // Generate new QR Code
        return $this->generateQRCode($nis);
    }

    /**
     * Get public URL for QR Code.
     *
     * @param string $nis Student NIS
     * @return string|null Public URL or null if not found
     */
    public function getQRCodeUrl(string $nis): ?string
    {
        $path = "qrcodes/{$nis}.svg";
        
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }
        
        return Storage::disk('public')->url($path);
    }

    /**
     * Generate QR Codes for multiple students in batch.
     *
     * @param array $students Array of student objects with 'nis' property
     * @return array Array of results with 'nis', 'path', 'success', 'error'
     */
    public function generateBatchQRCodes(array $students): array
    {
        $results = [];
        
        foreach ($students as $student) {
            try {
                $path = $this->generateQRCode($student->nis);
                
                $results[] = [
                    'nis' => $student->nis,
                    'nama' => $student->nama ?? null,
                    'path' => $path,
                    'success' => true,
                    'error' => null,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'nis' => $student->nis,
                    'nama' => $student->nama ?? null,
                    'path' => null,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }

    /**
     * Delete QR Code file for a student.
     *
     * @param string $nis Student NIS
     * @return bool Success status
     */
    public function deleteQRCode(string $nis): bool
    {
        $path = "qrcodes/{$nis}.svg";
        
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return true; // Already deleted
    }

    /**
     * Check if QR Code exists for a student.
     *
     * @param string $nis Student NIS
     * @return bool
     */
    public function qrCodeExists(string $nis): bool
    {
        $path = "qrcodes/{$nis}.svg";
        return Storage::disk('public')->exists($path);
    }
}
