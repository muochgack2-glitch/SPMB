<?php

namespace App\Services;

use App\Models\Pendaftar;
use App\Models\ExternalBroadcastBatch;
use App\Models\ExternalBroadcastRecipient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalBroadcastService
{
    /**
     * Normalize phone number to 62xxx format
     * 
     * @param string $phone
     * @return string|null
     */
    public function normalizePhone(string $phone): ?string
    {
        // Remove non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert to 62xxx format
        if (substr($cleaned, 0, 1) === '0') {
            // 0812xxx -> 62812xxx
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (substr($cleaned, 0, 1) === '8') {
            // 812xxx -> 62812xxx
            $cleaned = '62' . $cleaned;
        } elseif (!str_starts_with($cleaned, '62')) {
            // Invalid format
            return null;
        }
        
        // Validate length (10-15 digits)
        $length = strlen($cleaned);
        if ($length < 10 || $length > 15) {
            return null;
        }
        
        return $cleaned;
    }

    /**
     * Parse CSV file and return recipients array
     * 
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function parseCSV(UploadedFile $file): array
    {
        $errors = [];
        $recipients = [];
        
        $handle = fopen($file->path(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file');
        }
        
        // Read header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new \Exception('CSV file is empty');
        }
        
        // Validate header - must have 'name' and 'phone'
        $header = array_map('strtolower', $header);
        if (!in_array('name', $header) || !in_array('phone', $header)) {
            fclose($handle);
            throw new \Exception('CSV must have "name" and "phone" columns');
        }
        
        $row = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            
            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }
            
            $record = array_combine($header, $data);
            
            // Validate required fields
            if (empty(trim($record['name']))) {
                $errors[] = "Row {$row}: Name is required";
                continue;
            }
            
            if (empty(trim($record['phone']))) {
                $errors[] = "Row {$row}: Phone is required";
                continue;
            }
            
            // Normalize phone
            $normalized = $this->normalizePhone($record['phone']);
            if (!$normalized) {
                $errors[] = "Row {$row}: Invalid phone number format";
                continue;
            }
            
            $recipients[] = [
                'name' => trim($record['name']),
                'phone' => trim($record['phone']),
                'phone_normalized' => $normalized,
                'notes' => isset($record['notes']) ? trim($record['notes']) : null
            ];
        }
        
        fclose($handle);
        
        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }
        
        if (empty($recipients)) {
            throw new \Exception('No valid recipients found in CSV');
        }
        
        return $recipients;
    }

    /**
     * Parse manual input and return recipients array
     * 
     * @param string $input
     * @return array
     * @throws \Exception
     */
    public function parseManualInput(string $input): array
    {
        $errors = [];
        $recipients = [];
        
        $lines = explode("\n", $input);
        $lineNumber = 0;
        
        foreach ($lines as $line) {
            $lineNumber++;
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            
            // Check if line has pipe separator (phone|name|notes)
            if (str_contains($line, '|')) {
                $parts = explode('|', $line);
                $phone = trim($parts[0] ?? '');
                $name = trim($parts[1] ?? '');
                $notes = trim($parts[2] ?? '');
                
                if (empty($phone)) {
                    $errors[] = "Line {$lineNumber}: Phone is required";
                    continue;
                }
                
                if (empty($name)) {
                    $name = 'External Contact';
                }
            } else {
                // Only phone number provided
                $phone = $line;
                $name = 'External Contact';
                $notes = null;
            }
            
            // Normalize phone
            $normalized = $this->normalizePhone($phone);
            if (!$normalized) {
                $errors[] = "Line {$lineNumber}: Invalid phone number format";
                continue;
            }
            
            $recipients[] = [
                'name' => $name,
                'phone' => $phone,
                'phone_normalized' => $normalized,
                'notes' => $notes
            ];
            
            // Limit to 500 entries
            if (count($recipients) >= 500) {
                break;
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }
        
        if (empty($recipients)) {
            throw new \Exception('No valid phone numbers found in input');
        }
        
        return $recipients;
    }

    /**
     * Detect duplicates with SPMB database
     * 
     * @param array $recipients
     * @return array
     */
    public function detectDuplicates(array $recipients): array
    {
        // Extract normalized phones
        $normalizedPhones = array_column($recipients, 'phone_normalized');
        
        // Get ALL pendaftars with phone numbers (include status_siswa)
        $duplicates = DB::table('pendaftar')
            ->where(function($query) {
                $query->whereNotNull('no_hp_wali')
                      ->orWhereNotNull('no_hp_ortu')
                      ->orWhereNotNull('no_telepon');
            })
            ->select('id_pendaftar', 'no_hp_wali', 'no_hp_ortu', 'no_telepon', 'nama_lengkap', 'status_siswa')
            ->get();
        
        // Create lookup map with normalized phones and status
        $duplicateMap = [];
        foreach ($duplicates as $dup) {
            if (!empty($dup->no_hp_wali) && trim($dup->no_hp_wali) !== '' && $dup->no_hp_wali !== '-') {
                $normalized = $this->normalizePhone($dup->no_hp_wali);
                if ($normalized) {
                    $duplicateMap[$normalized] = [
                        'id_pendaftar' => $dup->id_pendaftar,
                        'status_siswa' => $dup->status_siswa,
                    ];
                }
            }
            if (!empty($dup->no_hp_ortu) && trim($dup->no_hp_ortu) !== '' && $dup->no_hp_ortu !== '-') {
                $normalized = $this->normalizePhone($dup->no_hp_ortu);
                if ($normalized) {
                    $duplicateMap[$normalized] = [
                        'id_pendaftar' => $dup->id_pendaftar,
                        'status_siswa' => $dup->status_siswa,
                    ];
                }
            }
            if (!empty($dup->no_telepon) && trim($dup->no_telepon) !== '' && $dup->no_telepon !== '-') {
                $normalized = $this->normalizePhone($dup->no_telepon);
                if ($normalized) {
                    $duplicateMap[$normalized] = [
                        'id_pendaftar' => $dup->id_pendaftar,
                        'status_siswa' => $dup->status_siswa,
                    ];
                }
            }
        }
        
        // Statuses that should be skipped from external broadcast
        $skipStatuses = ['Diterima', 'Sudah Daftar Ulang'];
        
        // Flag duplicates in recipients array
        foreach ($recipients as &$recipient) {
            $normalized = $recipient['phone_normalized'];
            if (isset($duplicateMap[$normalized])) {
                $recipient['is_duplicate_spmb'] = true;
                $recipient['matched_pendaftar_id'] = $duplicateMap[$normalized]['id_pendaftar'];
                $recipient['matched_status'] = $duplicateMap[$normalized]['status_siswa'];
                
                // NEW: Flag if should be skipped (student already enrolled)
                $recipient['will_be_skipped'] = in_array($duplicateMap[$normalized]['status_siswa'], $skipStatuses);
            } else {
                $recipient['is_duplicate_spmb'] = false;
                $recipient['matched_pendaftar_id'] = null;
                $recipient['matched_status'] = null;
                $recipient['will_be_skipped'] = false;
            }
        }
        
        return $recipients;
    }

    /**
     * Create broadcast batch
     * 
     * @param string $name
     * @param string $sourceType
     * @param int $userId
     * @param string|null $description
     * @param string|null $sourceFile
     * @return ExternalBroadcastBatch
     */
    public function createBatch(
        string $name, 
        string $sourceType, 
        int $userId,
        ?string $description = null,
        ?string $sourceFile = null
    ): ExternalBroadcastBatch
    {
        return ExternalBroadcastBatch::create([
            'batch_name' => $name,
            'description' => $description,
            'source_type' => $sourceType,
            'source_file' => $sourceFile,
            'created_by' => $userId,
            'status' => 'pending',
            'total_recipients' => 0,
            'total_sent' => 0,
            'total_failed' => 0,
        ]);
    }

    /**
     * Save recipients to database
     * 
     * @param int $batchId
     * @param array $recipients
     * @return void
     */
    public function saveRecipients(int $batchId, array $recipients): void
    {
        DB::transaction(function() use ($batchId, $recipients) {
            // Deduplicate within batch
            $uniquePhones = [];
            $deduplicatedRecipients = [];
            
            foreach ($recipients as $recipient) {
                $phone = $recipient['phone_normalized'];
                if (!isset($uniquePhones[$phone])) {
                    $uniquePhones[$phone] = true;
                    $deduplicatedRecipients[] = [
                        'batch_id' => $batchId,
                        'name' => $recipient['name'],
                        'phone' => $recipient['phone'],
                        'phone_normalized' => $recipient['phone_normalized'],
                        'notes' => $recipient['notes'] ?? null,
                        'is_duplicate_spmb' => $recipient['is_duplicate_spmb'] ?? false,
                        'matched_pendaftar_id' => $recipient['matched_pendaftar_id'] ?? null,
                        'matched_status' => $recipient['matched_status'] ?? null,
                        'will_be_skipped' => $recipient['will_be_skipped'] ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Bulk insert
            if (!empty($deduplicatedRecipients)) {
                ExternalBroadcastRecipient::insert($deduplicatedRecipients);
                
                // Update batch total_recipients
                ExternalBroadcastBatch::where('id', $batchId)
                    ->update(['total_recipients' => count($deduplicatedRecipients)]);
            }
        });
    }
}
