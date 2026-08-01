<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceScanController;
use App\Http\Controllers\Api\AttendanceStatsController;

// Attendance Scan API (untuk scanner frontend)
Route::prefix('attendance')->group(function () {
    Route::post('/scan', [AttendanceScanController::class, 'scan']);
    Route::post('/reject', [AttendanceScanController::class, 'reject']);
    
    // Stats API for public landing page
    Route::get('/stats/today', [AttendanceStatsController::class, 'todayStats']);
    Route::get('/school-hours', [AttendanceStatsController::class, 'schoolHours']);
});

// Announcement API
Route::get('/announcement/active', [AttendanceStatsController::class, 'activeAnnouncement']);
