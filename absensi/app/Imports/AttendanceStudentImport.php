<?php

namespace App\Imports;

use App\Models\AttendanceStudent;
use App\Models\AttendanceClass;
use App\Services\QRCodeService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class AttendanceStudentImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private int $successCount = 0;
    private int $failedCount = 0;
    private array $customErrors = [];

    public function __construct(
        private QRCodeService $qrCodeService
    ) {}

    /**
     * Transform each row into a model.
     */
    public function model(array $row)
    {
        try {
            // Check if student with this NIS already exists
            $existing = AttendanceStudent::where('nis', $row['nis'])->first();
            if ($existing) {
                $this->failedCount++;
                $this->customErrors[] = "NIS {$row['nis']} sudah ada";
                return null;
            }

            // Validate kelas_id exists
            $class = AttendanceClass::find($row['kelas_id']);
            if (!$class) {
                $this->failedCount++;
                $this->customErrors[] = "Kelas ID {$row['kelas_id']} tidak ditemukan untuk NIS {$row['nis']}";
                return null;
            }

            // Create student
            $student = AttendanceStudent::create([
                'nis' => $row['nis'],
                'nama' => $row['nama'],
                'kelas_id' => $row['kelas_id'],
                'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
                'is_active' => true,
            ]);

            // Generate QR Code
            try {
                $qrPath = $this->qrCodeService->generateQRCode($student->nis);
                $student->update(['qr_code_path' => $qrPath]);
            } catch (\Exception $e) {
                Log::error("Failed to generate QR for NIS {$student->nis}: " . $e->getMessage());
                // Student is still created, just without QR Code
            }

            $this->successCount++;
            return $student;

        } catch (\Exception $e) {
            $this->failedCount++;
            $this->customErrors[] = "Error pada NIS {$row['nis']}: " . $e->getMessage();
            Log::error("Import error for row: " . json_encode($row) . " | Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'kelas_id' => 'required|integer|exists:attendance_classes,id',
            'no_hp_ortu' => 'nullable|string|max:20',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages()
    {
        return [
            'nis.required' => 'NIS wajib diisi',
            'nis.unique' => 'NIS sudah terdaftar',
            'nama.required' => 'Nama wajib diisi',
            'kelas_id.required' => 'Kelas ID wajib diisi',
            'kelas_id.exists' => 'Kelas ID tidak valid',
        ];
    }

    /**
     * Get import results.
     */
    public function getResults(): array
    {
        return [
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'errors' => $this->customErrors,
        ];
    }

    /**
     * Handle errors during import.
     */
    public function onError(\Throwable $e)
    {
        $this->failedCount++;
        $this->customErrors[] = $e->getMessage();
        Log::error("Import error: " . $e->getMessage());
    }
}
