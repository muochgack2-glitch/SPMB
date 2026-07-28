<?php

namespace App\Services;

use App\Models\AttendanceStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Log;

class AttendanceNotificationService
{
    public function __construct(
        private AttendanceWhatsAppService $whatsAppService
    ) {}

    /**
     * Send check-in notification to parent.
     *
     * @param AttendanceStudent $student
     * @param AttendanceRecord $record
     * @return void
     */
    public function notifyCheckIn(AttendanceStudent $student, AttendanceRecord $record): void
    {
        // Check if parent notification is enabled
        $enabled = AttendanceSetting::get('enable_parent_notification', 'true');
        if ($enabled !== 'true') {
            return;
        }

        // Check if student has parent phone number
        if (empty($student->no_hp_ortu)) {
            Log::warning("No parent phone number for student {$student->nis}");
            return;
        }

        // Get school name
        $schoolName = AttendanceSetting::get('school_name', 'Sekolah');

        // Format message
        $message = $this->formatCheckInMessage($student, $record, $schoolName);

        // Check if photo should be included
        $includePhoto = AttendanceSetting::get('include_photo_in_notification', 'false') === 'true';
        $photoPath = $includePhoto ? $record->check_in_photo : null;

        // Send notification
        $result = $this->whatsAppService->sendParentNotification(
            $student->no_hp_ortu,
            $message,
            $photoPath
        );

        // Log notification attempt
        $this->logNotification($student->id, 'check_in', $result);
    }

    /**
     * Send check-out notification to parent.
     *
     * @param AttendanceStudent $student
     * @param AttendanceRecord $record
     * @return void
     */
    public function notifyCheckOut(AttendanceStudent $student, AttendanceRecord $record): void
    {
        // Check if parent notification is enabled
        $enabled = AttendanceSetting::get('enable_parent_notification', 'true');
        if ($enabled !== 'true') {
            return;
        }

        // Check if student has parent phone number
        if (empty($student->no_hp_ortu)) {
            return;
        }

        // Get school name
        $schoolName = AttendanceSetting::get('school_name', 'Sekolah');

        // Format message
        $message = $this->formatCheckOutMessage($student, $record, $schoolName);

        // Check if photo should be included
        $includePhoto = AttendanceSetting::get('include_photo_in_notification', 'false') === 'true';
        $photoPath = $includePhoto ? $record->check_out_photo : null;

        // Send notification
        $result = $this->whatsAppService->sendParentNotification(
            $student->no_hp_ortu,
            $message,
            $photoPath
        );

        // Log notification attempt
        $this->logNotification($student->id, 'check_out', $result);
    }

    /**
     * Format check-in message.
     */
    private function formatCheckInMessage(AttendanceStudent $student, AttendanceRecord $record, string $schoolName): string
    {
        $statusLabel = match ($record->status) {
            'hadir' => '✅ Hadir',
            'terlambat' => '⚠️ Terlambat',
            'alpha' => '❌ Alpha',
            'izin' => '📝 Izin',
            default => ucfirst($record->status),
        };

        $time = \Carbon\Carbon::parse($record->check_in_time)->format('H:i');

        $message = "🏫 *{$schoolName}*\n";
        $message .= "📍 Notifikasi Absensi\n\n";
        $message .= "Siswa: *{$student->nama}*\n";
        $message .= "Kelas: {$student->kelas->nama_kelas}\n";
        $message .= "Waktu Masuk: *{$time}*\n";
        $message .= "Status: {$statusLabel}\n";
        $message .= "\n_Pesan otomatis dari sistem absensi_";

        return $message;
    }

    /**
     * Format check-out message.
     */
    private function formatCheckOutMessage(AttendanceStudent $student, AttendanceRecord $record, string $schoolName): string
    {
        $time = \Carbon\Carbon::parse($record->check_out_time)->format('H:i');

        $message = "🏫 *{$schoolName}*\n";
        $message .= "📍 Notifikasi Pulang\n\n";
        $message .= "Siswa: *{$student->nama}*\n";
        $message .= "Kelas: {$student->kelas->nama_kelas}\n";
        $message .= "Waktu Pulang: *{$time}*\n";
        $message .= "\n_Pesan otomatis dari sistem absensi_";

        return $message;
    }

    /**
     * Log notification attempt.
     */
    private function logNotification(int $studentId, string $action, array $result): void
    {
        try {
            AttendanceLog::create([
                'student_id' => $studentId,
                'action' => 'notification',
                'message' => "WhatsApp notification for {$action}",
                'response' => json_encode($result),
                'status' => $result['success'] ? 'success' : 'failed',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log notification: ' . $e->getMessage());
        }
    }

    /**
     * Send absent notification to parent.
     *
     * @param AttendanceStudent $student
     * @param AttendanceRecord $record
     * @return void
     */
    public function notifyAbsent(AttendanceStudent $student, AttendanceRecord $record): void
    {
        // Check if parent notification is enabled
        $enabled = AttendanceSetting::get('enable_parent_notification', 'true');
        if ($enabled !== 'true') {
            return;
        }

        // Check if student has parent phone number
        if (empty($student->no_hp_ortu)) {
            return;
        }

        // Get school name
        $schoolName = AttendanceSetting::get('school_name', 'Sekolah');

        // Format message
        $message = "🏫 *{$schoolName}*\n";
        $message .= "⚠️ *Notifikasi Ketidakhadiran*\n\n";
        $message .= "Siswa: *{$student->nama}*\n";
        $message .= "Kelas: {$student->kelas->nama_kelas}\n";
        $message .= "Tanggal: " . $record->date->format('d/m/Y') . "\n";
        $message .= "Status: ❌ *Alpha (Tidak Hadir)*\n\n";
        $message .= "Mohon segera menghubungi pihak sekolah.\n";
        $message .= "\n_Pesan otomatis dari sistem absensi_";

        // Send notification
        $result = $this->whatsAppService->sendParentNotification(
            $student->no_hp_ortu,
            $message
        );

        // Log notification attempt
        $this->logNotification($student->id, 'absent', $result);
    }
}
