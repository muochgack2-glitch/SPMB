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

// Public Scanner Landing Page (no auth required)
Route::get('/', function () {
    return view('welcome');
})->name('home');

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
    
    Route::get('/attendance/dashboard/refresh', [AttendanceDashboardController::class, 'refresh'])
        ->name('attendance.dashboard.refresh');
    
    // API endpoint for sidebar badge
    Route::get('/api/attendance/today-stats', [AttendanceDashboardController::class, 'todayStats'])
        ->name('api.attendance.today-stats');

    // QR Scanner
    Route::get('/attendance/scanner', [AttendanceScanController::class, 'showScanner'])
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
    
    Route::post('/attendance/reports/generate', [AttendanceReportController::class, 'generate'])
        ->name('attendance.reports.generate');
    
    Route::get('/attendance/reports/export-summary', [AttendanceReportController::class, 'exportSummary'])
        ->name('attendance.reports.export-summary');

    // QR Code Display
    Route::get('/attendance/qr/{student}', [AttendanceQRController::class, 'show'])
        ->name('attendance.qr.show');
    
    Route::get('/attendance/qr/{student}/download', [AttendanceQRController::class, 'download'])
        ->name('attendance.qr.download');
    
    Route::post('/attendance/qr/{student}/regenerate', [AttendanceQRController::class, 'regenerate'])
        ->name('attendance.qr.regenerate');

    // Settings
    Route::get('/attendance/settings', [AttendanceSettingController::class, 'index'])
        ->name('attendance.settings.index');
    
    Route::put('/attendance/settings', [AttendanceSettingController::class, 'update'])
        ->name('attendance.settings.update');
    
    Route::post('/attendance/settings/reset', [AttendanceSettingController::class, 'reset'])
        ->name('attendance.settings.reset');
    
    Route::post('/attendance/settings/test-notification', [AttendanceSettingController::class, 'testNotification'])
        ->name('attendance.settings.test-notification');

    // WhatsApp Gateway
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('index');
        Route::get('/status', [App\Http\Controllers\WhatsAppController::class, 'getStatus'])->name('status');
        Route::get('/qr', [App\Http\Controllers\WhatsAppController::class, 'getQRCode'])->name('qr');
        Route::get('/health', [App\Http\Controllers\WhatsAppController::class, 'health'])->name('health');
        
        Route::get('/send', [App\Http\Controllers\WhatsAppController::class, 'sendPage'])->name('send');
        Route::post('/send', [App\Http\Controllers\WhatsAppController::class, 'sendMessage'])->name('send.submit');
        Route::post('/test', [App\Http\Controllers\WhatsAppController::class, 'testNotification'])->name('test');
        
        // Message Logs
        Route::get('/logs', [App\Http\Controllers\WhatsAppController::class, 'logs'])->name('logs');
        Route::get('/student/{studentId}/messages', [App\Http\Controllers\WhatsAppController::class, 'getStudentMessages'])->name('student.messages');
        
        // Settings
        Route::get('/settings', [App\Http\Controllers\WhatsAppController::class, 'settings'])->name('settings');
        Route::post('/settings', [App\Http\Controllers\WhatsAppController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/reset', [App\Http\Controllers\WhatsAppController::class, 'resetSettings'])->name('settings.reset');
        
        // Gateway Control
        Route::post('/gateway/start', [App\Http\Controllers\WhatsAppController::class, 'startGateway'])->name('gateway.start');
        Route::post('/gateway/stop', [App\Http\Controllers\WhatsAppController::class, 'stopGateway'])->name('gateway.stop');
        Route::get('/gateway/process-status', [App\Http\Controllers\WhatsAppController::class, 'getGatewayProcessStatus'])->name('gateway.process-status');
        
        Route::post('/logout', [App\Http\Controllers\WhatsAppController::class, 'logout'])->name('logout');
        Route::post('/restart', [App\Http\Controllers\WhatsAppController::class, 'restart'])->name('restart');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
