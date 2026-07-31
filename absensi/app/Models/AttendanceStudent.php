<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Fillable(['nis', 'nama', 'kelas_id', 'no_hp_ortu', 'qr_code_path', 'foto_profil', 'is_active'])]
class AttendanceStudent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_students';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the class that the student belongs to.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(AttendanceClass::class, 'kelas_id');
    }

    /**
     * Get the attendance records for the student.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    /**
     * Get the logs for the student.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'student_id');
    }

    /**
     * Get the QR code URL attribute.
     *
     * @return string|null
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_code_path);
    }

    /**
     * Get today's attendance record for this student.
     *
     * @return AttendanceRecord|null
     */
    public function getTodayAttendance(): ?AttendanceRecord
    {
        return $this->attendanceRecords()
            ->whereDate('date', Carbon::today())
            ->first();
    }

    /**
     * Check if the student has checked in today.
     *
     * @return bool
     */
    public function hasCheckedInToday(): bool
    {
        $todayAttendance = $this->getTodayAttendance();
        
        return $todayAttendance && $todayAttendance->check_in_time !== null;
    }

    /**
     * Check if the student has checked out today.
     *
     * @return bool
     */
    public function hasCheckedOutToday(): bool
    {
        $todayAttendance = $this->getTodayAttendance();
        
        return $todayAttendance && $todayAttendance->check_out_time !== null;
    }
}
