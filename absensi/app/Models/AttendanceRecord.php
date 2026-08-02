<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_photo',
        'check_out_photo',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the student that owns the attendance record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(AttendanceStudent::class, 'student_id');
    }

    /**
     * Scope a query to only include records for today.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope a query to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $classId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByClass($query, int $classId)
    {
        return $query->whereHas('student', function ($q) use ($classId) {
            $q->where('kelas_id', $classId);
        });
    }

    /**
     * Get the check-in photo URL attribute.
     *
     * @return string|null
     */
    public function getCheckInPhotoUrlAttribute(): ?string
    {
        if (!$this->check_in_photo) {
            return null;
        }

        return Storage::disk('public')->url($this->check_in_photo);
    }

    /**
     * Get the check-out photo URL attribute.
     *
     * @return string|null
     */
    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        if (!$this->check_out_photo) {
            return null;
        }

        return Storage::disk('public')->url($this->check_out_photo);
    }

    /**
     * Get the status label attribute (human-readable).
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'alpha' => 'Alpha',
            'izin' => 'Izin',
            default => ucfirst($this->status),
        };
    }
}
