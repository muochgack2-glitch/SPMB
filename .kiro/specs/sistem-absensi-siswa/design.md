# Design Document: Sistem Absensi Siswa

## Overview

Sistem Absensi Siswa adalah aplikasi berbasis QR Code Scanner dengan foto capture otomatis untuk mencatat kehadiran siswa dan memberikan notifikasi real-time kepada orang tua via WhatsApp. Sistem ini menggunakan Laravel 11 + Livewire untuk backend dan admin dashboard, serta JavaScript library untuk QR scanning dan webcam capture di sisi client. WhatsApp Gateway pada port 3001 digunakan khusus untuk mengirim notifikasi ke orang tua.

**Konsep Utama:**
- Siswa menunjukkan QR Code (kartu/HP) ke scanner
- Scanner otomatis capture foto siswa saat scan
- Sistem simpan data absensi + foto ke database
- Orang tua dapat notifikasi WhatsApp real-time
- Admin monitor dashboard dengan preview foto

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
│           - HTML5 QR Code Scanner (jsQR library)                 │
│           - Webcam API untuk photo capture                       │
│           - Display scan result + confirmation                   │
│           - Petugas interface dengan tombol REJECT               │
└──────────────────┬───────────────────────────────────────────────┘
                   │ HTTP POST /api/attendance/scan
                   │ (dengan foto base64)
                   ↓
┌──────────────────────────────────────────────────────────────────┐
│           Laravel 11 Application                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Controllers Layer                                      │    │
│  │  - AttendanceScanController (handle QR scan)            │    │
│  │  - AttendanceQRController (generate QR Code)            │    │
│  │  - AttendanceDashboardController                        │    │
│  │  - AttendanceStudentController                          │    │
│  │  - AttendanceClassController                            │    │
│  │  - AttendanceReportController                           │    │
│  │  - AttendanceSettingController                          │    │
│  └────────────────────────┬───────────────────────────────┘    │
│                           ↓                                      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Service Layer                                          │    │
│  │  - AttendanceService (core business logic)              │    │
│  │  - QRCodeService (generate QR Code)                     │    │
│  │  - PhotoCaptureService (save photos)                    │    │
│  │  - AttendanceWhatsAppService (send WA)                  │    │
│  │  - AttendanceNotificationService (format messages)      │    │
│  │  - AttendanceStatusService (determine status)           │    │
│  │  - AttendanceExportService (export to Excel)            │    │
│  └────────────────────────┬───────────────────────────────┘    │
│                           ↓                                      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Livewire Components (Real-time UI)                     │    │
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
│  │  - AttendanceLog                                        │    │
│  └────────────────────────┬───────────────────────────────┘    │
└───────────────────────────┼────────────────────────────────────┘
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│                    MySQL Database                                 │
│  - attendance_students (dengan qr_code_path)                     │
│  - attendance_classes                                            │
│  - attendance_records (dengan check_in_photo, check_out_photo)  │
│  - attendance_settings                                           │
│  - attendance_logs                                               │
└──────────────────────────────────────────────────────────────────┘
                           │
                           ↓
┌──────────────────────────────────────────────────────────────────┐
│                File Storage (Laravel Storage)                    │
│  storage/app/attendance/                                         │
│    ├── qrcodes/{NIS}.png                                         │
│    └── photos/{NIS}/{date}/                                      │
│         ├── checkin_{timestamp}.jpg                              │
│         └── checkout_{timestamp}.jpg                             │
└──────────────────────────────────────────────────────────────────┘
                           │
                           ↓ (async notification)
┌──────────────────────────────────────────────────────────────────┐
│           WhatsApp Gateway (Port 3001) - Notifikasi Only        │
│           - whatsapp-web.js                                      │
│           - PM2 Process Manager                                  │
│           - Hanya untuk KIRIM notifikasi, bukan terima pesan     │
└──────────────────────────────────────────────────────────────────┘
```

---

## Database Schema

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

### Table: attendance_students
```sql
CREATE TABLE attendance_students (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nis VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    no_hp_ortu VARCHAR(20),              -- Nomor HP orang tua untuk notifikasi
    qr_code_path VARCHAR(255),            -- Path to QR Code image file
    foto_profil VARCHAR(255),             -- Student profile photo
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nis (nis),
    INDEX idx_kelas (kelas_id),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (kelas_id) REFERENCES attendance_classes(id) ON DELETE RESTRICT
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
    check_in_photo VARCHAR(255),          -- Path to check-in photo
    check_out_photo VARCHAR(255),         -- Path to check-out photo
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
    group_name VARCHAR(50) NOT NULL,     -- 'time', 'tolerance', 'notification', 'general'
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
    action ENUM('qr_scan', 'check_in', 'check_out', 'notification', 'reject', 'error') NOT NULL,
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

### Data Relationships

```
attendance_classes (1) ----< (N) attendance_students
attendance_students (1) ----< (N) attendance_records
attendance_students (1) ----< (N) attendance_logs
```

---

## Service Layer Design

### AttendanceService
**Purpose:** Core business logic untuk proses absensi dari QR scan

```php
class AttendanceService
{
    public function processScan(string $nis, string $photoBase64, string $action = 'checkin'): array
    {
        // 1. Find student by NIS
        // 2. Validate: student exists and active
        // 3. Save photo using PhotoCaptureService
        // 4. If checkin:
        //    - Validate: belum absen hari ini
        //    - Validate: dalam waktu check-in window (05:00 - cutoff)
        //    - Determine status (hadir/terlambat) using AttendanceStatusService
        //    - Create AttendanceRecord with check_in_time, check_in_photo, status
        // 5. If checkout:
        //    - Validate: sudah check-in hari ini
        //    - Validate: belum check-out hari ini
        //    - Update AttendanceRecord with check_out_time, check_out_photo
        // 6. Log the action to attendance_logs
        // 7. Queue parent notification job (async)
        // 8. Return success response with student info, time, status, photo path
    }
    
    public function markAbsentStudents(): int
    {
        // Called by scheduler at cutoff time
        // 1. Get all active students who haven't checked in today
        // 2. Create attendance records with status 'alpha' and no photos
        // 3. Return count of marked students
    }
    
    public function getTodayAttendance(?int $classId = null): Collection
    {
        // Get today's attendance records with student, class, and photo data
        // Filter by class if provided
        // Order by class name, student name
    }
    
    public function getAttendanceStats(string $date): array
    {
        // Return: total_hadir, total_terlambat, total_alpha, total_belum
    }
    
    public function getAttendanceWithPhotos(int $recordId): AttendanceRecord
    {
        // Get attendance record with full photo URLs for display
    }
}
```

### QRCodeService
**Purpose:** Generate and manage QR Codes untuk siswa

```php
class QRCodeService
{
    public function generateQRCode(string $nis): string
    {
        // 1. Use library: SimpleSoftwareIO/simple-qrcode
        // 2. Generate QR Code containing NIS as plain text
        // 3. Save as PNG image: storage/app/attendance/qrcodes/{NIS}.png
        // 4. Return file path
    }
    
    public function regenerateQRCode(string $nis): string
    {
        // Delete old QR Code if exists
        // Generate new QR Code
        // Return new file path
    }
    
    public function getQRCodeUrl(string $nis): string
    {
        // Return public URL for QR Code image
        // E.g., /storage/attendance/qrcodes/{NIS}.png
    }
    
    public function generateBatchQRCodes(array $students): array
    {
        // Generate QR Codes for multiple students
        // Return array of [nis => file_path]
    }
}
```

### PhotoCaptureService
**Purpose:** Handle photo storage dan management

```php
class PhotoCaptureService
{
    public function savePhoto(string $base64Photo, string $nis, string $type = 'checkin'): string
    {
        // 1. Decode base64 photo
        // 2. Compress image (max 500KB, min 640x480)
        // 3. Generate filename: {type}_{timestamp}.jpg
        // 4. Create directory if not exists: storage/app/attendance/photos/{NIS}/{date}/
        // 5. Save file
        // 6. Return relative path: attendance/photos/{NIS}/{date}/{filename}
    }
    
    public function getPhotoUrl(string $path): string
    {
        // Convert storage path to public URL
        // Return full URL for photo display
    }
    
    public function deletePhoto(string $path): bool
    {
        // Delete photo file from storage
    }
    
    public function getPhotosForDate(string $nis, string $date): array
    {
        // Get all photos for a student on specific date
        // Return array of photo paths
    }
}
```

### AttendanceWhatsAppService
**Purpose:** Handle WhatsApp communication untuk notifikasi

```php
class AttendanceWhatsAppService
{
    protected $baseUrl = 'http://localhost:3001';  // Port 3001
    
    public function sendParentNotification(string $phone, string $message, ?string $photoPath = null): array
    {
        // Send notification to parent
        // If photoPath provided and setting enabled, send with media
        // POST {baseUrl}/api/send or {baseUrl}/api/send-media
        // Body: {phone, message, media_path (optional)}
        // Return: {success, messageId}
    }
    
    public function normalizePhone(string $phone): string
    {
        // Convert to international format: 628xxx
        // Handle various input formats: 08xxx, +628xxx, 628xxx
    }
    
    public function getGatewayStatus(): array
    {
        // GET {baseUrl}/api/status
        // Return: {connected, phone_number, battery, etc}
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
        $phone = $student->no_hp_ortu;
        $photoPath = $this->shouldIncludePhoto() ? $record->check_in_photo : null;
        
        $this->whatsappService->sendParentNotification($phone, $message, $photoPath);
    }
    
    public function notifyCheckOut(AttendanceStudent $student, AttendanceRecord $record): void
    {
        $message = $this->formatCheckOutMessage($student, $record);
        $phone = $student->no_hp_ortu;
        $photoPath = $this->shouldIncludePhoto() ? $record->check_out_photo : null;
        
        $this->whatsappService->sendParentNotification($phone, $message, $photoPath);
    }
    
    private function formatCheckInMessage($student, $record): string
    {
        $schoolName = AttendanceSetting::get('school_name', 'Sekolah');
        return "[ABSENSI {$schoolName}]\n" .
               "Ananda {$student->nama} telah tiba di sekolah pada " .
               $record->check_in_time->format('H:i') . " WIB.\n" .
               "Status: " . ucfirst($record->status) . "\n" .
               "Tanggal: " . $record->date->format('d/m/Y');
    }
    
    private function formatCheckOutMessage($student, $record): string
    {
        $schoolName = AttendanceSetting::get('school_name', 'Sekolah');
        return "[ABSENSI {$schoolName}]\n" .
               "Ananda {$student->nama} telah pulang dari sekolah pada " .
               $record->check_out_time->format('H:i') . " WIB.\n" .
               "Tanggal: " . $record->date->format('d/m/Y');
    }
    
    private function shouldIncludePhoto(): bool
    {
        return AttendanceSetting::get('include_photo_in_notification', 'false') === 'true';
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
    
    private function getSettings()
    {
        return (object) [
            'check_in_time' => AttendanceSetting::get('check_in_time', '07:00'),
            'tolerance_minutes' => AttendanceSetting::get('tolerance_minutes', 15),
            'cutoff_time' => AttendanceSetting::get('cutoff_time', '09:00'),
        ];
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
        // Use Laravel Excel package (maatwebsite/excel)
        // 1. Query attendance records with filters (date range, class, status)
        // 2. Include: Tanggal, NIS, Nama, Kelas, Jam Masuk, Jam Pulang, Status
        // 3. Format data into Excel rows
        // 4. Generate filename: Absensi_{StartDate}_to_{EndDate}.xlsx
        // 5. Return download response
    }
}
```

---

## Controllers & Routes

### AttendanceScanController
**Purpose:** Handle QR scan dan foto capture dari scanner interface

```php
// Route: POST /api/attendance/scan
class AttendanceScanController extends Controller
{
    public function scan(Request $request)
    {
        // 1. Validate request: nis, photo_base64, action (checkin/checkout)
        // 2. Call AttendanceService::processScan()
        // 3. Return JSON response: {success, message, student_info, time, status, photo_url}
        // 4. Handle errors gracefully with appropriate messages
    }
    
    public function reject(Request $request)
    {
        // Manual reject by petugas
        // 1. Validate request: nis, reason
        // 2. Log rejection to attendance_logs
        // 3. Return success message
    }
}
```

### AttendanceQRController
**Purpose:** Generate dan manage QR Codes

```php
// Routes:
// GET  /attendance/qr/{nis}           - Display QR Code for student
// GET  /attendance/qr/{nis}/download  - Download QR Code PNG
// POST /attendance/qr/regenerate/{nis} - Regenerate QR Code

class AttendanceQRController extends Controller
{
    public function show($nis)
    {
        // Display QR Code page (for students to show on mobile)
        // Large QR Code + student info
    }
    
    public function download($nis)
    {
        // Download QR Code as PNG file
    }
    
    public function regenerate($nis)
    {
        // Regenerate QR Code (admin only)
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
    public function store(Request $request)
    {
        // 1. Validate: nis unique, nama, kelas_id, no_hp_ortu
        // 2. Create student
        // 3. Generate QR Code using QRCodeService
        // 4. Save qr_code_path to student record
        // 5. Return success
    }
    
    public function importExcel(Request $request)
    {
        // 1. Validate Excel file
        // 2. Parse rows (NIS, Nama, Kelas, No HP Ortu)
        // 3. Create students in bulk
        // 4. Generate QR Codes for all
        // 5. Return success/error report
    }
}
```

### AttendanceClassController, AttendanceReportController, AttendanceSettingController
(sama seperti design lama, tidak ada perubahan signifikan)

---

## Route Registration

```php
// routes/web.php

Route::prefix('attendance')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AttendanceDashboardController::class, 'index'])
        ->name('attendance.dashboard');
    
    // QR Scanner Interface
    Route::get('/scanner', [AttendanceScanController::class, 'showScanner'])
        ->name('attendance.scanner');
    
    // Students
    Route::resource('students', AttendanceStudentController::class);
    Route::post('students/import', [AttendanceStudentController::class, 'importExcel'])
        ->name('attendance.students.import');
    
    // QR Code Management
    Route::get('qr/{nis}', [AttendanceQRController::class, 'show'])
        ->name('attendance.qr.show');
    Route::get('qr/{nis}/download', [AttendanceQRController::class, 'download'])
        ->name('attendance.qr.download');
    Route::post('qr/regenerate/{nis}', [AttendanceQRController::class, 'regenerate'])
        ->name('attendance.qr.regenerate');
    
    // Classes, Reports, Settings (sama seperti sebelumnya)
    Route::resource('classes', AttendanceClassController::class)->except(['show']);
    Route::get('reports', [AttendanceReportController::class, 'index'])->name('attendance.reports');
    Route::post('reports/export', [AttendanceReportController::class, 'export'])->name('attendance.reports.export');
    Route::get('settings', [AttendanceSettingController::class, 'index'])->name('attendance.settings');
    Route::post('settings', [AttendanceSettingController::class, 'update'])->name('attendance.settings.update');
});

// API Routes (no auth middleware)
Route::post('/api/attendance/scan', [AttendanceScanController::class, 'scan']);
Route::post('/api/attendance/reject', [AttendanceScanController::class, 'reject']);
```

---

## Livewire Components

### QRScannerInterface
**Purpose:** Real-time QR scanner interface untuk petugas

```php
// File: app/Livewire/QRScannerInterface.php
class QRScannerInterface extends Component
{
    public $scanResult = null;
    public $showResult = false;
    public $action = 'checkin'; // or 'checkout'
    
    protected $listeners = ['qrScanned' => 'handleScan'];
    
    public function handleScan($nis, $photoBase64)
    {
        // Called from JavaScript when QR is scanned
        // 1. Call AttendanceService::processScan()
        // 2. Set $scanResult with response data
        // 3. Set $showResult = true
        // 4. Auto-hide after 3 seconds
    }
    
    public function reject($nis)
    {
        // Manual reject by petugas
        // Log and display rejection message
    }
    
    public function render()
    {
        return view('livewire.qr-scanner-interface');
    }
}

// View will include:
// - Webcam video feed
// - jsQR library for QR detection
// - Photo capture on successful scan
// - Display scan result (student name, time, status, photo)
// - REJECT button
// - Audio feedback (beep on success/error)
```

### AttendanceDashboard
**Purpose:** Real-time dashboard dengan preview foto

```php
// File: app/Livewire/AttendanceDashboard.php
class AttendanceDashboard extends Component
{
    public $selectedClass = null;
    public $stats = [];
    public $students = [];
    public $selectedPhoto = null; // For lightbox
    
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
    
    public function viewPhoto($photoPath)
    {
        // Open photo in lightbox/modal
        $this->selectedPhoto = $photoPath;
    }
    
    public function render()
    {
        return view('livewire.attendance-dashboard');
    }
}

// View includes:
// - Stats cards (Hadir, Terlambat, Alpha, Belum)
// - Student table with photo thumbnails
// - Photo lightbox modal
// - Auto-refresh every 30 seconds
```

### AttendanceStudentTable, AttendanceReportGenerator
(sama seperti design lama dengan minor updates)

---

## QR Scan Flow (Check-In)

```
1. Siswa datang ke pos piket/scanner
   ↓
2. Siswa tunjukkan QR Code (kartu/HP) ke kamera scanner
   ↓
3. JavaScript QR Scanner Interface:
   - Webcam streaming di browser
   - jsQR library detect QR Code
   - Extract NIS from QR Code
   - Capture photo dari webcam (canvas.toDataURL())
   ↓
4. JavaScript sends AJAX POST to Laravel:
   POST /api/attendance/scan
   Body: {
     nis: "12345",
     photo_base64: "data:image/jpeg;base64,/9j/4AAQ...",
     action: "checkin"
   }
   ↓
5. AttendanceScanController::scan()
   - Validate input
   - Call AttendanceService::processScan()
   ↓
6. AttendanceService::processScan()
   ├─ Find student by NIS
   ├─ Validate: student exists and active
   │   NO → Return error "NIS tidak terdaftar"
   │   YES → Continue
   ├─ Validate: belum check-in hari ini
   │   ALREADY → Return error "Sudah absen masuk hari ini"
   │   NOT YET → Continue
   ├─ Validate: within check-in window (05:00 - cutoff)
   │   NO → Return error "Waktu absensi masuk telah berakhir"
   │   YES → Continue
   ├─ Save photo using PhotoCaptureService
   │   - Decode base64
   │   - Compress to max 500KB
   │   - Save to: storage/app/attendance/photos/{NIS}/{date}/checkin_{timestamp}.jpg
   │   - Return path: attendance/photos/{NIS}/{date}/checkin_{timestamp}.jpg
   ├─ Determine status using AttendanceStatusService
   │   ├─ Time <= 07:15 → status = 'hadir'
   │   └─ Time > 07:15 → status = 'terlambat'
   ├─ Create AttendanceRecord
   │   INSERT INTO attendance_records 
   │   (student_id, date, check_in_time, check_in_photo, status)
   ├─ Log action to attendance_logs
   │   INSERT INTO attendance_logs 
   │   (student_id, action='check_in', status='success')
   ├─ Queue parent notification (async via Laravel Queue)
   │   Job: SendParentNotification
   │   → AttendanceNotificationService::notifyCheckIn()
   │   → AttendanceWhatsAppService::sendParentNotification()
   │   → POST http://localhost:3001/api/send
   │   → Parent receives WhatsApp message
   └─ Return success response
   ↓
7. Return JSON to JavaScript:
   {
     "success": true,
     "message": "Absen masuk berhasil",
     "data": {
       "student_name": "Budi Santoso",
       "nis": "12345",
       "class": "12 RPL",
       "time": "07:10",
       "status": "Hadir",
       "photo_url": "/storage/attendance/photos/12345/2024-01-15/checkin_071000.jpg"
     }
   }
   ↓
8. JavaScript displays result on scanner screen:
   - Student photo (captured)
   - Student name & class
   - Time & status with color coding
   - Play success beep sound
   - Auto-hide after 3 seconds
   
TOTAL TIME: < 2 seconds
```

---

## QR Code Generation

### Library
- **SimpleSoftwareIO/simple-qrcode** - Laravel package for QR generation

### Format
- QR Code berisi: NIS siswa (plain text)
- Size: 300x300 pixels
- Format: PNG
- Error correction: High (30%)

### Storage
```
storage/app/attendance/qrcodes/
  ├── 12345.png
  ├── 12346.png
  └── ...
```

### Access
- Public URL: `/storage/attendance/qrcodes/{NIS}.png`
- Symlink: `php artisan storage:link`

---

## Photo Storage

### Directory Structure
```
storage/app/attendance/photos/
  ├── 12345/                    (NIS)
  │   ├── 2024-01-15/
  │   │   ├── checkin_071000.jpg
  │   │   └── checkout_153000.jpg
  │   └── 2024-01-16/
  │       ├── checkin_070500.jpg
  │       └── checkout_152000.jpg
  └── 12346/
      └── ...
```

### Specifications
- Format: JPEG
- Max size: 500KB (compressed)
- Min resolution: 640x480
- Compression quality: 85%

### Access
- Public URL: `/storage/attendance/photos/{NIS}/{date}/{filename}`
- Symlink: `php artisan storage:link`

---

## Frontend Technologies

### QR Scanner
- **jsQR** - Pure JavaScript QR Code reader
- Usage: Decode QR from video stream

### Webcam Access
- **HTML5 getUserMedia API**
- Capture video stream from webcam
- Canvas API for photo capture

### JavaScript Flow
```javascript
// 1. Initialize webcam
navigator.mediaDevices.getUserMedia({ video: true })
  .then(stream => {
    videoElement.srcObject = stream;
  });

// 2. Continuous QR scanning
function scanQR() {
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0);
  
  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
  const code = jsQR(imageData.data, imageData.width, imageData.height);
  
  if (code) {
    handleQRDetected(code.data); // NIS
  }
  
  requestAnimationFrame(scanQR);
}

// 3. Capture photo when QR detected
function handleQRDetected(nis) {
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0);
  
  const photoBase64 = canvas.toDataURL('image/jpeg', 0.85);
  
  sendToServer(nis, photoBase64);
}

// 4. Send to Laravel
function sendToServer(nis, photoBase64) {
  fetch('/api/attendance/scan', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nis: nis,
      photo_base64: photoBase64,
      action: 'checkin'
    })
  })
  .then(response => response.json())
  .then(data => displayResult(data));
}
```

---

## Scheduled Tasks

### Daily Alpha Marking

```php
// File: app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Mark absent students as Alpha at cutoff time
    $cutoffTime = AttendanceSetting::get('cutoff_time', '09:00');
    
    $schedule->call(function () {
        $service = app(AttendanceService::class);
        $count = $service->markAbsentStudents();
        
        Log::info("Auto-marked {$count} students as Alpha");
    })->dailyAt($cutoffTime);
    
    // Optional: Daily summary at 4pm
    $schedule->call(function () {
        $service = app(AttendanceService::class);
        $stats = $service->getAttendanceStats(today());
        
        // Send summary to admin via WhatsApp
    })->dailyAt('16:00');
}
```

---

## Configuration

### Environment Variables

```env
# .env file additions

# Attendance WhatsApp Gateway (notifikasi only)
ATTENDANCE_WA_GATEWAY_URL=http://localhost:3001

# Attendance Time Settings (defaults, can be changed in UI)
ATTENDANCE_CHECKIN_TIME=07:00
ATTENDANCE_CHECKOUT_TIME=15:00
ATTENDANCE_TOLERANCE_MINUTES=15
ATTENDANCE_CUTOFF_TIME=09:00

# Notification Settings
ATTENDANCE_ENABLE_PARENT_NOTIFICATION=true
ATTENDANCE_INCLUDE_PHOTO_IN_NOTIFICATION=false

# School Info
ATTENDANCE_SCHOOL_NAME="SMK PGRI BLORA"

# Storage
FILESYSTEM_DISK=local
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
        'include_photo' => env('ATTENDANCE_INCLUDE_PHOTO_IN_NOTIFICATION', false),
    ],
    
    'school' => [
        'name' => env('ATTENDANCE_SCHOOL_NAME', 'SMK PGRI BLORA'),
    ],
    
    'storage' => [
        'qr_path' => 'attendance/qrcodes',
        'photo_path' => 'attendance/photos',
    ],
];
```

---

## Laravel Models

### AttendanceStudent Model

```php
namespace App\Models;

class AttendanceStudent extends Model
{
    protected $fillable = [
        'nis', 'nama', 'kelas_id', 'no_hp_ortu', 
        'qr_code_path', 'foto_profil', 'is_active'
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
    
    // Accessors
    public function getQrCodeUrlAttribute()
    {
        return $this->qr_code_path 
            ? Storage::url($this->qr_code_path)
            : null;
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

### AttendanceRecord Model

```php
namespace App\Models;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'student_id', 'date', 'check_in_time', 'check_out_time',
        'check_in_photo', 'check_out_photo', 'status', 'notes'
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
    
    // Accessors
    public function getCheckInPhotoUrlAttribute()
    {
        return $this->check_in_photo 
            ? Storage::url($this->check_in_photo)
            : null;
    }
    
    public function getCheckOutPhotoUrlAttribute()
    {
        return $this->check_out_photo 
            ? Storage::url($this->check_out_photo)
            : null;
    }
    
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

---

## Security Considerations

1. **Photo Storage**
   - Store in private storage (storage/app), not public
   - Serve via Laravel route with authentication check
   - OR use symlink with proper permissions

2. **QR Code Validation**
   - Validate NIS exists in database
   - Check student is active
   - Prevent SQL injection (use parameterized queries)

3. **API Endpoints**
   - Rate limiting on /api/attendance/scan
   - CSRF protection for web routes
   - Validate photo base64 format and size

4. **Photo File Security**
   - Validate image format (JPEG/PNG only)
   - Limit file size (max 5MB upload, compress to 500KB)
   - Sanitize filenames
   - Prevent directory traversal

---

## Performance Optimization

1. **Database Indexes**
   - Already defined in schema (see above)

2. **Photo Compression**
   - Compress on upload (85% quality)
   - Lazy load thumbnails in dashboard

3. **Caching**
   - Cache attendance settings
   - Cache QR Code URLs

4. **Queued Jobs**
   - Parent notifications via queue
   - Batch QR Code generation via queue

---

## Deployment Notes

1. **Storage Symlink**
   ```bash
   php artisan storage:link
   ```

2. **Directories Permission**
   ```bash
   chmod -R 775 storage/app/attendance
   chown -R www-data:www-data storage/app/attendance
   ```

3. **WhatsApp Gateway**
   - Run on separate port (3001)
   - PM2 process manager for auto-restart
   - Separate from SPMB gateway (port 3000)

4. **Cron Job for Scheduler**
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

5. **Dependencies**
   ```bash
   composer require simplesoftwareio/simple-qrcode
   composer require maatwebsite/excel
   npm install jsqr
   ```

---

## Future Enhancements

1. **Face Recognition**
   - Auto-validate captured photo vs profile photo
   - Alert if confidence score low

2. **Multi-Scanner Support**
   - Multiple scanner stations
   - Load balancing

3. **Offline Mode**
   - PWA dengan service worker
   - Sync data when back online

4. **Analytics Dashboard**
   - Attendance trends
   - Per-student attendance rate
   - Class comparison

5. **Mobile App**
   - Native Android/iOS app
   - Better camera integration

---

**End of Design Document**
