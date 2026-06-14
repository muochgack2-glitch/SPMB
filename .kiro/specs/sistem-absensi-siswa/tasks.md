# Task Breakdown: Sistem Absensi Siswa (QR Code Scanner)

## Overview

Total estimated time: **55-65 hours** (7-8 working days)
Total tasks: **17 main tasks** with **180+ subtasks**

**Sistem:** QR Code Scanner dengan Foto Capture otomatis

## Task Dependencies

```
Task 1 (Database) ─┬─→ Task 2 (Models)
                   ├─→ Task 3 (Migrations)
                   └─→ Task 4 (Seeders)
                        ↓
Task 5 (Services) ─────→ Task 6 (Controllers)
                        ↓
Task 7 (Routes) ───────→ Task 8 (Views/Livewire)
                        ↓
Task 9 (QR Code Generation) ─→ Task 10 (Photo Storage)
                        ↓
Task 11 (QR Scanner Interface) ─→ Task 12 (Scan API)
                        ↓
Task 13 (Dashboard) ────→ Task 14 (Student Management)
                        ↓
Task 15 (Reports) ──────→ Task 16 (Settings)
                        ↓
Task 17 (WhatsApp Notification) ─→ Task 18 (Testing & Polish)
```

---

## Task 1: Setup Database Schema

**Priority:** Critical
**Estimated Time:** 4 hours
**Dependencies:** None
**Status:** pending

Create complete database structure for attendance system with QR and photo support.

### Subtasks:

- [x] 1.1: Create migration for `attendance_classes` table
  - Fields: id, nama_kelas, tingkat, jurusan, wali_kelas_id, is_active, timestamps
  - Add indexes: idx_tingkat, idx_active
  - Add unique constraint on (nama_kelas, tingkat)

- [x] 1.2: Create migration for `attendance_students` table
  - Fields: id, nis, nama, kelas_id, no_hp_ortu, qr_code_path, foto_profil, is_active, timestamps
  - Add indexes: idx_nis, idx_kelas, idx_active
  - Add unique constraint on nis
  - Add foreign key to attendance_classes
  - Note: no_hp_siswa dihapus, hanya no_hp_ortu

- [x] 1.3: Create migration for `attendance_records` table
  - Fields: id, student_id, date, check_in_time, check_out_time, check_in_photo, check_out_photo, status (enum), notes, timestamps
  - Add indexes: idx_date, idx_status, idx_student_date
  - Add unique constraint on (student_id, date)
  - Add foreign key to attendance_students with CASCADE delete

- [x] 1.4: Create migration for `attendance_settings` table
  - Fields: id, key, value, group_name, description, timestamps
  - Add indexes: idx_group, idx_key
  - Add unique constraint on key

- [x] 1.5: Create migration for `attendance_logs` table
  - Fields: id, student_id, action (enum: qr_scan, check_in, check_out, notification, reject, error), message, response, status (enum), created_at
  - Add indexes: idx_student, idx_action, idx_date
  - Add foreign key to attendance_students with SET NULL

- [x] 1.6: Test all migrations run successfully
- [x] 1.7: Test rollback functionality

---

## Task 2: Create Eloquent Models

**Priority:** Critical
**Estimated Time:** 3 hours
**Dependencies:** Task 1
**Status:** pending

Create Laravel models with relationships and helper methods for QR + Photo system.

### Subtasks:

- [x] 2.1: Create `AttendanceClass` model
  - Define fillable fields
  - Add relationship: hasMany students
  - Add scope: active()
  - Add helper: getStudentCount()

- [ ] 2.2: Create `AttendanceStudent` model
  - Define fillable fields: nis, nama, kelas_id, no_hp_ortu, qr_code_path, foto_profil, is_active
  - Add cast: is_active to boolean
  - Add relationship: belongsTo kelas
  - Add relationship: hasMany attendanceRecords
  - Add relationship: hasMany logs
  - Add accessor: getQrCodeUrlAttribute() - return Storage::url()
  - Add helper: getTodayAttendance()
  - Add helper: hasCheckedInToday()
  - Add helper: hasCheckedOutToday()

- [~] 2.3: Create `AttendanceRecord` model
  - Define fillable fields including check_in_photo, check_out_photo
  - Add casts: date, check_in_time, check_out_time
  - Add relationship: belongsTo student
  - Add scope: today()
  - Add scope: byStatus()
  - Add scope: byClass()
  - Add accessor: getCheckInPhotoUrlAttribute()
  - Add accessor: getCheckOutPhotoUrlAttribute()
  - Add accessor: getStatusLabelAttribute()

- [~] 2.4: Create `AttendanceSetting` model
  - Define fillable fields
  - Add static method: get($key, $default)
  - Add static method: set($key, $value)

- [~] 2.5: Create `AttendanceLog` model
  - Define fillable fields
  - Disable updated_at timestamp
  - Add relationship: belongsTo student

- [~] 2.6: Test all model relationships work correctly

---

## Task 3: Create Database Seeders

**Priority:** Medium
**Estimated Time:** 2 hours
**Dependencies:** Task 1, Task 2
**Status:** pending

Create sample data for testing including QR codes and photos.

### Subtasks:

- [~] 3.1: Create AttendanceSettingsSeeder
  - Insert default settings: check_in_time (07:00), check_out_time (15:00)
  - Insert tolerance_minutes (15), cutoff_time (09:00)
  - Insert enable_parent_notification (true)
  - Insert include_photo_in_notification (false)
  - Insert school_name

- [~] 3.2: Create AttendanceClassSeeder
  - Create sample classes: 10 RPL, 11 RPL, 12 RPL
  - Create sample classes: 10 TKJ, 11 TKJ, 12 TKJ

- [~] 3.3: Create AttendanceStudentSeeder
  - Create 30-50 sample students across different classes
  - Use realistic Indonesian names
  - Generate valid phone numbers (628xxx format) for orang tua
  - Leave qr_code_path empty (will be generated in Task 9)

- [~] 3.4: Register seeders in DatabaseSeeder
- [~] 3.5: Test seeders run without errors

---

## Task 4: Install Required Dependencies

**Priority:** Critical
**Estimated Time:** 1 hour
**Dependencies:** None
**Status:** pending

### Subtasks:

- [~] 4.1: Install QR Code library
  - Run: `composer require simplesoftwareio/simple-qrcode`
- [~] 4.2: Install Laravel Excel
  - Run: `composer require maatwebsite/excel`
- [~] 4.3: Install Livewire (if not installed)
  - Run: `composer require livewire/livewire`
- [~] 4.4: Setup storage symlink
  - Run: `php artisan storage:link`
- [~] 4.5: Test all dependencies load correctly

---

## Task 5: Create Service Layer - Core Services

**Priority:** Critical
**Estimated Time:** 8 hours
**Dependencies:** Task 2, Task 4
**Status:** pending

### Subtasks:

- [~] 5.1: Create `QRCodeService` class
  - Implement generateQRCode($nis): string - save to storage/app/attendance/qrcodes/{NIS}.png
  - Implement regenerateQRCode($nis): string
  - Implement getQRCodeUrl($nis): string
  - Implement generateBatchQRCodes(array $students): array
  - Use SimpleSoftwareIO/simple-qrcode library
  - QR size: 300x300, error correction: high

- [~] 5.2: Create `PhotoCaptureService` class
  - Implement savePhoto($base64, $nis, $type): string
  - Decode base64, compress to max 500KB (quality 85%)
  - Save to: storage/app/attendance/photos/{NIS}/{date}/{type}_{timestamp}.jpg
  - Implement getPhotoUrl($path): string
  - Implement deletePhoto($path): bool

- [~] 5.3: Create `AttendanceService` class
  - Implement processScan($nis, $photoBase64, $action): array
    * Find student by NIS
    * Validate student exists, active, not duplicate today
    * Validate time window
    * Save photo via PhotoCaptureService
    * Determine status via AttendanceStatusService
    * Create/update AttendanceRecord
    * Log action
    * Queue parent notification
    * Return success response
  - Implement markAbsentStudents(): int
  - Implement getTodayAttendance(?$classId): Collection
  - Implement getAttendanceStats($date): array

- [~] 5.4: Create `AttendanceStatusService` class
  - Implement determineStatus($checkInTime): string
  - Implement isWithinCheckInWindow($time): bool

- [~] 5.5: Create `AttendanceWhatsAppService` class
  - Base URL: http://localhost:3001
  - Implement sendParentNotification($phone, $message, ?$photoPath): array
  - Implement normalizePhone($phone): string
  - Implement getGatewayStatus(): array

- [~] 5.6: Create `AttendanceNotificationService` class
  - Implement notifyCheckIn($student, $record): void
  - Implement notifyCheckOut($student, $record): void
  - Format messages with school name, time, status
  - Check setting for include_photo_in_notification

- [~] 5.7: Create `AttendanceExportService` class
  - Implement exportToExcel($filters): string
  - Use Laravel Excel
  - Columns: Tanggal, NIS, Nama, Kelas, Jam Masuk, Jam Pulang, Status

- [~] 5.8: Register all services in service provider
- [~] 5.9: Test all service methods with unit tests

---

## Task 6: Create Controllers

**Priority:** Critical
**Estimated Time:** 5 hours
**Dependencies:** Task 5
**Status:** pending

### Subtasks:

- [~] 6.1: Create `AttendanceScanController`
  - Method: scan(Request) - handle POST /api/attendance/scan
  - Validate: nis, photo_base64, action
  - Call AttendanceService::processScan()
  - Return JSON: {success, message, data}
  - Method: reject(Request) - manual reject by petugas
  - Method: showScanner() - display scanner interface page

- [~] 6.2: Create `AttendanceQRController`
  - Method: show($nis) - display QR Code page for student
  - Method: download($nis) - download QR PNG
  - Method: regenerate($nis) - regenerate QR (admin only)

- [~] 6.3: Create `AttendanceStudentController` (Resource)
  - Method: store() - create student + generate QR Code
  - Method: importExcel() - bulk import + generate QR Codes
  - Other CRUD methods

- [~] 6.4: Create AttendanceDashboardController, AttendanceClassController, AttendanceReportController, AttendanceSettingController
  - Standard CRUD operations

- [~] 6.5: Add request validation classes for all controllers

---

## Task 7: Create Routes

**Priority:** Critical
**Estimated Time:** 1 hour
**Dependencies:** Task 6
**Status:** pending

### Subtasks:

- [~] 7.1: Add routes in routes/web.php
  - Dashboard, Scanner Interface, Students (CRUD + import), QR Management, Classes, Reports, Settings
  - All with 'auth' middleware

- [~] 7.2: Add API routes in routes/api.php
  - POST /api/attendance/scan (no auth)
  - POST /api/attendance/reject (no auth)

- [~] 7.3: Test all routes registered: `php artisan route:list | grep attendance`

---

## Task 8: Create QR Scanner Interface (Livewire + JavaScript)

**Priority:** Critical
**Estimated Time:** 6 hours
**Dependencies:** Task 7
**Status:** pending

### Subtasks:

- [~] 8.1: Create Livewire component: `QRScannerInterface`
  - Properties: scanResult, showResult, action
  - Listener: qrScanned event
  - Method: handleScan($nis, $photoBase64)
  - Method: reject($nis)

- [~] 8.2: Create blade view: `livewire.qr-scanner-interface.blade.php`
  - Video element for webcam stream
  - Canvas for QR detection
  - Result display card (student info, photo, status)
  - REJECT button
  - Action selector (Check In / Check Out)

- [~] 8.3: Add JavaScript for QR scanning
  - Include jsQR library (via CDN or npm)
  - Initialize webcam via getUserMedia API
  - Continuous QR scanning loop with requestAnimationFrame
  - Capture photo when QR detected
  - Convert to base64: canvas.toDataURL('image/jpeg', 0.85)
  - Send AJAX POST to /api/attendance/scan

- [~] 8.4: Add audio feedback
  - Success beep on successful scan
  - Error beep on validation failure
  - Use HTML5 Audio API

- [~] 8.5: Add auto-hide result after 3 seconds
- [~] 8.6: Test on Chrome and Edge with webcam

---

## Task 9: Implement QR Code Generation

**Priority:** Critical
**Estimated Time:** 3 hours
**Dependencies:** Task 5, Task 6
**Status:** pending

### Subtasks:

- [~] 9.1: Create QR Code generation on student create/update
  - In AttendanceStudentController::store()
  - Call QRCodeService::generateQRCode($nis)
  - Save qr_code_path to student record

- [~] 9.2: Create batch QR generation command
  - php artisan make:command GenerateQRCodes
  - Signature: attendance:generate-qr
  - Loop through students without QR, generate for all
  - Display progress bar

- [~] 9.3: Create QR display page for students
  - Route: /attendance/qr/{nis}
  - Large QR Code display
  - Student name and class
  - "Show this QR to scanner" instruction
  - Mobile-friendly layout

- [~] 9.4: Create QR download functionality
  - Route: /attendance/qr/{nis}/download
  - Download QR as PNG file
  - Filename: QR_{NIS}_{Nama}.png

- [~] 9.5: Test QR generation and scanning
  - Generate QR for test student
  - Scan with QR scanner interface
  - Verify NIS extracted correctly

---

## Task 10: Implement Photo Storage System

**Priority:** Critical
**Estimated Time:** 2 hours
**Dependencies:** Task 5
**Status:** pending

### Subtasks:

- [~] 10.1: Create storage directories
  - storage/app/attendance/qrcodes/
  - storage/app/attendance/photos/

- [~] 10.2: Set proper permissions
  - chmod -R 775 storage/app/attendance

- [~] 10.3: Test photo save and retrieve
  - Save test base64 photo
  - Verify file created with correct path
  - Verify compression works (< 500KB)
  - Retrieve photo URL

- [~] 10.4: Test photo display in dashboard
  - Create test attendance record with photo
  - Display thumbnail in dashboard
  - Click to view full-size in modal

---

## Task 11: Create Dashboard with Photo Preview

**Priority:** High
**Estimated Time:** 5 hours
**Dependencies:** Task 8, Task 10
**Status:** pending

### Subtasks:

- [~] 11.1: Create Livewire component: `AttendanceDashboard`
  - Properties: selectedClass, stats, students, selectedPhoto
  - Method: loadData() - get stats and attendance list
  - Method: viewPhoto($photoPath) - open lightbox
  - Auto-refresh every 30 seconds: wire:poll.30s

- [~] 11.2: Create dashboard view
  - Stats cards: Hadir (green), Terlambat (yellow), Alpha (red), Belum (gray)
  - Class filter dropdown
  - Student table: Foto (thumbnail), Nama, Kelas, Jam Masuk, Jam Pulang, Status
  - Photo lightbox modal

- [~] 11.3: Add CSS for photo thumbnails
  - Thumbnail size: 50x50px
  - Rounded corners
  - Hover effect
  - Click to enlarge

- [~] 11.4: Test dashboard real-time updates
- [~] 11.5: Test photo lightbox functionality

---

## Task 12: Create Student Management with QR

**Priority:** High
**Estimated Time:** 5 hours
**Dependencies:** Task 9
**Status:** pending

### Subtasks:

- [~] 12.1: Create student form view
  - Fields: NIS, Nama, Kelas (dropdown), No HP Orang Tua, Foto Profil (upload)
  - Validation rules
  - Auto-generate QR on save

- [~] 12.2: Create Livewire component: `AttendanceStudentTable`
  - Search by nama or nis
  - Filter by class
  - Display QR Code column (thumbnail)
  - Actions: Edit, Delete, View QR, Download QR

- [~] 12.3: Create Excel import with QR generation
  - Template: NIS, Nama, Kelas, No HP Ortu
  - Validate all rows
  - Import students
  - Generate QR Codes for all (queue job)
  - Show progress and results

- [~] 12.4: Create Excel template file
  - Save to: storage/app/templates/template-siswa-absensi.xlsx

- [~] 12.5: Test CRUD operations
- [~] 12.6: Test Excel import with 50+ students

---

## Task 13: Create Reports with Photos

**Priority:** High
**Estimated Time:** 4 hours
**Dependencies:** Task 11
**Status:** pending

### Subtasks:

- [~] 13.1: Create Livewire component: `AttendanceReportGenerator`
  - Date range filter
  - Class filter
  - Status filter
  - Preview table with photo thumbnails
  - Export button

- [~] 13.2: Implement Excel export
  - Include all data + note about photos (stored separately)
  - Or optionally embed photos in Excel (advanced)

- [~] 13.3: Test export with various filters
- [~] 13.4: Test with large datasets (1000+ records)

---

## Task 14: Create Settings Page

**Priority:** Medium
**Estimated Time:** 2 hours
**Dependencies:** Task 6
**Status:** pending

### Subtasks:

- [~] 14.1: Create settings form view
  - Time settings section
  - Notification settings section (with include_photo_in_notification toggle)
  - School info section

- [~] 14.2: Implement validation
  - cutoff_time > (check_in_time + tolerance)

- [~] 14.3: Test settings update and apply to attendance logic

---

## Task 15: Setup WhatsApp Gateway for Notifications

**Priority:** High
**Estimated Time:** 3 hours
**Dependencies:** Task 5
**Status:** pending

### Subtasks:

- [~] 15.1: Create WhatsApp Gateway directory: `absensi/whatsapp-gateway`
- [~] 15.2: Initialize Node.js project
  - npm init -y
  - npm install whatsapp-web.js express body-parser

- [~] 15.3: Create server.js
  - Initialize Express on port 3001
  - Initialize WhatsApp client
  - POST /api/send - send text message
  - POST /api/send-media - send message with photo
  - GET /api/status - check connection status

- [~] 15.4: Create PM2 ecosystem file
  - Name: "attendance-whatsapp-gateway"
  - Script: server.js
  - Port: 3001

- [~] 15.5: Test WhatsApp authentication (scan QR)
- [~] 15.6: Test sending notification to test number
- [~] 15.7: Test sending notification with photo

---

## Task 16: Setup Scheduled Jobs

**Priority:** High
**Estimated Time:** 2 hours
**Dependencies:** Task 5
**Status:** pending

### Subtasks:

- [~] 16.1: Create command: MarkAbsentStudents
  - php artisan make:command MarkAbsentStudents
  - Signature: attendance:mark-absent
  - Call AttendanceService::markAbsentStudents()

- [~] 16.2: Register in Kernel.php schedule
  - Run daily at cutoff_time (from settings)

- [~] 16.3: Test command manually
- [~] 16.4: Setup cron job on server
  - * * * * * cd /path && php artisan schedule:run

---

## Task 17: End-to-End Testing & Polish

**Priority:** Critical
**Estimated Time:** 8 hours
**Dependencies:** All previous tasks
**Status:** pending

### Subtasks:

- [~] 17.1: Test complete check-in flow
  - Generate QR for test student
  - Scan QR in scanner interface
  - Verify photo captured
  - Verify attendance recorded
  - Verify notification sent to parent

- [~] 17.2: Test complete check-out flow
- [~] 17.3: Test all error scenarios
  - Invalid QR
  - Already checked in
  - Outside time window
  - No parent phone number

- [~] 17.4: Test petugas REJECT functionality
- [~] 17.5: Test dashboard photo preview and lightbox
- [~] 17.6: Test auto alpha marking (scheduled job)
- [~] 17.7: Test with multiple students rapidly (performance)
- [~] 17.8: Test on different browsers (Chrome, Edge)
- [~] 17.9: Test webcam on different devices
- [~] 17.10: Dark mode compatibility check
- [~] 17.11: Mobile responsiveness check
- [~] 17.12: Security review (photo access, API rate limiting)
- [~] 17.13: Code cleanup and documentation
- [~] 17.14: Create README.md with setup instructions
- [~] 17.15: Create user manual (PDF) for petugas and students

---

## Critical Path (Must complete in order):

1. Task 1: Database (4h)
2. Task 2: Models (3h)
3. Task 4: Dependencies (1h)
4. Task 5: Services (8h)
5. Task 6: Controllers (5h)
6. Task 7: Routes (1h)
7. Task 9: QR Generation (3h)
8. Task 10: Photo Storage (2h)
9. Task 8: Scanner Interface (6h)
10. Task 11: Dashboard (5h)
11. Task 15: WhatsApp Gateway (3h)
12. Task 16: Scheduled Jobs (2h)
13. Task 17: Testing (8h)

**Total Critical Path: ~51 hours**

## Can be done in parallel:
- Task 3: Seeders (while building other features)
- Task 12: Student Management (after Task 9)
- Task 13: Reports (after Task 11)
- Task 14: Settings (anytime after Task 6)

---

## MVP Checklist (Minimum to launch):

- ✅ Task 1-7: Core infrastructure
- ✅ Task 8: Scanner Interface (critical!)
- ✅ Task 9: QR Generation
- ✅ Task 10: Photo Storage
- ✅ Task 11: Dashboard (basic version)
- ✅ Task 12: Student Management (at least create/list)
- ✅ Task 15: WhatsApp Notifications
- ✅ Task 16: Auto Alpha marking
- ✅ Task 17: Basic testing

**MVP Time: ~45 hours (6 working days)**

---

## Key Differences from WhatsApp-based System:

1. **No incoming WhatsApp messages** - Gateway hanya untuk notifikasi keluar
2. **QR Code generation** - Setiap siswa punya QR Code unik
3. **Photo capture** - Webcam capture otomatis saat scan
4. **Scanner interface** - Web-based dengan jsQR library
5. **Photo storage** - File storage dengan thumbnail preview
6. **Manual verification** - Petugas bisa reject scan yang mencurigakan

---

## Technical Notes:

- **Browser compatibility:** Chrome, Edge (perlu webcam access)
- **Security:** Photo stored in private storage, accessed via Laravel route
- **Performance:** Photo compression critical untuk storage efficiency
- **Scalability:** Index pada database untuk query cepat
- **Reliability:** PM2 untuk auto-restart WhatsApp Gateway

---

**End of Task Breakdown**
