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

        // Return response
        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
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
