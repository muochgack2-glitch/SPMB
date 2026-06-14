# Task Breakdown: Sistem Absensi Siswa

## Overview

Total estimated time: **60-70 hours** (8-9 working days)
Total tasks: **16 main tasks** with **195+ subtasks**

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
Task 9 (WhatsApp Gateway) ─→ Task 10 (Webhook Integration)
                        ↓
Task 11 (Dashboard) ────→ Task 12 (Student Management)
                        ↓
Task 13 (Reports) ──────→ Task 14 (Settings)
                        ↓
Task 15 (Scheduled Jobs) ─→ Task 16 (Testing & Polish)
```

---

## Task 1: Setup Database Schema

**Priority:** Critical
**Estimated Time:** 4 hours
**Dependencies:** None
**Status:** pending

Create complete database structure for attendance system.

### Subtasks:

- [ ] 1.1: Create migration for `attendance_classes` table
  - Fields: id, nama_kelas, tingkat, jurusan, wali_kelas_id, is_active, timestamps
  - Add indexes: idx_tingkat, idx_active
  - Add unique constraint on (nama_kelas, tingkat)

- [ ] 1.2: Create migration for `attendance_students` table
  - Fields: id, nis, nama, kelas_id, no_hp_siswa, no_hp_ortu, foto, is_active, timestamps
  - Add indexes: idx_nis, idx_kelas, idx_no_hp_siswa, idx_active
  - Add unique constraint on nis
  - Add foreign key to attendance_classes

- [ ] 1.3: Create migration for `attendance_records` table
  - Fields: id, student_id, date, check_in_time, check_out_time, status (enum), notes, timestamps
  - Add indexes: idx_date, idx_status, idx_student_date
  - Add unique constraint on (student_id, date)
  - Add foreign key to attendance_students with CASCADE delete

- [ ] 1.4: Create migration for `attendance_settings` table
  - Fields: id, key, value, group_name, description, timestamps
  - Add indexes: idx_group, idx_key
  - Add unique constraint on key

- [ ] 1.5: Create migration for `attendance_logs` table
  - Fields: id, student_id, phone, action (enum), message, response, status (enum), created_at
  - Add indexes: idx_student, idx_phone, idx_action, idx_date
  - Add foreign key to attendance_students with SET NULL

- [ ] 1.6: Test all migrations run successfully
- [ ] 1.7: Test rollback functionality

---

## Task 2: Create Eloquent Models

**Priority:** Critical
**Estimated Time:** 3 hours
**Dependencies:** Task 1
**Status:** pending

Create Laravel models with relationships and helper methods.

### Subtasks:

- [ ] 2.1: Create `AttendanceClass` model
  - Define fillable fields
  - Add relationship: hasMany students
  - Add scope: active()
  - Add helper: getStudentCount()

- [ ] 2.2: Create `AttendanceStudent` model
  - Define fillable fields
  - Add cast: is_active to boolean
  - Add relationship: belongsTo kelas
  - Add relationship: hasMany attendanceRecords
  - Add relationship: hasMany logs
  - Add helper: getTodayAttendance()
  - Add helper: hasCheckedInToday()
  - Add helper: hasCheckedOutToday()

- [ ] 2.3: Create `AttendanceRecord` model
  - Define fillable fields
  - Add casts: date, check_in_time, check_out_time
  - Add relationship: belongsTo student
  - Add scope: today()
  - Add scope: byStatus()
  - Add scope: byClass()
  - Add accessor: getStatusLabelAttribute()

- [ ] 2.4: Create `AttendanceSetting` model
  - Define fillable fields
  - Add static method: get($key, $default)
  - Add static method: set($key, $value)

- [ ] 2.5: Create `AttendanceLog` model
  - Define fillable fields
  - Disable updated_at timestamp
  - Add relationship: belongsTo student

- [ ] 2.6: Test all model relationships work correctly

---

## Task 3: Create Database Seeders

**Priority:** Medium
**Estimated Time:** 2 hours
**Dependencies:** Task 1, Task 2
**Status:** pending

Create sample data for testing and demo purposes.

### Subtasks:

- [ ] 3.1: Create AttendanceSettingsSeeder
  - Insert default settings: check_in_time (07:00), check_out_time (15:00)
  - Insert tolerance_minutes (15), cutoff_time (09:00)
  - Insert enable_parent_notification (true), school_name

- [ ] 3.2: Create AttendanceClassSeeder
  - Create sample classes: 10 RPL, 11 RPL, 12 RPL
  - Create sample classes: 10 TKJ, 11 TKJ, 12 TKJ

- [ ] 3.3: Create AttendanceStudentSeeder
  - Create 30-50 sample students across different classes
  - Use realistic Indonesian names
  - Generate valid phone numbers (628xxx format)
  - Include both student and parent phone numbers

- [ ] 3.4: Create AttendanceRecordSeeder (optional for testing)
  - Create sample attendance records for last 7 days
  - Mix of hadir, terlambat, alpha statuses

- [ ] 3.5: Register seeders in DatabaseSeeder
- [ ] 3.6: Test seeders run without errors

---

## Task 4: Create Service Layer - AttendanceService

**Priority:** Critical
**Estimated Time:** 6 hours
**Dependencies:** Task 2
**Status:** pending

Core business logic for attendance processing.

### Subtasks:

- [ ] 4.1: Create `AttendanceService` class in app/Services

- [ ] 4.2: Implement `processCheckIn(string $phone): array` method
  - Find student by phone number (no_hp_siswa)
  - Validate: student exists (return error if not)
  - Validate: not already checked in today (return error if already)
  - Validate: within check-in window (05:00 - cutoff_time)
  - Determine status using AttendanceStatusService
  - Create AttendanceRecord with check_in_time and status
  - Log the action to attendance_logs
  - Trigger parent notification (async via Queue)
  - Return success response with confirmation message

- [ ] 4.3: Implement `processCheckOut(string $phone): array` method
  - Find student by phone number
  - Validate: student exists
  - Validate: has checked in today (return error if not)
  - Validate: not already checked out today (return error if already)
  - Update AttendanceRecord with check_out_time
  - Log the action to attendance_logs
  - Trigger parent notification (async via Queue)
  - Return success response with confirmation message

- [ ] 4.4: Implement `markAbsentStudents(): int` method
  - Get all active students who haven't checked in today
  - Create AttendanceRecord with status 'alpha' for each
  - Return count of marked students
  - This will be called by scheduled task at cutoff time

- [ ] 4.5: Implement `getTodayAttendance(?int $classId = null): Collection` method
  - Query today's attendance records with student and class data
  - Apply class filter if provided
  - Order by class name, student name

- [ ] 4.6: Implement `getAttendanceStats(string $date): array` method
  - Count total_hadir, total_terlambat, total_alpha
  - Count total_belum (students without attendance record for date)
  - Return array with all stats

- [ ] 4.7: Add error handling and logging
- [ ] 4.8: Test all service methods with unit tests

---

## Task 5: Create Service Layer - Supporting Services

**Priority:** Critical
**Estimated Time:** 5 hours
**Dependencies:** Task 2
**Status:** pending

Create additional services for WhatsApp, notifications, status determination, and exports.

### Subtasks:

- [ ] 5.1: Create `AttendanceWhatsAppService` class
  - Set base URL: http://localhost:3001
  - Implement sendConfirmation($phone, $message) - POST to /api/send
  - Implement sendParentNotification($phone, $message) - POST to /api/send
  - Implement normalizePhone($phone) - convert to 628xxx format
  - Implement getGatewayStatus() - GET /api/status
  - Add error handling and retry logic

- [ ] 5.2: Create `AttendanceNotificationService` class
  - Implement notifyCheckIn($student, $record) method
  - Implement notifyCheckOut($student, $record) method
  - Implement formatCheckInMessage() private method
    * Format: "[ABSENSI]\nAnanda {nama} telah absen masuk pada {time}.\nStatus: {status}"
  - Implement formatCheckOutMessage() private method
    * Format: "[ABSENSI]\nAnanda {nama} telah absen pulang pada {time}."
  - Skip notification if parent phone is empty

- [ ] 5.3: Create `AttendanceStatusService` class
  - Implement determineStatus($checkInTime): string method
    * Get settings: check_in_time, tolerance_minutes
    * If time <= (check_in_time + tolerance) → 'hadir'
    * If time > (check_in_time + tolerance) → 'terlambat'
  - Implement isWithinCheckInWindow($time): bool method
    * Check if time is between 05:00 and cutoff_time

- [ ] 5.4: Create `AttendanceExportService` class
  - Install Laravel Excel package: composer require maatwebsite/excel
  - Implement exportToExcel($filters): string method
  - Create export class: AttendanceRecordsExport
  - Apply filters: date_from, date_to, class_id, status
  - Format columns: Tanggal, NIS, Nama, Kelas, Jam Masuk, Jam Pulang, Status
  - Generate filename: Absensi_{StartDate}_to_{EndDate}.xlsx

- [ ] 5.5: Register all services in service provider
- [ ] 5.6: Test all service integrations

---

## Task 6: Create Controllers

**Priority:** Critical
**Estimated Time:** 6 hours
**Dependencies:** Task 4, Task 5
**Status:** pending

Create controllers for handling requests.

### Subtasks:

- [ ] 6.1: Create `AttendanceWebhookController`
  - Create handleIncoming(Request $request) method
  - Extract: phone, message, timestamp from request
  - Normalize phone number
  - Detect keyword: "ABSEN MASUK" or "ABSEN PULANG" (case insensitive)
  - Route to appropriate service method (processCheckIn or processCheckOut)
  - Return JSON response for WhatsApp gateway: {success, reply}
  - Handle unknown messages gracefully

- [ ] 6.2: Create `AttendanceDashboardController`
  - Create index() method - render dashboard view
  - Pass initial data to Livewire component

- [ ] 6.3: Create `AttendanceStudentController` (Resource controller)
  - Implement index() - list students with search, filter, pagination
  - Implement create() - show create form
  - Implement store() - validate and create student
    * Validate: nis unique, phone numbers valid, kelas exists
  - Implement edit($id) - show edit form
  - Implement update($id) - validate and update student
  - Implement destroy($id) - soft delete student
  - Add importExcel() method for Excel import
    * Validate Excel file
    * Parse rows using Laravel Excel
    * Validate each row: nis, nama, kelas, phone numbers
    * Create students in bulk
    * Return success/error report with invalid rows

- [ ] 6.4: Create `AttendanceClassController` (Resource controller)
  - Implement index() - list classes
  - Implement store() - create new class
  - Implement update($id) - update class
  - Implement destroy($id) - delete if no students enrolled
    * Check student count, prevent deletion if > 0

- [ ] 6.5: Create `AttendanceReportController`
  - Implement index() - show report page with filters
  - Implement export(Request $request) - export to Excel
    * Get filters: date_from, date_to, class_id, status
    * Call AttendanceExportService
    * Return download response

- [ ] 6.6: Create `AttendanceSettingController`
  - Implement index() - show settings form
  - Implement update(Request $request) - update settings
    * Validate: times are valid format (HH:MM)
    * Validate: cutoff_time > (check_in_time + tolerance)
    * Update all settings in database

- [ ] 6.7: Add authorization checks (middleware auth)
- [ ] 6.8: Add request validation classes for all controllers

---

## Task 7: Create Routes

**Priority:** Critical
**Estimated Time:** 1 hour
**Dependencies:** Task 6
**Status:** pending

Register all routes for the attendance system.

### Subtasks:

- [ ] 7.1: Add routes in routes/web.php
  - Create attendance route group with 'auth' middleware
  - Register dashboard route
  - Register student resource routes
  - Register student import route
  - Register class resource routes
  - Register report routes (index, export)
  - Register settings routes (index, update)

- [ ] 7.2: Add webhook route in routes/api.php
  - POST /api/attendance/webhook (no auth middleware)
  - Point to AttendanceWebhookController::handleIncoming

- [ ] 7.3: Test all routes registered correctly using `php artisan route:list`

---

## Task 8: Create Livewire Components

**Priority:** High
**Estimated Time:** 8 hours
**Dependencies:** Task 5, Task 6
**Status:** pending

Create real-time Livewire components for dynamic UI.

### Subtasks:

- [ ] 8.1: Install Livewire if not already installed
  - Run: composer require livewire/livewire
  - Publish config: php artisan livewire:publish --config

- [ ] 8.2: Create `AttendanceDashboard` Livewire component
  - Properties: selectedClass, stats, students
  - Add listener: refreshDashboard
  - Implement mount() - load initial data
  - Implement loadData() - get stats and attendance list
  - Implement updatedSelectedClass() - reload on filter change
  - Add polling: wire:poll.30s="loadData" in view
  - Create blade view with:
    * Stats cards (Hadir, Terlambat, Alpha, Belum Absen)
    * Class filter dropdown
    * Student attendance table (Nama, Kelas, Jam Masuk, Jam Pulang, Status)
    * Color-coded status badges
    * Dark mode compatible

- [ ] 8.3: Create `AttendanceStudentTable` Livewire component
  - Properties: search, classFilter, perPage
  - Use query string for search and filter
  - Implement updatingSearch() - reset pagination
  - Implement delete($id) - delete student with confirmation
  - Implement render() - query students with filters
  - Create blade view with:
    * Search input
    * Class filter dropdown
    * Students table (NIS, Nama, Kelas, HP Siswa, HP Ortu, Actions)
    * Edit/Delete buttons
    * Pagination
    * Dark mode compatible

- [ ] 8.4: Create `AttendanceReportGenerator` Livewire component
  - Properties: dateFrom, dateTo, classId, status, preview
  - Implement mount() - set default dates (today)
  - Implement generatePreview() - query and show preview
  - Implement export() - call export service and download
  - Create blade view with:
    * Date range picker
    * Class filter dropdown
    * Status filter (All, Hadir, Terlambat, Alpha)
    * Preview button
    * Export to Excel button
    * Preview table
    * Dark mode compatible

- [ ] 8.5: Test all Livewire components functionality
- [ ] 8.6: Test real-time updates (polling, events)

---

## Task 9: Setup WhatsApp Gateway

**Priority:** Critical
**Estimated Time:** 4 hours
**Dependencies:** None
**Status:** pending

Setup separate WhatsApp gateway on port 3001 for attendance system.

### Subtasks:

- [ ] 9.1: Create new directory: `absensi/whatsapp-gateway`
- [ ] 9.2: Initialize Node.js project
  - Run: npm init -y
  - Install dependencies: npm install whatsapp-web.js qrcode-terminal express body-parser

- [ ] 9.3: Create `server.js` file
  - Import whatsapp-web.js, express, body-parser
  - Setup Express server on port 3001
  - Initialize WhatsApp client with auth strategy
  - Display QR code on terminal for authentication
  - Handle 'ready' event
  - Handle 'message' event - forward to Laravel webhook

- [ ] 9.4: Implement webhook forwarding
  - On incoming message, POST to Laravel: http://localhost:8000/api/attendance/webhook
  - Send: phone (from), message (body), timestamp
  - Receive: success, reply message
  - Send reply back to WhatsApp sender

- [ ] 9.5: Create API endpoints
  - POST /api/send - send message to phone number
    * Body: {phone, message}
    * Return: {success, messageId}
  - GET /api/status - check gateway status
    * Return: {connected, phone_number}

- [ ] 9.6: Add error handling and logging
- [ ] 9.7: Create PM2 ecosystem file for process management
  - Name: "attendance-whatsapp-gateway"
  - Script: server.js
  - Instances: 1
  - Auto restart: true

- [ ] 9.8: Test WhatsApp authentication (scan QR code)
- [ ] 9.9: Test sending and receiving messages
- [ ] 9.10: Document setup steps in README.md

---

## Task 10: Implement Webhook Integration

**Priority:** Critical
**Estimated Time:** 3 hours
**Dependencies:** Task 6, Task 9
**Status:** pending

Integrate WhatsApp gateway with Laravel webhook endpoint.

### Subtasks:

- [ ] 10.1: Test webhook endpoint is accessible
  - POST to /api/attendance/webhook from Postman
  - Verify request is received and processed

- [ ] 10.2: Test "ABSEN MASUK" flow end-to-end
  - Send WhatsApp message: "ABSEN MASUK"
  - Verify gateway forwards to Laravel
  - Verify Laravel processes check-in
  - Verify confirmation sent back to student
  - Verify parent receives notification
  - Verify attendance record created in database
  - Verify log entry created

- [ ] 10.3: Test "ABSEN PULANG" flow end-to-end
  - Send WhatsApp message: "ABSEN PULANG"
  - Verify gateway forwards to Laravel
  - Verify Laravel processes check-out
  - Verify confirmation sent back to student
  - Verify parent receives notification
  - Verify attendance record updated in database

- [ ] 10.4: Test error cases
  - Unregistered phone number → "Nomor tidak terdaftar"
  - Already checked in → "Anda sudah absen masuk hari ini"
  - Check-out without check-in → "Anda belum absen masuk hari ini"
  - Outside time window → "Waktu absensi masuk telah berakhir"

- [ ] 10.5: Test response time (should be < 3 seconds)
- [ ] 10.6: Add request logging for debugging

---

## Task 11: Create Dashboard Views

**Priority:** High
**Estimated Time:** 5 hours
**Dependencies:** Task 8
**Status:** pending

Create beautiful dashboard UI for real-time monitoring.

### Subtasks:

- [ ] 11.1: Create main layout: `resources/views/layouts/attendance.blade.php`
  - Extend existing app layout or create new
  - Add Livewire styles and scripts
  - Add Bootstrap 5 CSS (or use existing)
  - Add FontAwesome icons
  - Add custom CSS for dark mode compatibility

- [ ] 11.2: Create dashboard view: `resources/views/attendance/dashboard.blade.php`
  - Include Livewire component: <livewire:attendance-dashboard />

- [ ] 11.3: Create Livewire dashboard component view
  - Create 4 stat cards at top:
    * Hadir (green background)
    * Terlambat (yellow background)
    * Alpha (red background)
    * Belum Absen (gray background)
  - Add class filter dropdown
  - Add attendance table:
    * Columns: No, Nama, Kelas, Jam Masuk, Jam Pulang, Status
    * Color-coded status badges
    * Empty state for no data
  - Add auto-refresh indicator
  - Use CSS variables for colors (dark mode compatible)

- [ ] 11.4: Add navigation menu item for Dashboard
  - Update sidebar/navbar to include "Dashboard Absensi"
  - Add icon (e.g., fa-chart-line)

- [ ] 11.5: Test responsive design (mobile, tablet, desktop)
- [ ] 11.6: Test dark mode compatibility

---

## Task 12: Create Student Management Views

**Priority:** High
**Estimated Time:** 6 hours
**Dependencies:** Task 8
**Status:** pending

Create CRUD views for student management.

### Subtasks:


- [ ] 12.1: Create student list view: `resources/views/attendance/students/index.blade.php`
  - Include Livewire component: <livewire:attendance-student-table />
  - Add "Tambah Siswa" button
  - Add "Import Excel" button

- [ ] 12.2: Create Livewire student table component view
  - Search input (by nama or nis)
  - Class filter dropdown
  - Students table:
    * Columns: NIS, Nama, Kelas, HP Siswa, HP Ortu, Actions
    * Edit button (icon)
    * Delete button (icon) with confirmation
  - Pagination controls
  - Empty state

- [ ] 12.3: Create student create/edit form view: `resources/views/attendance/students/form.blade.php`
  - Form fields: NIS, Nama, Kelas (dropdown), No HP Siswa, No HP Ortu, Foto (upload)
  - Validation error display
  - Submit button
  - Cancel button
  - Use Bootstrap form styling
  - Dark mode compatible

- [ ] 12.4: Create Excel import modal
  - File upload input (accept .xlsx, .xls)
  - Download template button
  - Import button
  - Progress indicator
  - Success/error report display
  - Show invalid rows with reasons

- [ ] 12.5: Create Excel template file
  - Columns: NIS, Nama, Kelas, No HP Siswa, No HP Ortu
  - Include sample rows with instructions
  - Save as: storage/app/templates/template-import-siswa.xlsx

- [ ] 12.6: Add navigation menu item for Student Management
- [ ] 12.7: Test CRUD operations (Create, Read, Update, Delete)
- [ ] 12.8: Test Excel import with valid and invalid data
- [ ] 12.9: Test form validations
- [ ] 12.10: Test responsive design

---

## Task 13: Create Reports & Export Views

**Priority:** High
**Estimated Time:** 4 hours
**Dependencies:** Task 8
**Status:** pending

Create report generation and export functionality.

### Subtasks:

- [ ] 13.1: Create report view: `resources/views/attendance/reports/index.blade.php`
  - Include Livewire component: <livewire:attendance-report-generator />

- [ ] 13.2: Create Livewire report generator component view
  - Date range picker (from - to)
  - Class filter dropdown
  - Status filter dropdown (All, Hadir, Terlambat, Alpha)
  - "Tampilkan Preview" button
  - "Export ke Excel" button
  - Preview table:
    * Columns: Tanggal, NIS, Nama, Kelas, Jam Masuk, Jam Pulang, Status
    * Paginated results
    * Empty state
  - Loading indicators

- [ ] 13.3: Test report preview with various filters
- [ ] 13.4: Test Excel export
  - Verify file downloads
  - Verify filename format: Absensi_YYYY-MM-DD_to_YYYY-MM-DD.xlsx
  - Open Excel file and verify data accuracy
  - Verify formatting (headers, columns, data types)

- [ ] 13.5: Add navigation menu item for Reports
- [ ] 13.6: Test with large datasets (1000+ records)
- [ ] 13.7: Test performance (should complete in < 10 seconds for 1000 records)

---

## Task 14: Create Settings Views

**Priority:** Medium
**Estimated Time:** 3 hours
**Dependencies:** Task 6
**Status:** pending

Create settings page for time configuration.

### Subtasks:

- [ ] 14.1: Create settings view: `resources/views/attendance/settings/index.blade.php`
  - Create form with sections:
    * Waktu Absensi section
    * Toleransi section
    * Notifikasi section
    * Informasi Sekolah section

- [ ] 14.2: Add form fields
  - Jam Masuk (time input, e.g., 07:00)
  - Jam Pulang (time input, e.g., 15:00)
  - Toleransi Keterlambatan (number input in minutes, e.g., 15)
  - Cut-off Time (time input, e.g., 09:00)
  - Aktifkan Notifikasi Orang Tua (checkbox)
  - Nama Sekolah (text input)

- [ ] 14.3: Add validation messages display
- [ ] 14.4: Add save button with loading state
- [ ] 14.5: Add success/error flash messages
- [ ] 14.6: Pre-populate form with current settings from database

- [ ] 14.7: Test settings update
  - Update all fields
  - Verify settings saved to database
  - Verify validation works (e.g., cutoff must be after check-in time)

- [ ] 14.8: Test settings are applied to attendance processing
  - Change cutoff time
  - Process check-in and verify status is determined correctly

- [ ] 14.9: Add navigation menu item for Settings
- [ ] 14.10: Test dark mode compatibility

---

## Task 15: Setup Scheduled Jobs

**Priority:** High
**Estimated Time:** 2 hours
**Dependencies:** Task 4
**Status:** pending

Setup Laravel scheduler for automatic alpha marking.

### Subtasks:

- [ ] 15.1: Create scheduled command: `php artisan make:command MarkAbsentStudents`
  - Set signature: attendance:mark-absent
  - Set description: "Mark students who haven't checked in as Alpha"
  - Implement handle() method:
    * Get cutoff_time from settings
    * Call AttendanceService::markAbsentStudents()
    * Log result

- [ ] 15.2: Register command in app/Console/Kernel.php
  - Add to schedule(): $schedule->command('attendance:mark-absent')->dailyAt('09:00')
  - Adjust time based on cutoff_time setting (dynamic scheduling)

- [ ] 15.3: Test command manually: `php artisan attendance:mark-absent`
  - Verify alpha records are created for students who haven't checked in

- [ ] 15.4: Setup Laravel scheduler cron job
  - Add to server crontab: * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  - Document in README.md

- [ ] 15.5: Create optional daily summary command (future enhancement)
  - Send attendance summary to admin via WhatsApp at 4pm


- [ ] 15.6: Test scheduler is running: `php artisan schedule:list`

---

## Task 16: Testing, Polish & Documentation

**Priority:** High
**Estimated Time:** 8 hours
**Dependencies:** All previous tasks
**Status:** pending

Final testing, bug fixes, and documentation.

### Subtasks:

- [ ] 16.1: End-to-end testing - Happy path
  - Complete student registration
  - Check-in on time (status: hadir)
  - Check-out
  - Verify dashboard updates
  - Verify parent notifications sent
  - Verify logs recorded

- [ ] 16.2: End-to-end testing - Late check-in
  - Student checks in after tolerance period
  - Verify status: terlambat
  - Verify notification includes "Terlambat"

- [ ] 16.3: End-to-end testing - No check-in (alpha)
  - Student doesn't check in
  - Run scheduled command
  - Verify status: alpha
  - Verify record created

- [ ] 16.4: Test all error scenarios
  - Unregistered phone number
  - Duplicate check-in
  - Check-out without check-in
  - Outside time window
  - Invalid Excel import data
  - Class deletion with students

- [ ] 16.5: Performance testing
  - Test with 100+ students
  - Test dashboard load time
  - Test export with 1000+ records
  - Test webhook response time (< 3 seconds)

- [ ] 16.6: Browser compatibility testing
  - Chrome
  - Firefox
  - Edge
  - Mobile browsers (iOS Safari, Android Chrome)

- [ ] 16.7: Dark mode testing
  - Test all pages in dark mode
  - Verify colors use CSS variables
  - Fix any contrast issues

- [ ] 16.8: Mobile responsiveness testing
  - Test dashboard on mobile devices
  - Test student management on mobile
  - Test reports on mobile
  - Fix any layout issues

- [ ] 16.9: Security review
  - Verify all routes have auth middleware
  - Verify webhook endpoint validation
  - Check for SQL injection vulnerabilities
  - Check for XSS vulnerabilities
  - Verify phone number sanitization

- [ ] 16.10: Code cleanup
  - Remove commented code
  - Remove debug statements
  - Format code consistently
  - Add missing comments

- [ ] 16.11: Create comprehensive README.md
  - Project overview
  - Features list
  - System requirements
  - Installation steps:
    * Clone repository
    * Install PHP dependencies (composer install)
    * Install Node.js dependencies (npm install)
    * Setup database
    * Run migrations and seeders
    * Setup WhatsApp gateway
    * Configure environment variables
    * Start Laravel server (php artisan serve)
    * Start WhatsApp gateway (pm2 start ecosystem.config.js)
    * Setup cron job for scheduler
  - Usage instructions:
    * How to add students
    * How to import from Excel
    * How students check in/out via WhatsApp
    * How to view dashboard
    * How to generate reports
    * How to configure settings
  - Troubleshooting section
  - Screenshots of all major features

- [ ] 16.12: Create user manual (PDF or online)
  - For admin: complete guide with screenshots
  - For students: simple guide on how to use WhatsApp
  - For parents: what notifications they will receive

- [ ] 16.13: Create API documentation for webhook
  - Document webhook endpoint format
  - Request/response examples
  - Error codes

- [ ] 16.14: Setup logging and monitoring
  - Configure Laravel log channels
  - Add monitoring for WhatsApp gateway uptime
  - Add alerts for gateway disconnection

- [ ] 16.15: Create backup strategy documentation
  - Database backup procedures
  - WhatsApp session backup
  - Recovery procedures

- [ ] 16.16: Final deployment checklist
  - [ ] Database migrations run
  - [ ] Seeders run (settings only for production)
  - [ ] Environment variables configured
  - [ ] WhatsApp gateway authenticated
  - [ ] PM2 processes running
  - [ ] Laravel scheduler cron job active
  - [ ] File permissions set correctly
  - [ ] SSL certificate configured (if production)
  - [ ] Firewall rules configured
  - [ ] Backup strategy implemented

---

## Summary

### Critical Path (Must complete in order):

1. Task 1: Database Schema (4h)
2. Task 2: Models (3h)
3. Task 4: AttendanceService (6h)
4. Task 5: Supporting Services (5h)
5. Task 6: Controllers (6h)
6. Task 7: Routes (1h)
7. Task 9: WhatsApp Gateway (4h)
8. Task 10: Webhook Integration (3h)
9. Task 8: Livewire Components (8h)
10. Task 11: Dashboard Views (5h)
11. Task 15: Scheduled Jobs (2h)
12. Task 16: Testing & Polish (8h)

**Total Critical Path Time: ~55 hours**

### Can be done in parallel:
- Task 3: Seeders (while building services)
- Task 12: Student Management Views (while building other views)
- Task 13: Reports Views (while building other views)
- Task 14: Settings Views (while building other views)

### Recommended Development Order:

**Week 1 (Day 1-2): Foundation**
- Task 1: Database Schema
- Task 2: Models
- Task 3: Seeders
- Task 7: Routes

**Week 1 (Day 3-4): Core Logic**
- Task 4: AttendanceService
- Task 5: Supporting Services
- Task 6: Controllers

**Week 2 (Day 5-6): WhatsApp Integration**
- Task 9: WhatsApp Gateway Setup
- Task 10: Webhook Integration
- End-to-end testing of check-in/check-out flow

**Week 2 (Day 7-8): UI Development**
- Task 8: Livewire Components
- Task 11: Dashboard Views
- Task 12: Student Management Views

**Week 3 (Day 9): UI Development Continued**
- Task 13: Reports Views
- Task 14: Settings Views
- Task 15: Scheduled Jobs

**Week 3 (Day 10): Final Polish**
- Task 16: Testing, Bug Fixes, Documentation

### Minimum Viable Product (MVP) Checklist:

To have a working attendance system, you MUST complete:
- ✅ Task 1: Database Schema
- ✅ Task 2: Models
- ✅ Task 4: AttendanceService (processCheckIn, processCheckOut)
- ✅ Task 5: Supporting Services (WhatsApp, Notification, Status)
- ✅ Task 6: Controllers (Webhook, Student basic CRUD)
- ✅ Task 7: Routes
- ✅ Task 9: WhatsApp Gateway
- ✅ Task 10: Webhook Integration
- ✅ Task 11: Dashboard (basic view, can skip real-time polling for MVP)
- ✅ Task 12: Student Management (at least create/list)
- ✅ Task 15: Scheduled Jobs (alpha marking)

**MVP Time: ~45 hours (6 working days)**

### Nice-to-Have (Can be added post-MVP):
- Excel import/export functionality
- Real-time dashboard polling
- Advanced reports with filters
- Settings UI (can use database directly for MVP)
- Comprehensive testing
- User manual and documentation
- Mobile optimization

---

## Notes

- All times are estimates and may vary based on developer experience
- Each task should be tested individually before moving to the next
- Use git commits at the end of each major task
- Keep the SPMB WhatsApp gateway (port 3000) separate from Attendance gateway (port 3001)
- Follow existing SPMB project patterns for consistency
- Prioritize functionality over aesthetics for MVP
- Dark mode compatibility is a must from the start (easier than retrofitting)

---

## Risk Mitigation

**High Risk Areas:**
1. **WhatsApp Gateway Stability**
   - Mitigation: Add auto-restart in PM2, implement health checks

2. **Response Time > 3 seconds**
   - Mitigation: Use queue jobs for notifications, optimize database queries with indexes

3. **Phone Number Format Inconsistency**
   - Mitigation: Normalize all phone numbers to 628xxx format on input and query

4. **Race Conditions (concurrent check-ins)**
   - Mitigation: Use database unique constraints, handle duplicate key exceptions

5. **Timezone Issues**
   - Mitigation: Always use Carbon with WIB timezone, store all times in WIB

**Medium Risk Areas:**
1. **Large dataset performance (1000+ students)**
   - Mitigation: Add pagination, use database indexes, implement caching

2. **Excel import data quality**
   - Mitigation: Comprehensive validation, provide clear error messages, example template

3. **Dark mode CSS conflicts**
   - Mitigation: Use CSS variables from the start, test both modes continuously

---

## Future Enhancements (Post-MVP)

These features are explicitly out of scope for MVP but can be added later:

1. **QR Code Attendance**
   - Generate unique QR codes per student
   - Scan QR code for instant check-in

2. **GPS/Location Validation**
   - Verify student is within school radius
   - Prevent remote check-ins

3. **Izin/Sakit Management**
   - Allow students to submit permission/sick leave
   - Upload doctor's note
   - Approval workflow for admin

4. **Advanced Analytics**
   - Attendance trends graphs
   - Per-student attendance percentage
   - Class-wise comparison charts
   - Monthly reports

5. **Multi-Semester Support**
   - Track attendance across multiple semesters
   - Academic year management
   - Historical data archiving

6. **Reminder Scheduler**
   - Automatic reminders to students who haven't checked in
   - Daily summary to admin/principal
   - Weekly reports to parents

7. **Parent Portal**
   - Web portal for parents to view attendance history
   - Download monthly reports
   - Set notification preferences

8. **Integration with Academic System**
   - Sync student data from existing SIS
   - Export attendance for report cards
   - API for third-party integrations

9. **Mobile Native App**
   - iOS and Android apps for students
   - Push notifications
   - Offline capability

10. **Biometric Integration**
    - Fingerprint scanner integration
    - Face recognition
    - Hybrid approach (WhatsApp + Biometric)

---

## Success Metrics

The MVP will be considered successful if:

1. ✅ Students can check in/out via WhatsApp in < 3 seconds
2. ✅ Parents receive notifications within 5 seconds
3. ✅ Dashboard updates in real-time (or within 30 seconds via polling)
4. ✅ System correctly determines status (Hadir, Terlambat, Alpha)
5. ✅ Zero duplicate attendance records per student per day
6. ✅ Excel export completes in < 10 seconds for 1000 records
7. ✅ WhatsApp gateway uptime > 99%
8. ✅ Zero learning curve for students (no training needed)
9. ✅ Admin can manage 500+ students efficiently
10. ✅ System handles 100+ concurrent check-ins without degradation

---

**End of Task Breakdown**
