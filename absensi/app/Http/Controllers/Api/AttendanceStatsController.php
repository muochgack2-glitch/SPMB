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

    /**
     * Get recent attendance scans (last 10 for today - both check-in and check-out).
     * 
     * GET /api/attendance/recent-scans
     * 
     * @return JsonResponse
     */
    public function recentScans(): JsonResponse
    {
        $today = Carbon::today();
        
        // Get records with check-in or check-out from today
        $records = AttendanceRecord::with(['student', 'student.kelas'])
            ->whereDate('date', $today)
            ->where(function($query) {
                $query->whereNotNull('check_in_time')
                      ->orWhereNotNull('check_out_time');
            })
            ->orderBy('updated_at', 'desc')
            ->limit(20) // Get more to ensure we have enough after filtering
            ->get();
        
        // Build scan events for both check-in and check-out
        $scanEvents = collect();
        
        foreach ($records as $record) {
            $student = $record->student;
            
            // Add check-out event if exists
            if ($record->check_out_time) {
                $checkOutTime = is_string($record->check_out_time) 
                    ? Carbon::parse($record->check_out_time) 
                    : $record->check_out_time;
                    
                $scanEvents->push([
                    'nama' => $student->nama ?? '-',
                    'nis' => $student->nis ?? '-',
                    'kelas' => $student->kelas?->nama_kelas ?? '-',
                    'status' => $record->status,
                    'time' => $checkOutTime->format('H:i'),
                    'action' => 'check_out',
                    'timestamp' => $checkOutTime->timestamp,
                ]);
            }
            
            // Add check-in event if exists
            if ($record->check_in_time) {
                $checkInTime = is_string($record->check_in_time) 
                    ? Carbon::parse($record->check_in_time) 
                    : $record->check_in_time;
                    
                $scanEvents->push([
                    'nama' => $student->nama ?? '-',
                    'nis' => $student->nis ?? '-',
                    'kelas' => $student->kelas?->nama_kelas ?? '-',
                    'status' => $record->status,
                    'time' => $checkInTime->format('H:i'),
                    'action' => 'check_in',
                    'timestamp' => $checkInTime->timestamp,
                ]);
            }
        }
        
        // Sort by timestamp descending and take 10 most recent
        $recentScans = $scanEvents
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $recentScans,
        ]);
    }

    /**
     * Get complete live data for polling (stats + records + absent students).
     * 
     * GET /api/attendance/live-data
     * 
     * @return JsonResponse
     */
    public function liveData(): JsonResponse
    {
        $today = Carbon::today();
        
        // Get statistics
        $hadir = AttendanceRecord::whereDate('date', $today)->where('status', 'hadir')->count();
        $terlambat = AttendanceRecord::whereDate('date', $today)->where('status', 'terlambat')->count();
        $izin = AttendanceRecord::whereDate('date', $today)->where('status', 'izin')->count();
        $sakit = AttendanceRecord::whereDate('date', $today)->where('status', 'sakit')->count();
        $totalStudents = AttendanceStudent::where('is_active', true)->count();
        $alpha = max(0, $totalStudents - ($hadir + $terlambat + $izin + $sakit));
        
        // Get attendance records
        $records = AttendanceRecord::with(['student', 'student.kelas'])
            ->whereDate('date', $today)
            ->orderBy('check_in_time', 'desc')
            ->get()
            ->map(function($record) {
                return [
                    'id' => $record->id,
                    'nis' => $record->student->nis ?? '-',
                    'nama' => $record->student->nama ?? '-',
                    'kelas' => $record->student->kelas?->nama_kelas ?? '-',
                    'check_in_time' => $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : null,
                    'check_out_time' => $record->check_out_time ? Carbon::parse($record->check_out_time)->format('H:i') : null,
                    'status' => $record->status,
                    'check_in_photo_url' => $record->check_in_photo_url,
                    'check_out_photo_url' => $record->check_out_photo_url,
                ];
            });
        
        // Get absent students (not in today's records)
        $presentStudentIds = AttendanceRecord::whereDate('date', $today)->pluck('student_id');
        $absentStudents = AttendanceStudent::with('kelas')
            ->where('is_active', true)
            ->whereNotIn('id', $presentStudentIds)
            ->orderBy('nama')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'nama' => $student->nama,
                    'kelas' => $student->kelas?->nama_kelas ?? '-',
                ];
            });
        
        return response()->json([
            'success' => true,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'data' => [
                'stats' => [
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'alpha' => $alpha,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'total' => $totalStudents,
                ],
                'records' => $records,
                'absent_students' => $absentStudents,
                'absent_count' => $absentStudents->count(),
            ],
        ]);
    }
}
