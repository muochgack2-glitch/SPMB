<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClass;
use App\Models\AttendanceRecord;
use App\Models\AttendanceStudent;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceDashboardController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display the attendance dashboard
     */
    public function index(Request $request)
    {
        return view('attendance.dashboard.index');
    }

    /**
     * Get real-time statistics (for AJAX refresh)
     */
    public function stats(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $classId = $request->input('class_id');

        $stats = $this->attendanceService->getAttendanceStats($date, $classId);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get today's attendance summary
     */
    public function todaySummary(Request $request)
    {
        $classId = $request->input('class_id');
        $attendance = $this->attendanceService->getTodayAttendance($classId);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Show photo in modal/lightbox
     */
    public function showPhoto($recordId, $type)
    {
        $record = AttendanceRecord::findOrFail($recordId);

        $photoPath = $type === 'check_in' 
            ? $record->check_in_photo 
            : $record->check_out_photo;

        if (!$photoPath || !Storage::exists($photoPath)) {
            abort(404, 'Photo not found');
        }

        return response()->file(storage_path('app/' . $photoPath));
    }
}
