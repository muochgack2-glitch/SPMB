# Design Document: Sistem Absensi Siswa

## Overview

Sistem Absensi Siswa adalah aplikasi berbasis QR Code Scanner dengan foto capture otomatis untuk mencatat kehadiran siswa dan memberikan notifikasi real-time kepada orang tua via WhatsApp. Sistem ini menggunakan Laravel 11 + Livewire dengan WhatsApp Gateway pada port 3001 untuk notifikasi.

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         User Layer                               │
├──────────────┬───────────────────────┬─────────────────────────┤
│   Siswa      │    Orang Tua         │    Admin/Guru           │
│ (QR Code)    │    (WhatsApp)        │   (Web Browser)         │
└──────┬───────┴───────────┬───────────┴──────────┬──────────────┘
       │                   │                      │
       │ Show QR Code      │  Notifikasi         │  Dashboard
       ↓                   ↑                      ↓
┌──────────────────────────────────────────────────────────────────┐
│           QR Scanner Station (Browser + Webcam)                  │
│           - Scan QR Code                                         │
│           - Capture Photo                                        │
│           - Display Confirmation                                 │
└──────────────────┬───────────────────────────────────────────────┘
                   │ HTTP POST /api/attendance/scan
                   ↓
┌──────────────────────────────────────────────────────────────────┐
│           Laravel 11 Application                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Controllers Layer                                      │    │
│  │  - AttendanceScanController                             │    │
│  │  - AttendanceQRController                               │    │
│  │  - AttendanceDashboardController                        │    │
│  │  - AttendanceStudentController                          │    │
│  │  - AttendanceClassController                            │    │
│  └────────────────────────┬───────────────────────────────┘    │
│                           ↓                                      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Service Layer                                          │    │
│  │  - AttendanceService                                    │    │
│  │  - QRCodeService                                        │    │
│  │  - PhotoCaptureService                                  │    │
│  │  - AttendanceWhatsAppService                            │    │
│  │  - AttendanceNotificationService                        │    │
│  │  - AttendanceStatusService                              │    │
│  │  - AttendanceExportService                              │    │
│  └────────────────────────┬───────────────────────────────┘    │
│                           ↓                                      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Livewire Components (Real-time)                        │    │
│  │  - AttendanceDashboard                                  │    │
│  │  - AttendanceStudentTable                               │    │
│  │  - AttendanceReportGenerator                            │    │
│  │  - QRScannerInterface                                   │    │
│  └────────────────────────┬───────────────────────────────┘    │
│                           ↓                                      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Model Layer                                            │    │
│  │  - AttendanceStudent                                    │    │
│  │  - AttendanceClass                                      │    │
│  │  - AttendanceRecord                                     │    │
│  │  - AttendanceSetting                                    │    │
│  └────────────────────────┬───────────────────────────────┘    │
└───────────────────────────┼────────────────────────────────────┘
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│                    MySQL Database                                 │
│  - attendance_students                                           │
│  - attendance_classes                                            │
│  - attendance_records                                            │
│  - attendance_settings                                           │
│  - attendance_logs                                               │
└──────────────────────────────────────────────────────────────────┘
                           │
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│                File Storage (Photos)                             │
│  storage/app/attendance/photos/{NIS}/{date}/                    │
│  - checkin_timestamp.jpg                                         │
│  - checkout_timestamp.jpg                                        │
└──────────────────────────────────────────────────────────────────┘
                           │
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│           WhatsApp Gateway (Port 3001) - Notifikasi Only        │
│           - whatsapp-web.js                                      │
│           - PM2 Process Manager                                  │
└──────────────────────────────────────────────────────────────────┘
```


## Database Schema

### Table: attendance_students
```sql
CREATE TABLE attendance_students (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nis VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    no_hp_ortu VARCHAR(20),
    qr_code_path VARCHAR(255),          -- Path to generated QR Code image
    foto_profil VARCHAR(255),            -- Student profile photo
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nis (nis),
    INDEX idx_kelas (kelas_id),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (kelas_id) REFERENCES attendance_classes(id) ON DELETE RESTRICT
);
```

### Table: attendance_classes
```sql
CREATE TABLE attendance_classes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama_kelas VARCHAR(100) NOT NULL,
    tingkat VARCHAR(10) NOT NULL,  -- '10', '11', '12'
    jurusan VARCHAR(100),           -- 'RPL', 'TKJ', etc
    wali_kelas_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_class (nama_kelas, tingkat),
    INDEX idx_tingkat (tingkat),
    INDEX idx_active (is_active)
);
```

### Table: attendance_records
```sql
CREATE TABLE attendance_records (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    check_in_photo VARCHAR(255),         -- Path to check-in photo
    check_out_photo VARCHAR(255),        -- Path to check-out photo
    status ENUM('hadir', 'terlambat', 'alpha') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_attendance (student_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status),
    INDEX idx_student_date (student_id, date),
    
    FOREIGN KEY (student_id) REFERENCES attendance_students(id) ON DELETE CASCADE
);
```


### Table: attendance_settings
```sql
CREATE TABLE attendance_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    group_name VARCHAR(50) NOT NULL,     -- 'time', 'tolerance', 'notification'
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_group (group_name),
    INDEX idx_key (key)
);

-- Default settings
INSERT INTO attendance_settings (key, value, group_name, description) VALUES
('check_in_time', '07:00', 'time', 'Jam masuk resmi'),
('check_out_time', '15:00', 'time', 'Jam pulang resmi'),
('tolerance_minutes', '15', 'tolerance', 'Toleransi keterlambatan (menit)'),
('cutoff_time', '09:00', 'time', 'Batas waktu absen masuk (setelah ini = alpha)'),
('enable_parent_notification', 'true', 'notification', 'Aktifkan notifikasi orang tua'),
('include_photo_in_notification', 'false', 'notification', 'Sertakan foto dalam notifikasi WA'),
('school_name', 'SMK PGRI BLORA', 'general', 'Nama sekolah');
```

### Table: attendance_logs
```sql
CREATE TABLE attendance_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED,
    action ENUM('check_in', 'check_out', 'notification', 'qr_scan', 'reject', 'error') NOT NULL,
    message TEXT,
    response TEXT,
    status ENUM('success', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_student (student_id),
    INDEX idx_action (action),
    INDEX idx_date (created_at),
    
    FOREIGN KEY (student_id) REFERENCES attendance_students(id) ON DELETE SET NULL
);
```

## Data Relationships

```
attendance_classes (1) ----< (N) attendance_students
attendance_students (1) ----< (N) attendance_records
attendance_students (1) ----< (N) attendance_logs
```


## Service Layer Design

### AttendanceService
**Purpose:** Core business logic untuk proses absensi

```php
class AttendanceService
{
    public function processCheckIn(string $phone): array
    {
        // 1. Find student by phone
        // 2. Validate: belum absen hari ini?
        // 3. Validate: dalam waktu check-in?
        // 4. Determine status (hadir/terlambat)
        // 5. Create attendance record
        // 6. Log the action
        // 7. Trigger parent notification
        // 8. Return confirmation message
    }
    
    public function processCheckOut(string $phone): array
    {
        // 1. Find student by phone
        // 2. Validate: sudah check-in hari ini?
        // 3. Validate: belum check-out hari ini?
        // 4. Update attendance record
        // 5. Log the action
        // 6. Trigger parent notification
        // 7. Return confirmation message
    }
    
    public function markAbsentStudents(): int
    {
        // Called by scheduler at cutoff time
        // 1. Get all students who haven't checked in today
        // 2. Create attendance records with status 'alpha'
        // 3. Return count of marked students
    }
    
    public function getTodayAttendance(?int $classId = null): Collection
    {
        // Get today's attendance records
        // Filter by class if provided
    }
    
    public function getAttendanceStats(string $date): array
    {
        // Return: total_hadir, total_terlambat, total_alpha, total_belum
    }
}
```

### AttendanceWhatsAppService
**Purpose:** Handle WhatsApp communication untuk absensi

```php
class AttendanceWhatsAppService
{
    protected $baseUrl = 'http://localhost:3001';  // Port 3001
    
    public function sendConfirmation(string $phone, string $message): array
    {
        // Send confirmation to student
        // POST {baseUrl}/api/send
    }
    
    public function sendParentNotification(string $phone, string $message): array
    {
        // Send notification to parent
        // POST {baseUrl}/api/send
    }
    
    public function normalizePhone(string $phone): string
    {
        // Convert to international format: 628xxx
    }
    
    public function getGatewayStatus(): array
    {
        // GET {baseUrl}/api/status
    }
}
```


### AttendanceNotificationService
**Purpose:** Format dan kirim notifikasi ke orang tua

```php
class AttendanceNotificationService
{
    public function notifyCheckIn(AttendanceStudent $student, AttendanceRecord $record): void
    {
        $message = $this->formatCheckInMessage($student, $record);
        $this->send($student->no_hp_ortu, $message);
    }
    
    public function notifyCheckOut(AttendanceStudent $student, AttendanceRecord $record): void
    {
        $message = $this->formatCheckOutMessage($student, $record);
        $this->send($student->no_hp_ortu, $message);
    }
    
    private function formatCheckInMessage($student, $record): string
    {
        return "[ABSENSI]\n" .
               "Ananda {$student->nama} telah absen masuk pada {$record->check_in_time}.\n" .
               "Status: " . ucfirst($record->status);
    }
    
    private function formatCheckOutMessage($student, $record): string
    {
        return "[ABSENSI]\n" .
               "Ananda {$student->nama} telah absen pulang pada {$record->check_out_time}.";
    }
}
```

### AttendanceStatusService
**Purpose:** Tentukan status kehadiran berdasarkan waktu

```php
class AttendanceStatusService
{
    public function determineStatus(Carbon $checkInTime): string
    {
        $settings = $this->getSettings();
        $officialTime = Carbon::parse($settings->check_in_time);
        $toleranceEnd = $officialTime->copy()->addMinutes($settings->tolerance_minutes);
        
        if ($checkInTime->lte($toleranceEnd)) {
            return 'hadir';
        }
        
        return 'terlambat';
    }
    
    public function isWithinCheckInWindow(Carbon $time): bool
    {
        $settings = $this->getSettings();
        $start = Carbon::parse('05:00');
        $end = Carbon::parse($settings->cutoff_time);
        
        return $time->between($start, $end);
    }
}
```

### AttendanceExportService
**Purpose:** Export data ke Excel

```php
class AttendanceExportService
{
    public function exportToExcel(array $filters): string
    {
        // Use Laravel Excel package
        // 1. Query attendance records with filters
        // 2. Format data
        // 3. Generate Excel file
        // 4. Return file path
    }
}
```


## Controllers & Routes

### AttendanceWebhookController
**Purpose:** Handle incoming WhatsApp messages

```php
// Route: POST /api/attendance/webhook
class AttendanceWebhookController extends Controller
{
    public function handleIncoming(Request $request)
    {
        // 1. Extract: phone, message, timestamp
        // 2. Normalize phone number
        // 3. Detect keyword: "ABSEN MASUK" or "ABSEN PULANG"
        // 4. Route to appropriate service method
        // 5. Return response for WhatsApp gateway
    }
}
```

### AttendanceDashboardController
**Purpose:** Show admin dashboard

```php
// Route: GET /attendance/dashboard
class AttendanceDashboardController extends Controller
{
    public function index()
    {
        // Render Livewire dashboard component
        return view('attendance.dashboard');
    }
}
```

### AttendanceStudentController
**Purpose:** CRUD for students

```php
// Routes:
// GET    /attendance/students
// GET    /attendance/students/create
// POST   /attendance/students
// GET    /attendance/students/{id}/edit
// PUT    /attendance/students/{id}
// DELETE /attendance/students/{id}
// POST   /attendance/students/import  (Excel import)

class AttendanceStudentController extends Controller
{
    public function index()
    {
        // List students with search, filter, pagination
    }
    
    public function store(Request $request)
    {
        // Create new student
        // Validate phone numbers
    }
    
    public function importExcel(Request $request)
    {
        // Validate Excel file
        // Parse and create students
        // Return success/error report
    }
}
```

### AttendanceClassController
**Purpose:** CRUD for classes

```php
// Routes:
// GET    /attendance/classes
// POST   /attendance/classes
// PUT    /attendance/classes/{id}
// DELETE /attendance/classes/{id}

class AttendanceClassController extends Controller
{
    public function destroy($id)
    {
        // Prevent deletion if class has students
    }
}
```


### AttendanceReportController
**Purpose:** Generate reports and exports

```php
// Routes:
// GET  /attendance/reports
// POST /attendance/reports/export

class AttendanceReportController extends Controller
{
    public function index()
    {
        // Show report page with filters
    }
    
    public function export(Request $request)
    {
        // Get filters: date_from, date_to, class_id
        // Generate Excel
        // Download file
    }
}
```

### AttendanceSettingController
**Purpose:** Manage attendance settings

```php
// Routes:
// GET  /attendance/settings
// POST /attendance/settings

class AttendanceSettingController extends Controller
{
    public function index()
    {
        // Show settings form
    }
    
    public function update(Request $request)
    {
        // Update check_in_time, check_out_time, etc.
        // Validate cutoff_time > (check_in_time + tolerance)
    }
}
```

## Route Registration

```php
// routes/web.php

Route::prefix('attendance')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AttendanceDashboardController::class, 'index'])
        ->name('attendance.dashboard');
    
    // Students
    Route::resource('students', AttendanceStudentController::class);
    Route::post('students/import', [AttendanceStudentController::class, 'importExcel'])
        ->name('attendance.students.import');
    
    // Classes
    Route::resource('classes', AttendanceClassController::class)
        ->except(['show']);
    
    // Reports
    Route::get('reports', [AttendanceReportController::class, 'index'])
        ->name('attendance.reports');
    Route::post('reports/export', [AttendanceReportController::class, 'export'])
        ->name('attendance.reports.export');
    
    // Settings
    Route::get('settings', [AttendanceSettingController::class, 'index'])
        ->name('attendance.settings');
    Route::post('settings', [AttendanceSettingController::class, 'update'])
        ->name('attendance.settings.update');
});

// Webhook (no auth, from WhatsApp Gateway)
Route::post('/api/attendance/webhook', [AttendanceWebhookController::class, 'handleIncoming']);
```


## Livewire Components

### AttendanceDashboard
**Purpose:** Real-time dashboard untuk monitoring hari ini

```php
// File: app/Livewire/AttendanceDashboard.php
class AttendanceDashboard extends Component
{
    public $selectedClass = null;
    public $stats = [];
    public $students = [];
    
    protected $listeners = ['refreshDashboard' => '$refresh'];
    
    public function mount()
    {
        $this->loadData();
    }
    
    public function loadData()
    {
        $this->stats = $this->attendanceService->getAttendanceStats(today());
        $this->students = $this->attendanceService->getTodayAttendance($this->selectedClass);
    }
    
    public function updatedSelectedClass()
    {
        $this->loadData();
    }
    
    public function render()
    {
        return view('livewire.attendance-dashboard');
    }
}

// Polling every 30 seconds
// In view: wire:poll.30s="loadData"
```

### AttendanceStudentTable
**Purpose:** Manage students with search, filter, CRUD

```php
// File: app/Livewire/AttendanceStudentTable.php
class AttendanceStudentTable extends Component
{
    public $search = '';
    public $classFilter = null;
    public $perPage = 20;
    
    protected $queryString = ['search', 'classFilter'];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function delete($id)
    {
        AttendanceStudent::findOrFail($id)->delete();
        session()->flash('message', 'Siswa berhasil dihapus');
    }
    
    public function render()
    {
        $students = AttendanceStudent::query()
            ->when($this->search, fn($q) => $q->where('nama', 'like', "%{$this->search}%"))
            ->when($this->classFilter, fn($q) => $q->where('kelas_id', $this->classFilter))
            ->with('kelas')
            ->paginate($this->perPage);
        
        return view('livewire.attendance-student-table', compact('students'));
    }
}
```

### AttendanceReportGenerator
**Purpose:** Generate reports with filters

```php
// File: app/Livewire/AttendanceReportGenerator.php
class AttendanceReportGenerator extends Component
{
    public $dateFrom;
    public $dateTo;
    public $classId = null;
    public $status = null;
    public $preview = [];
    
    public function mount()
    {
        $this->dateFrom = today()->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');
    }
    
    public function generatePreview()
    {
        $this->preview = AttendanceRecord::query()
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->classId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('kelas_id', $this->classId)))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->with(['student.kelas'])
            ->get();
    }
    
    public function export()
    {
        return $this->exportService->exportToExcel([
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'class_id' => $this->classId,
            'status' => $this->status
        ]);
    }
}
```


## WhatsApp Message Flow

### Check-In Flow (Absen Masuk)

```
1. Student sends "ABSEN MASUK" via WhatsApp
   ↓
2. WhatsApp Gateway (port 3001) receives message
   - whatsapp-web.js detects incoming message
   ↓
3. Gateway sends HTTP POST to Laravel
   POST /api/attendance/webhook
   Body: {
     "phone": "628123456789",
     "message": "ABSEN MASUK",
     "timestamp": "2024-01-15 07:10:00"
   }
   ↓
4. AttendanceWebhookController::handleIncoming()
   - Normalize phone: 628123456789
   - Detect keyword: "ABSEN MASUK"
   ↓
5. AttendanceService::processCheckIn("628123456789")
   ├─ Find student by phone (no_hp_siswa)
   ├─ Validate: Student exists?
   │   NO → Return error message "Nomor tidak terdaftar"
   │   YES → Continue
   ├─ Validate: Already checked in today?
   │   YES → Return "Anda sudah absen masuk hari ini"
   │   NO → Continue
   ├─ Validate: Within check-in window (05:00 - cutoff)?
   │   NO → Return "Waktu absensi masuk telah berakhir"
   │   YES → Continue
   ├─ Determine status (AttendanceStatusService)
   │   ├─ Time <= 07:15 → status = 'hadir'
   │   └─ Time > 07:15 → status = 'terlambat'
   ├─ Create AttendanceRecord
   │   INSERT INTO attendance_records (student_id, date, check_in_time, status)
   ├─ Log action
   │   INSERT INTO attendance_logs (student_id, phone, action='check_in', status='success')
   ├─ Send parent notification (async via Queue)
   │   Queue: SendParentNotification
   │   → AttendanceNotificationService::notifyCheckIn()
   │   → AttendanceWhatsAppService::sendParentNotification()
   └─ Return confirmation message
   ↓
6. Return response to Gateway
   Response: {
     "success": true,
     "reply": "✅ Absen masuk tercatat pada 07:10 WIB\nStatus: Hadir\nSelamat belajar!"
   }
   ↓
7. Gateway sends reply to student
   Student receives confirmation in WhatsApp
   
TOTAL TIME: < 3 seconds
```

### Check-Out Flow (Absen Pulang)

```
1. Student sends "ABSEN PULANG"
   ↓
2-4. Same as check-in flow (webhook → controller → service)
   ↓
5. AttendanceService::processCheckOut("628123456789")
   ├─ Find student by phone
   ├─ Validate: Has checked in today?
   │   NO → Return "Anda belum absen masuk hari ini"
   │   YES → Continue
   ├─ Validate: Already checked out today?
   │   YES → Return "Anda sudah absen pulang hari ini"
   │   NO → Continue
   ├─ Update AttendanceRecord
   │   UPDATE attendance_records SET check_out_time = '15:30:00'
   │   WHERE student_id = X AND date = today()
   ├─ Log action
   ├─ Send parent notification (async)
   └─ Return confirmation message
   ↓
6. Return response
   Response: {
     "success": true,
     "reply": "✅ Absen pulang tercatat pada 15:30 WIB\nHati-hati di jalan!"
   }
   ↓
7. Gateway sends reply to student
```


## Scheduled Tasks

### Daily Alpha Marking

```php
// File: app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Mark absent students as Alpha at cutoff time (09:00)
    $schedule->call(function () {
        $service = app(AttendanceService::class);
        $count = $service->markAbsentStudents();
        
        Log::info("Auto-marked {$count} students as Alpha");
    })->dailyAt('09:00');
    
    // Optional: Send summary to admin
    $schedule->call(function () {
        $service = app(AttendanceService::class);
        $stats = $service->getAttendanceStats(today());
        
        // Send WA to admin/principal with daily summary
    })->dailyAt('16:00');
}
```

### Livewire Polling

```blade
{{-- In dashboard view --}}
<div wire:poll.30s="loadData">
    {{-- Dashboard content auto-refreshes every 30 seconds --}}
</div>
```

## Configuration

### Environment Variables

```env
# .env file additions

# Attendance WhatsApp Gateway
ATTENDANCE_WA_GATEWAY_URL=http://localhost:3001

# Attendance Time Settings (defaults, can be changed in UI)
ATTENDANCE_CHECKIN_TIME=07:00
ATTENDANCE_CHECKOUT_TIME=15:00
ATTENDANCE_TOLERANCE_MINUTES=15
ATTENDANCE_CUTOFF_TIME=09:00

# Notification Settings
ATTENDANCE_ENABLE_PARENT_NOTIFICATION=true

# School Info
ATTENDANCE_SCHOOL_NAME="SMK PGRI BLORA"
```

### Config File

```php
// config/attendance.php

return [
    'gateway_url' => env('ATTENDANCE_WA_GATEWAY_URL', 'http://localhost:3001'),
    
    'time' => [
        'check_in' => env('ATTENDANCE_CHECKIN_TIME', '07:00'),
        'check_out' => env('ATTENDANCE_CHECKOUT_TIME', '15:00'),
        'cutoff' => env('ATTENDANCE_CUTOFF_TIME', '09:00'),
        'tolerance_minutes' => env('ATTENDANCE_TOLERANCE_MINUTES', 15),
    ],
    
    'notification' => [
        'enabled' => env('ATTENDANCE_ENABLE_PARENT_NOTIFICATION', true),
    ],
    
    'school' => [
        'name' => env('ATTENDANCE_SCHOOL_NAME', 'SMK PGRI BLORA'),
    ],
];
```


## Laravel Models

### AttendanceStudent Model

```php
namespace App\Models;

class AttendanceStudent extends Model
{
    protected $fillable = [
        'nis', 'nama', 'kelas_id', 'no_hp_siswa', 
        'no_hp_ortu', 'foto', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function kelas()
    {
        return $this->belongsTo(AttendanceClass::class, 'kelas_id');
    }
    
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }
    
    public function logs()
    {
        return $this->hasMany(AttendanceLog::class, 'student_id');
    }
    
    // Helper methods
    public function getTodayAttendance()
    {
        return $this->attendanceRecords()
            ->whereDate('date', today())
            ->first();
    }
    
    public function hasCheckedInToday(): bool
    {
        return $this->attendanceRecords()
            ->whereDate('date', today())
            ->whereNotNull('check_in_time')
            ->exists();
    }
    
    public function hasCheckedOutToday(): bool
    {
        return $this->attendanceRecords()
            ->whereDate('date', today())
            ->whereNotNull('check_out_time')
            ->exists();
    }
}
```

### AttendanceClass Model

```php
namespace App\Models;

class AttendanceClass extends Model
{
    protected $fillable = [
        'nama_kelas', 'tingkat', 'jurusan', 
        'wali_kelas_id', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function students()
    {
        return $this->hasMany(AttendanceStudent::class, 'kelas_id');
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Helper
    public function getStudentCount()
    {
        return $this->students()->where('is_active', true)->count();
    }
}
```


### AttendanceRecord Model

```php
namespace App\Models;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'student_id', 'date', 'check_in_time', 
        'check_out_time', 'status', 'notes'
    ];
    
    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
    ];
    
    // Relationships
    public function student()
    {
        return $this->belongsTo(AttendanceStudent::class, 'student_id');
    }
    
    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }
    
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    public function scopeByClass($query, int $classId)
    {
        return $query->whereHas('student', fn($q) => $q->where('kelas_id', $classId));
    }
    
    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'alpha' => 'Alpha',
            default => 'Unknown'
        };
    }
}
```

### AttendanceSetting Model

```php
namespace App\Models;

class AttendanceSetting extends Model
{
    protected $fillable = ['key', 'value', 'group_name', 'description'];
    
    // Static helper to get setting value
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    // Static helper to set setting value
    public static function set(string $key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
```

### AttendanceLog Model

```php
namespace App\Models;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id', 'phone', 'action', 
        'message', 'response', 'status'
    ];
    
    public $timestamps = false; // Only created_at
    
    // Relationships
    public function student()
    {
        return $this->belongsTo(AttendanceStudent::class, 'student_id');
    }
}
```

## Testing Strategy

### Unit Tests

```php
// tests/Unit/AttendanceServiceTest.php
class AttendanceServiceTest extends TestCase
{
    public function test_can_process_check_in_for_valid_student()
    {
        // Given: A student with phone number
        // When: ProcessCheckIn is called
        // Then: Attendance record is created with correct status
    }
    
    public function test_cannot_check_in_twice_same_day()
    {
        // Given: Student already checked in today
        // When: ProcessCheckIn is called again
        // Then: Returns error message
    }
    
    public function test_determines_late_status_correctly()
    {
        // Given: Check-in time after tolerance period
        // When: Determining status
        // Then: Status is 'terlambat'
    }
}
```

### Feature Tests

```php
// tests/Feature/AttendanceWebhookTest.php
class AttendanceWebhookTest extends TestCase
{
    public function test_webhook_handles_absen_masuk()
    {
        // Given: Valid webhook payload with "ABSEN MASUK"
        // When: POST /api/attendance/webhook
        // Then: Response contains success and confirmation message
    }
    
    public function test_webhook_rejects_unregistered_phone()
    {
        // Given: Webhook payload with unregistered phone
        // When: POST /api/attendance/webhook
        // Then: Response contains error "Nomor tidak terdaftar"
    }
}
```

### Integration Tests

```php
// tests/Integration/WhatsAppNotificationTest.php
class WhatsAppNotificationTest extends TestCase
{
    public function test_parent_receives_notification_after_check_in()
    {
        // Given: Student checks in
        // When: Notification is triggered
        // Then: WhatsApp service is called with parent's phone
        // And: Message contains student name and time
    }
}
```


## Security Considerations

### Webhook Authentication

```php
// Add middleware to verify requests from WhatsApp Gateway
// Option 1: API Token
Route::post('/api/attendance/webhook', [AttendanceWebhookController::class, 'handleIncoming'])
    ->middleware('verify.gateway.token');

// Option 2: IP Whitelist
// Only allow localhost:3001 for now
```

### Data Validation

```php
// Always validate phone numbers
public function processCheckIn(string $phone)
{
    $phone = $this->normalizePhone($phone);
    
    if (!$this->isValidPhone($phone)) {
        throw new InvalidPhoneException();
    }
    
    // Continue...
}
```

### Rate Limiting

```php
// Prevent abuse - limit webhook calls
Route::post('/api/attendance/webhook', [AttendanceWebhookController::class, 'handleIncoming'])
    ->middleware('throttle:60,1'); // 60 requests per minute
```

## Performance Optimization

### Database Indexes

```sql
-- Already included in schema, but emphasizing importance:
CREATE INDEX idx_student_date ON attendance_records(student_id, date);
CREATE INDEX idx_phone_lookup ON attendance_students(no_hp_siswa);
CREATE INDEX idx_date_filter ON attendance_records(date);
```

### Query Optimization

```php
// Use eager loading to prevent N+1 queries
$students = AttendanceStudent::with(['kelas', 'attendanceRecords'])
    ->get();

// Use select only needed columns
$records = AttendanceRecord::select('id', 'student_id', 'date', 'status')
    ->today()
    ->get();
```

### Caching

```php
// Cache settings to avoid repeated DB queries
public function getSettings()
{
    return Cache::remember('attendance_settings', 3600, function () {
        return AttendanceSetting::all()->pluck('value', 'key');
    });
}

// Cache today's stats for dashboard
public function getAttendanceStats()
{
    return Cache::remember('attendance_stats_' . today(), 60, function () {
        return [
            'total_hadir' => AttendanceRecord::today()->byStatus('hadir')->count(),
            'total_terlambat' => AttendanceRecord::today()->byStatus('terlambat')->count(),
            'total_alpha' => AttendanceRecord::today()->byStatus('alpha')->count(),
        ];
    });
}
```


## Deployment Notes

### WhatsApp Gateway Setup (Port 3001)

```bash
# 1. Copy SPMB's whatsapp-server to new folder
cp -r whatsapp-server whatsapp-server-absensi

# 2. Update port in whatsapp-server-absensi/server.js
const PORT = 3001;

# 3. Update webhook URL to Laravel attendance endpoint
const WEBHOOK_URL = 'http://localhost/api/attendance/webhook';

# 4. Start with PM2
cd whatsapp-server-absensi
pm2 start server.js --name "whatsapp-absensi" --watch

# 5. Save PM2 configuration
pm2 save
```

### Laravel Setup

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed default settings
php artisan db:seed --class=AttendanceSettingsSeeder

# 3. Create storage link (for student photos)
php artisan storage:link

# 4. Set up scheduled tasks (crontab)
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

### Environment Configuration

```env
# Add to .env
ATTENDANCE_WA_GATEWAY_URL=http://localhost:3001
ATTENDANCE_CHECKIN_TIME=07:00
ATTENDANCE_CHECKOUT_TIME=15:00
ATTENDANCE_TOLERANCE_MINUTES=15
ATTENDANCE_CUTOFF_TIME=09:00
```

## Future Enhancements (Out of MVP Scope)

### QR Code Integration

```
When implementing QR Code:
1. Add qr_tokens table (already in schema)
2. Generate QR with token per day or per student
3. Student scans QR → redirect to /attendance/qr-scan/{token}
4. Validate token + optionally check GPS
5. Process check-in via AttendanceService
```

### GPS/Location Validation

```
When implementing GPS:
1. Add lat/lng columns to attendance_records
2. Store school coordinates in settings
3. Calculate distance using Haversine formula
4. Flag suspicious attendance (far from school)
5. Admin can review and approve/reject
```

### Leave (Izin/Sakit) Management

```
When implementing leave:
1. Add attendance_leaves table
2. Allow student/parent to submit leave request
3. Upload supporting document (surat)
4. Admin approval workflow
5. Automatic status update to 'izin' or 'sakit'
```

### Reminder Scheduler

```
When implementing reminders:
1. Add scheduled jobs for 06:30, 07:15, 14:00, 15:00
2. Query students who haven't checked in/out
3. Bulk send WhatsApp reminders
4. Track reminder delivery status
```

## Implementation Priority

### Phase 1: Core Setup (Day 1-2)
- [x] Requirements document ✅
- [x] Design document ✅
- [ ] Database migrations
- [ ] Models with relationships
- [ ] Basic service layer

### Phase 2: WhatsApp Integration (Day 3-4)
- [ ] WhatsApp Gateway setup on port 3001
- [ ] Webhook controller
- [ ] Message processing logic
- [ ] Confirmation replies

### Phase 3: Business Logic (Day 4-5)
- [ ] Check-in/check-out processing
- [ ] Status determination
- [ ] Parent notifications
- [ ] Alpha auto-marking (scheduler)

### Phase 4: Admin Interface (Day 6-7)
- [ ] Dashboard (Livewire)
- [ ] Student CRUD + Excel import
- [ ] Class management
- [ ] Settings page

### Phase 5: Reports & Polish (Day 7-8)
- [ ] Excel export
- [ ] Report filters
- [ ] Dark mode styling
- [ ] Mobile responsive

### Phase 6: Testing & Deployment (Day 8-9)
- [ ] Unit tests
- [ ] Feature tests
- [ ] Deploy to aaPanel
- [ ] End-to-end testing

**Total Estimated Time: 8-9 days for MVP** 🎯

