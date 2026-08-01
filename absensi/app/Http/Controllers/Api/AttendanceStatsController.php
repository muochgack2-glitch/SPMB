<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceStudent;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AttendanceStatsController extends Controller
{
    /**
     * Get today's attendance statistics.
     * 
     * GET /api/attendance/stats/today
     * 
     * @return JsonResponse
     */
    public function todayStats(): JsonResponse
    {
        $today = Carbon::today();
        
        // Count by status
        $hadir = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'hadir')
            ->count();
            
        $terlambat = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'terlambat')
            ->count();
            
        $izin = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'izin')
            ->count();
            
        $sakit = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'sakit')
            ->count();
            
        // Alpha = Total students - (hadir + terlambat + izin + sakit)
        $totalStudents = AttendanceStudent::where('is_active', true)->count();
        $alpha = $totalStudents - ($hadir + $terlambat + $izin + $sakit);
        
        return response()->json([
            'success' => true,
            'data' => [
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'alpha' => max(0, $alpha), // Prevent negative
                'izin' => $izin,
                'sakit' => $sakit,
                'total' => $totalStudents,
                'date' => $today->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get school hours from settings.
     * 
     * GET /api/attendance/school-hours
     * 
     * @return JsonResponse
     */
    public function schoolHours(): JsonResponse
    {
        // Get settings from key-value structure
        $checkInTime = AttendanceSetting::get('check_in_time', '07:00');
        $checkOutTime = AttendanceSetting::get('check_out_time', '15:00');
        $toleranceMinutes = (int) AttendanceSetting::get('tolerance_minutes', 15);
        
        // Calculate time windows
        $checkInStart = date('H:i', strtotime($checkInTime) - ($toleranceMinutes * 60));
        $checkInEnd = $checkInTime;
        
        $checkOutStart = $checkOutTime;
        $checkOutEnd = date('H:i', strtotime($checkOutTime) + (30 * 60)); // 30 min after
        
        return response()->json([
            'success' => true,
            'data' => [
                'check_in_start' => $checkInStart,
                'check_in_end' => $checkInEnd,
                'check_out_start' => $checkOutStart,
                'check_out_end' => $checkOutEnd,
                'tolerance_minutes' => $toleranceMinutes,
            ],
        ]);
    }

    /**
     * Get active announcement.
     * 
     * GET /api/announcement/active
     * 
     * @return JsonResponse
     */
    public function activeAnnouncement(): JsonResponse
    {
        $message = AttendanceSetting::get('announcement', 'Siswa harap scan QR Code saat masuk gerbang sekolah');
        
        return response()->json([
            'success' => true,
            'data' => [
                'message' => $message,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
