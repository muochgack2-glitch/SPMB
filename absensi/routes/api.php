<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceScanController;

// Attendance Scan API (untuk scanner frontend)
Route::prefix('attendance')->group(function () {
    Route::post('/scan', [AttendanceScanController::class, 'scan']);
    Route::post('/reject', [AttendanceScanController::class, 'reject']);
});
