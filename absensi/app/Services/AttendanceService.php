<?php

namespace App\Services;

use App\Models\AttendanceStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private PhotoCaptureService $photoCaptureService,
        private AttendanceStatusService $statusService
    ) {}

    /**
     * Process QR scan and record attendance.
     *
     * @param string $nis Student NIS
     * @param string $photoBase64 Base64 encoded photo
     * @param string $action 'check_in' or 'check_out'
     * @return array Response with success status and data
     */
    public function processScan(string $nis, ?string $photoBase64, string $action): array
    {
        $startTime = microtime(true);
        DB::beginTransaction();
        
        try {
            // 1. Find student with eager loading to prevent N+1 queries
            $t1 = microtime(true);
            $student = AttendanceStudent::with('kelas')
                ->where('nis', $nis)
                ->where('is_active', true)
                ->first();
            \Log::info('Query student took: ' . round((microtime(true) - $t1) * 1000, 2) . 'ms');
            
            if (!$student) {
                $this->logAction(null, 'qr_scan', "Student not found: {$nis}", null, 'failed');
                
                return [
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan atau tidak aktif',
                    'data' => null,
                ];
            }

            // 2. Validate photo data (skip validation if photo is nullable/empty for performance)
            if ($photoBase64 && !empty(trim($photoBase64))) {
                if (!$this->photoCaptureService->validatePhotoData($photoBase64)) {
                    $this->logAction($student->id, 'qr_scan', 'Invalid photo data', null, 'failed');
                    
                    return [
                        'success' => false,
                        'message' => 'Data foto tidak valid',
                        'data' => null,
                    ];
                }
            }

            // 3. Get or create today's attendance record
            $today = Carbon::today();
            $record = AttendanceRecord::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => $today,
                ],
                [
                    'status' => 'alpha', // Default status
                ]
            );

            // 4. Process based on action
            if ($action === 'check_in') {
                return $this->processCheckIn($student, $record, $photoBase64);
            } elseif ($action === 'check_out') {
                return $this->processCheckOut($student, $record, $photoBase64);
            } else {
                throw new \Exception("Invalid action: {$action}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->logAction(
                $student->id ?? null,
                'error',
                'Exception during scan processing: ' . $e->getMessage(),
                null,
                'failed'
            );
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Process check-in action.
     */
    private function processCheckIn(AttendanceStudent $student, AttendanceRecord $record, ?string $photoBase64): array
    {
        // Check if already checked in
        if ($record->check_in_time !== null) {
            DB::rollBack();
            
            $this->logAction(
                $student->id,
                'check_in',
                'Already checked in today',
                null,
                'failed'
            );
            
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini',
                'data' => [
                    'nama' => $student->nama,
                    'nis' => $student->nis,
                    'kelas' => $student->kelas->nama_kelas ?? '-',
                    'status' => $record->status,
                    'time' => Carbon::parse($record->check_in_time)->format('H:i'),
                    'duplicate' => true,
                ],
            ];
        }

        // Validate time window
        $currentTime = Carbon::now()->format('H:i:s');
        if (!$this->statusService->isWithinCheckInWindow($currentTime)) {
            DB::rollBack();
            
            $this->logAction(
                $student->id,
                'check_in',
                'Check-in time past cutoff',
                null,
                'failed'
            );
            
            return [
                'success' => false,
                'message' => 'Waktu check-in sudah lewat',
                'data' => null,
            ];
        }

        // Save photo (skip if empty for performance)
        $photoPath = null;
        if ($photoBase64 && !empty(trim($photoBase64))) {
            $photoPath = $this->photoCaptureService->savePhoto($photoBase64, $student->nis, 'check_in');
        }

        // Determine status
        $status = $this->statusService->determineStatus($currentTime);

        // Update record
        $record->update([
            'check_in_time' => $currentTime,
            'check_in_photo' => $photoPath,
            'status' => $status,
        ]);

        DB::commit();

        // Log success
        $this->logAction(
            $student->id,
            'check_in',
            "Check-in successful at {$currentTime}, status: {$status}",
            json_encode(['photo_path' => $photoPath]),
            'success'
        );

        return [
            'success' => true,
            'message' => "Check-in berhasil! Status: {$this->statusService->getStatusLabel($status)}",
            'data' => [
                'nama' => $student->nama,
                'nis' => $student->nis,
                'kelas' => $student->kelas->nama_kelas ?? '-',
                'status' => $status,
                'status_label' => $this->statusService->getStatusLabel($status),
                'time' => Carbon::parse($currentTime)->format('H:i'),
            ],
        ];
    }

    /**
     * Process check-out action.
     */
    private function processCheckOut(AttendanceStudent $student, AttendanceRecord $record, ?string $photoBase64): array
    {
        // Check if checked in first
        if ($record->check_in_time === null) {
            DB::rollBack();
            
            $this->logAction(
                $student->id,
                'check_out',
                'Cannot check out without check-in',
                null,
                'failed'
            );
            
            return [
                'success' => false,
                'message' => 'Anda belum melakukan check-in',
                'data' => null,
            ];
        }

        // Check if already checked out
        if ($record->check_out_time !== null) {
            DB::rollBack();
            
            $this->logAction(
                $student->id,
                'check_out',
                'Already checked out today',
                null,
                'failed'
            );
            
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan check-out hari ini',
                'data' => [
                    'nama' => $student->nama,
                    'nis' => $student->nis,
                    'kelas' => $student->kelas->nama_kelas ?? '-',
                    'status' => $record->status,
                    'time' => Carbon::parse($record->check_out_time)->format('H:i'),
                    'duplicate' => true,
                ],
            ];
        }

        // Save photo (skip if empty for performance)
        $currentTime = Carbon::now()->format('H:i:s');
        $photoPath = null;
        if ($photoBase64 && !empty(trim($photoBase64))) {
            $photoPath = $this->photoCaptureService->savePhoto($photoBase64, $student->nis, 'check_out');
        }

        // Update record
        $record->update([
            'check_out_time' => $currentTime,
            'check_out_photo' => $photoPath,
        ]);

        DB::commit();

        // Log success
        $this->logAction(
            $student->id,
            'check_out',
            "Check-out successful at {$currentTime}",
            json_encode(['photo_path' => $photoPath]),
            'success'
        );

        return [
            'success' => true,
            'message' => 'Check-out berhasil!',
            'data' => [
                'nama' => $student->nama,
                'nis' => $student->nis,
                'kelas' => $student->kelas->nama_kelas ?? '-',
                'status' => $record->status,
                'time' => Carbon::parse($currentTime)->format('H:i'),
            ],
        ];
    }

    /**
     * Mark students as absent if they haven't checked in by cutoff time.
     *
     * @return array Result with statistics and marked students
     */
    public function markAbsentStudents(): array
    {
        $today = Carbon::today();
        $cutoffTime = AttendanceSetting::get('cutoff_time', '09:00');
        
        // Get all active students
        $students = AttendanceStudent::with('kelas')->where('is_active', true)->get();
        
        $markedCount = 0;
        $alreadyRecorded = 0;
        $inactiveSkipped = AttendanceStudent::where('is_active', false)->count();
        $markedStudents = [];
        
        foreach ($students as $student) {
            // Check if student has attendance record for today
            $record = AttendanceRecord::where('student_id', $student->id)
                ->where('date', $today)
                ->first();
            
            // If no record or no check-in, mark as alpha
            if (!$record) {
                AttendanceRecord::create([
                    'student_id' => $student->id,
                    'date' => $today,
                    'status' => 'alpha',
                    'notes' => "Auto-marked absent at {$cutoffTime}",
                ]);
                
                $this->logAction(
                    $student->id,
                    'auto_alpha',
                    "Auto-marked absent (no record)",
                    null,
                    'success'
                );
                
                $markedCount++;
                $markedStudents[] = [
                    'nis' => $student->nis,
                    'nama' => $student->nama,
                    'kelas' => $student->kelas->nama_kelas ?? '-',
                ];
            } elseif ($record->check_in_time === null && $record->status !== 'alpha') {
                $record->update([
                    'status' => 'alpha',
                    'notes' => "Auto-marked absent at {$cutoffTime}",
                ]);
                
                $this->logAction(
                    $student->id,
                    'auto_alpha',
                    "Auto-marked absent (no check-in)",
                    null,
                    'success'
                );
                
                $markedCount++;
                $markedStudents[] = [
                    'nis' => $student->nis,
                    'nama' => $student->nama,
                    'kelas' => $student->kelas->nama_kelas ?? '-',
                ];
            } else {
                // Student already has check-in or manually marked
                $alreadyRecorded++;
            }
        }
        
        return [
            'success' => true,
            'total_students' => $students->count(),
            'marked_absent' => $markedCount,
            'already_recorded' => $alreadyRecorded,
            'inactive_skipped' => $inactiveSkipped,
            'marked_students' => $markedStudents,
        ];
    }


    /**
     * Get today's attendance records with optional class filter.
     *
     * @param int|null $classId Optional class ID filter
     * @return Collection
     */
    public function getTodayAttendance(?int $classId = null): Collection
    {
        $query = AttendanceRecord::with(['student.kelas'])
            ->whereDate('date', Carbon::today())
            ->orderBy('check_in_time', 'asc');
        
        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('kelas_id', $classId);
            });
        }
        
        return $query->get();
    }

    /**
     * Get attendance statistics for a specific date.
     *
     * @param string|null $date Date in Y-m-d format (defaults to today)
     * @param int|null $classId Optional class ID filter
     * @return array Statistics
     */
    public function getAttendanceStats(?string $date = null, ?int $classId = null): array
    {
        $date = $date ?? Carbon::today()->format('Y-m-d');
        
        $query = AttendanceRecord::whereDate('date', $date);
        
        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('kelas_id', $classId);
            });
        }
        
        $records = $query->get();
        
        // Count by status
        $hadir = $records->where('status', 'hadir')->count();
        $terlambat = $records->where('status', 'terlambat')->count();
        $alpha = $records->where('status', 'alpha')->count();
        $izin = $records->where('status', 'izin')->count();
        
        // Total students
        $totalStudentsQuery = AttendanceStudent::where('is_active', true);
        if ($classId) {
            $totalStudentsQuery->where('kelas_id', $classId);
        }
        $totalStudents = $totalStudentsQuery->count();
        
        // Not yet recorded
        $belum = $totalStudents - $records->count();
        
        return [
            'date' => $date,
            'class_id' => $classId,
            'total_students' => $totalStudents,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'alpha' => $alpha,
            'izin' => $izin,
            'belum' => $belum,
            'percentage_hadir' => $totalStudents > 0 ? round(($hadir / $totalStudents) * 100, 1) : 0,
            'percentage_terlambat' => $totalStudents > 0 ? round(($terlambat / $totalStudents) * 100, 1) : 0,
        ];
    }

    /**
     * Log attendance action.
     *
     * @param int|null $studentId
     * @param string $action
     * @param string $message
     * @param string|null $response
     * @param string $status 'pending', 'success', or 'failed'
     */
    private function logAction(?int $studentId, string $action, string $message, ?string $response, string $status): void
    {
        try {
            AttendanceLog::create([
                'student_id' => $studentId,
                'action' => $action,
                'message' => $message,
                'response' => $response,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking main flow
            \Log::error('Failed to create attendance log: ' . $e->getMessage());
        }
    }

    /**
     * Reject a scan (manual rejection by petugas).
     *
     * @param string $nis Student NIS
     * @param string $reason Rejection reason
     * @return array Response
     */
    public function rejectScan(string $nis, string $reason): array
    {
        $student = AttendanceStudent::where('nis', $nis)->first();
        
        if (!$student) {
            return [
                'success' => false,
                'message' => 'Siswa tidak ditemukan',
            ];
        }
        
        $this->logAction(
            $student->id,
            'reject',
            "Scan rejected by petugas: {$reason}",
            null,
            'success'
        );
        
        return [
            'success' => true,
            'message' => 'Scan berhasil ditolak',
            'data' => [
                'student' => $student,
                'reason' => $reason,
            ],
        ];
    }
}
