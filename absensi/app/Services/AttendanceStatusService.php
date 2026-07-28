<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use Carbon\Carbon;

class AttendanceStatusService
{
    /**
     * Determine attendance status based on check-in time.
     *
     * @param string $checkInTime Time in H:i:s format (e.g., '07:15:00')
     * @return string Status: 'hadir', 'terlambat', or 'alpha'
     */
    public function determineStatus(string $checkInTime): string
    {
        // Get settings
        $officialCheckInTime = AttendanceSetting::get('check_in_time', '07:00');
        $toleranceMinutes = (int) AttendanceSetting::get('tolerance_minutes', 15);

        // Parse times
        $checkIn = Carbon::createFromTimeString($checkInTime);
        $official = Carbon::createFromTimeString($officialCheckInTime);
        $toleranceEnd = $official->copy()->addMinutes($toleranceMinutes);

        // Determine status
        if ($checkIn->lessThanOrEqualTo($toleranceEnd)) {
            return 'hadir';
        }

        return 'terlambat';
    }

    /**
     * Check if current time is within check-in window.
     *
     * @param string|null $time Optional time to check (defaults to now)
     * @return bool True if within check-in window
     */
    public function isWithinCheckInWindow(?string $time = null): bool
    {
        // Get cutoff time from settings
        $cutoffTime = AttendanceSetting::get('cutoff_time', '09:00');

        // Use provided time or current time
        $currentTime = $time ? Carbon::createFromTimeString($time) : Carbon::now();
        $cutoff = Carbon::createFromTimeString($cutoffTime);

        // Check if before cutoff time
        return $currentTime->lessThan($cutoff);
    }

    /**
     * Check if current time is within check-out window.
     *
     * @param string|null $time Optional time to check (defaults to now)
     * @return bool True if within check-out window
     */
    public function isWithinCheckOutWindow(?string $time = null): bool
    {
        // Get check-out time from settings
        $officialCheckOutTime = AttendanceSetting::get('check_out_time', '15:00');

        // Use provided time or current time
        $currentTime = $time ? Carbon::createFromTimeString($time) : Carbon::now();
        $checkOutTime = Carbon::createFromTimeString($officialCheckOutTime);

        // Allow check-out from official time onwards
        return $currentTime->greaterThanOrEqualTo($checkOutTime);
    }

    /**
     * Get time window information for display.
     *
     * @return array Time window details
     */
    public function getTimeWindowInfo(): array
    {
        $checkInTime = AttendanceSetting::get('check_in_time', '07:00');
        $checkOutTime = AttendanceSetting::get('check_out_time', '15:00');
        $toleranceMinutes = (int) AttendanceSetting::get('tolerance_minutes', 15);
        $cutoffTime = AttendanceSetting::get('cutoff_time', '09:00');

        $official = Carbon::createFromTimeString($checkInTime);
        $toleranceEnd = $official->copy()->addMinutes($toleranceMinutes);

        return [
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'tolerance_minutes' => $toleranceMinutes,
            'tolerance_end' => $toleranceEnd->format('H:i'),
            'cutoff_time' => $cutoffTime,
            'is_within_check_in_window' => $this->isWithinCheckInWindow(),
            'is_within_check_out_window' => $this->isWithinCheckOutWindow(),
        ];
    }

    /**
     * Get status label (human-readable).
     *
     * @param string $status Status code
     * @return string Localized label
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'alpha' => 'Alpha',
            'izin' => 'Izin',
            default => ucfirst($status),
        };
    }

    /**
     * Validate if a check-in time is acceptable.
     *
     * @param string $checkInTime Time in H:i:s format
     * @return array ['valid' => bool, 'message' => string, 'status' => string|null]
     */
    public function validateCheckInTime(string $checkInTime): array
    {
        // Check if within window
        if (!$this->isWithinCheckInWindow($checkInTime)) {
            return [
                'valid' => false,
                'message' => 'Check-in time is past the cutoff time',
                'status' => null,
            ];
        }

        // Determine status
        $status = $this->determineStatus($checkInTime);

        return [
            'valid' => true,
            'message' => 'Check-in time is valid',
            'status' => $status,
        ];
    }

    /**
     * Calculate minutes late based on check-in time.
     *
     * @param string $checkInTime Time in H:i:s format
     * @return int Minutes late (0 if on time or early)
     */
    public function calculateMinutesLate(string $checkInTime): int
    {
        $officialCheckInTime = AttendanceSetting::get('check_in_time', '07:00');
        $toleranceMinutes = (int) AttendanceSetting::get('tolerance_minutes', 15);

        $checkIn = Carbon::createFromTimeString($checkInTime);
        $toleranceEnd = Carbon::createFromTimeString($officialCheckInTime)
            ->addMinutes($toleranceMinutes);

        if ($checkIn->lessThanOrEqualTo($toleranceEnd)) {
            return 0;
        }

        return $checkIn->diffInMinutes($toleranceEnd);
    }
}
