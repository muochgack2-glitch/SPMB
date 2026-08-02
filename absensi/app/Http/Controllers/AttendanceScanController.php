<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanAttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceScanController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * Handle QR scan and process attendance.
     * 
     * POST /api/attendance/scan
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        // Validate request
        $validated = $request->validated();

        // Process scan
        $result = $this->attendanceService->processScan(
            $validated['nis'],
            $validated['photo_base64'],
            $validated['action']
        );

        // If successful, broadcast to SSE clients
        if ($result['success'] && isset($result['data'])) {
            cache()->put('latest_attendance_scan', [
                'nama' => $result['data']['nama'] ?? '',
                'nis' => $result['data']['nis'] ?? '',
                'kelas' => $result['data']['kelas'] ?? '',
                'status' => $result['data']['status'] ?? '',
                'time' => now()->format('H:i'),
            ], now()->addSeconds(5)); // Cache for 5 seconds
        }

        // Return response
        // Use 200 for all responses (success/business logic errors)
        // Use 422 only for validation errors (handled by FormRequest)
        return response()->json($result, 200);
    }

    /**
     * Reject a scan manually by petugas.
     * 
     * POST /api/attendance/reject
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reject(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'nis' => 'required|string|max:50',
            'reason' => 'required|string|max:255',
        ]);

        // Process rejection
        $result = $this->attendanceService->rejectScan(
            $validated['nis'],
            $validated['reason']
        );

        return response()->json($result);
    }

    /**
     * Show QR scanner interface page.
     * 
     * GET /attendance/scanner
     * 
     * @return \Illuminate\View\View
     */
    public function showScanner()
    {
        return view('attendance.scanner');
    }
}
