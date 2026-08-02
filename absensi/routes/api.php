<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceScanController;
use App\Http\Controllers\Api\AttendanceStatsController;
use App\Http\Controllers\AttendanceSSEController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// AJAX Login API (dengan session middleware untuk auth)
Route::middleware(['web'])->post('/auth/login', [AuthenticatedSessionController::class, 'store']);

// Attendance Scan API (untuk scanner frontend)
Route::prefix('attendance')->group(function () {
    Route::post('/scan', [AttendanceScanController::class, 'scan']);
    Route::post('/reject', [AttendanceScanController::class, 'reject']);
    
    // Stats API for public landing page
    Route::get('/stats/today', [AttendanceStatsController::class, 'todayStats']);
    Route::get('/school-hours', [AttendanceStatsController::class, 'schoolHours']);
    Route::get('/recent-scans', [AttendanceStatsController::class, 'recentScans']);
    Route::get('/live-data', [AttendanceStatsController::class, 'liveData']);
    
    // SSE for real-time updates
    Route::get('/sse', [AttendanceSSEController::class, 'stream']);
});

// Announcement API
Route::get('/announcement/active', [AttendanceStatsController::class, 'activeAnnouncement']);
