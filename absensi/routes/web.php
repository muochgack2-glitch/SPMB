<?php

use App\Http\Controllers\AttendanceDashboardController;
use App\Http\Controllers\AttendanceScanController;
use App\Http\Controllers\AttendanceStudentController;
use App\Http\Controllers\AttendanceClassController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AttendanceQRController;
use App\Http\Controllers\AttendanceSettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login atau dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('attendance.dashboard');
    }
    return redirect()->route('login');
});

// Auth routes
require __DIR__.'/auth.php';

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return redirect()->route('attendance.dashboard');
    })->name('dashboard');

    // Attendance Dashboard
    Route::get('/attendance/dashboard', [AttendanceDashboardController::class, 'index'])
        ->name('attendance.dashboard');

    // QR Scanner
    Route::get('/attendance/scanner', [AttendanceScanController::class, 'index'])
        ->name('attendance.scanner');
    
    Route::post('/attendance/scan', [AttendanceScanController::class, 'scan'])
        ->name('attendance.scan');

    // Students Management
    Route::resource('attendance/students', AttendanceStudentController::class)
        ->names('attendance.students');
    
    Route::get('/attendance/students/import/form', [AttendanceStudentController::class, 'importForm'])
        ->name('attendance.students.import.form');
    
    Route::post('/attendance/students/import', [AttendanceStudentController::class, 'import'])
        ->name('attendance.students.import');
    
    Route::get('/attendance/students/export/template', [AttendanceStudentController::class, 'exportTemplate'])
        ->name('attendance.students.export.template');

    // Classes Management
    Route::resource('attendance/classes', AttendanceClassController::class)
        ->names('attendance.classes');

    // Reports
    Route::get('/attendance/reports', [AttendanceReportController::class, 'index'])
        ->name('attendance.reports.index');
    
    Route::get('/attendance/reports/daily', [AttendanceReportController::class, 'daily'])
        ->name('attendance.reports.daily');
    
    Route::get('/attendance/reports/monthly', [AttendanceReportController::class, 'monthly'])
        ->name('attendance.reports.monthly');
    
    Route::get('/attendance/reports/student/{student}', [AttendanceReportController::class, 'studentHistory'])
        ->name('attendance.reports.student');
    
    Route::post('/attendance/reports/export', [AttendanceReportController::class, 'export'])
        ->name('attendance.reports.export');

    // QR Code Display
    Route::get('/attendance/qr/{student}', [AttendanceQRController::class, 'show'])
        ->name('attendance.qr.show');

    // Settings
    Route::get('/attendance/settings', [AttendanceSettingController::class, 'index'])
        ->name('attendance.settings.index');
    
    Route::put('/attendance/settings', [AttendanceSettingController::class, 'update'])
        ->name('attendance.settings.update');
    
    Route::post('/attendance/settings/test-notification', [AttendanceSettingController::class, 'testNotification'])
        ->name('attendance.settings.test-notification');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
