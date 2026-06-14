# Tasks

## Task 1: Setup Database Schema & Models
Create database migrations and Eloquent models for attendance system

### Subtasks
- [ ] 1.1: Create migration for `attendance_students` table with indexes
- [ ] 1.2: Create migration for `attendance_classes` table
- [ ] 1.3: Create migration for `attendance_records` table with unique constraint
- [ ] 1.4: Create migration for `attendance_settings` table with default data
- [ ] 1.5: Create migration for `attendance_logs` table
- [ ] 1.6: Create AttendanceStudent model with relationships and helper methods
- [ ] 1.7: Create AttendanceClass model with scopes
- [ ] 1.8: Create AttendanceRecord model with scopes and accessors
- [ ] 1.9: Create AttendanceSetting model with static get/set methods
- [ ] 1.10: Create AttendanceLog model
- [ ] 1.11: Run migrations and verify schema
- [ ] 1.12: Create AttendanceSettingsSeeder for default values

**Depends on:** None

**Estimated:** 3-4 hours

---

## Task 2: Create Service Layer
Implement core business logic services for attendance processing

### Subtasks
- [ ] 2.1: Create AttendanceService with processCheckIn() method
- [ ] 2.2: Implement processCheckOut() method in AttendanceService
- [ ] 2.3: Implement markAbsentStudents() method for auto-alpha
- [ ] 2.4: Implement getTodayAttendance() and getAttendanceStats() methods
- [ ] 2.5: Create AttendanceStatusService with determineStatus() method
- [ ] 2.6: Implement isWithinCheckInWindow() validation
- [ ] 2.7: Create AttendanceWhatsAppService extending base WhatsAppService
- [ ] 2.8: Implement sendConfirmation() and sendParentNotification() methods
- [ ] 2.9: Implement normalizePhone() and validation methods
- [ ] 2.10: Create AttendanceNotificationService for message formatting
- [ ] 2.11: Implement formatCheckInMessage() and formatCheckOutMessage()
- [ ] 2.12: Create AttendanceExportService with exportToExcel() method

**Depends on:** Task 1

**Estimated:** 5-6 hours

---

## Task 3: Setup WhatsApp Gateway Integration
Configure separate WhatsApp gateway on port 3001 and webhook handling

### Subtasks
- [ ] 3.1: Copy whatsapp-server to whatsapp-server-absensi folder
- [ ] 3.2: Update server.js to use port 3001
- [ ] 3.3: Configure webhook URL to /api/attendance/webhook
- [ ] 3.4: Test gateway connection and QR code generation
- [ ] 3.5: Setup PM2 process for whatsapp-absensi
- [ ] 3.6: Configure auto-restart and monitoring
- [ ] 3.7: Create AttendanceWebhookController
- [ ] 3.8: Implement handleIncoming() method with keyword detection
- [ ] 3.9: Add webhook route (POST /api/attendance/webhook)
- [ ] 3.10: Implement phone number normalization in webhook
- [ ] 3.11: Add rate limiting middleware to webhook route
- [ ] 3.12: Test end-to-end message flow from WhatsApp to Laravel

**Depends on:** Task 2

**Estimated:** 4-5 hours

---

## Task 4: Implement Check-In/Check-Out Flow
Complete the attendance recording workflow with validations

### Subtasks
- [ ] 4.1: Integrate AttendanceService::processCheckIn() with webhook controller
- [ ] 4.2: Add validation: student exists by phone number
- [ ] 4.3: Add validation: not already checked in today
- [ ] 4.4: Add validation: within check-in time window
- [ ] 4.5: Implement status determination (hadir vs terlambat)
- [ ] 4.6: Create attendance record in database
- [ ] 4.7: Log check-in action to attendance_logs
- [ ] 4.8: Queue parent notification job
- [ ] 4.9: Return formatted confirmation message to student
- [ ] 4.10: Implement processCheckOut() with validations
- [ ] 4.11: Update attendance record with check-out time
- [ ] 4.12: Send check-out notification to parent
- [ ] 4.13: Test complete flow with multiple scenarios

**Depends on:** Task 3

**Estimated:** 4-5 hours

---

## Task 5: Implement Auto-Alpha Scheduler
Create scheduled task to mark absent students as Alpha

### Subtasks
- [ ] 5.1: Register scheduled command in app/Console/Kernel.php
- [ ] 5.2: Schedule markAbsentStudents() at cutoff time (09:00)
- [ ] 5.3: Query all students without check-in for today
- [ ] 5.4: Create attendance records with status 'alpha'
- [ ] 5.5: Log auto-alpha marking to attendance_logs
- [ ] 5.6: Add optional admin notification for daily summary
- [ ] 5.7: Test scheduler manually with artisan schedule:run
- [ ] 5.8: Verify cron job setup on server

**Depends on:** Task 4

**Estimated:** 2-3 hours

---


## Task 6: Create Admin Dashboard with Livewire
Build real-time attendance monitoring dashboard

### Subtasks
- [ ] 6.1: Create AttendanceDashboard Livewire component
- [ ] 6.2: Implement loadData() method to fetch today's attendance
- [ ] 6.3: Implement getAttendanceStats() for summary cards
- [ ] 6.4: Add class filter dropdown
- [ ] 6.5: Create dashboard blade view with Bootstrap 5 styling
- [ ] 6.6: Add wire:poll.30s for auto-refresh
- [ ] 6.7: Display summary cards (Hadir, Terlambat, Alpha, Belum Absen)
- [ ] 6.8: Display student list with attendance status
- [ ] 6.9: Add visual indicators (colors, icons) for each status
- [ ] 6.10: Create AttendanceDashboardController
- [ ] 6.11: Add route GET /attendance/dashboard
- [ ] 6.12: Add dark mode styling support
- [ ] 6.13: Make dashboard mobile-responsive

**Depends on:** Task 5

**Estimated:** 4-5 hours

---

## Task 7: Build Student Management Interface
Create CRUD interface for managing students with Excel import

### Subtasks
- [ ] 7.1: Create AttendanceStudentController with resource methods
- [ ] 7.2: Create index view with search and filter
- [ ] 7.3: Create AttendanceStudentTable Livewire component
- [ ] 7.4: Implement real-time search functionality
- [ ] 7.5: Add class filter dropdown
- [ ] 7.6: Create student create/edit form
- [ ] 7.7: Add phone number validation (Indonesian format)
- [ ] 7.8: Implement store() method with validation
- [ ] 7.9: Implement update() method
- [ ] 7.10: Implement delete() method with confirmation modal
- [ ] 7.11: Add student photo upload functionality
- [ ] 7.12: Create Excel import form
- [ ] 7.13: Implement importExcel() method with validation
- [ ] 7.14: Add import preview before saving
- [ ] 7.15: Show success/error report after import
- [ ] 7.16: Add resource routes for students

**Depends on:** Task 1

**Estimated:** 5-6 hours

---

## Task 8: Build Class Management Interface
Create CRUD interface for managing classes

### Subtasks
- [ ] 8.1: Create AttendanceClassController with resource methods
- [ ] 8.2: Create index view listing all classes
- [ ] 8.3: Create class create/edit modal
- [ ] 8.4: Implement store() method with validation
- [ ] 8.5: Implement update() method
- [ ] 8.6: Implement delete() method with student count check
- [ ] 8.7: Prevent deletion of classes with enrolled students
- [ ] 8.8: Display student count for each class
- [ ] 8.9: Add resource routes for classes
- [ ] 8.10: Apply Bootstrap 5 styling
- [ ] 8.11: Add dark mode support
- [ ] 8.12: Make interface mobile-responsive

**Depends on:** Task 1

**Estimated:** 3-4 hours

---

## Task 9: Build Settings Interface
Create configuration page for attendance times and rules

### Subtasks
- [ ] 9.1: Create AttendanceSettingController
- [ ] 9.2: Implement index() method to show settings form
- [ ] 9.3: Group settings by category (Time, Tolerance, Notification, School)
- [ ] 9.4: Create settings form with time pickers
- [ ] 9.5: Implement update() method with validation
- [ ] 9.6: Validate cutoff_time > (check_in_time + tolerance)
- [ ] 9.7: Add client-side validation
- [ ] 9.8: Clear settings cache after update
- [ ] 9.9: Show success message after save
- [ ] 9.10: Add routes GET/POST /attendance/settings
- [ ] 9.11: Add help text explaining each setting
- [ ] 9.12: Test settings persistence and application

**Depends on:** Task 1

**Estimated:** 3-4 hours

---

## Task 10: Build Reports & Export Interface
Create report generation and Excel export functionality

### Subtasks
- [ ] 10.1: Create AttendanceReportController
- [ ] 10.2: Create AttendanceReportGenerator Livewire component
- [ ] 10.3: Add date range picker (from/to)
- [ ] 10.4: Add class filter dropdown
- [ ] 10.5: Add status filter (Hadir, Terlambat, Alpha)
- [ ] 10.6: Implement generatePreview() method
- [ ] 10.7: Display preview table with filters
- [ ] 10.8: Implement export() method using AttendanceExportService
- [ ] 10.9: Generate Excel file with proper columns
- [ ] 10.10: Set filename format: Absensi_YYYY-MM-DD_to_YYYY-MM-DD.xlsx
- [ ] 10.11: Trigger file download
- [ ] 10.12: Add loading indicator during export
- [ ] 10.13: Test export with various filter combinations
- [ ] 10.14: Add routes GET /attendance/reports and POST /attendance/reports/export

**Depends on:** Task 2

**Estimated:** 4-5 hours

---


## Task 11: Implement Parent Notification System
Build asynchronous notification system for parents

### Subtasks
- [ ] 11.1: Create SendParentNotification job class
- [ ] 11.2: Implement handle() method with AttendanceNotificationService
- [ ] 11.3: Queue job after check-in success
- [ ] 11.4: Queue job after check-out success
- [ ] 11.5: Add retry logic for failed notifications
- [ ] 11.6: Log notification attempts to attendance_logs
- [ ] 11.7: Test notification delivery
- [ ] 11.8: Verify 5-second delivery requirement
- [ ] 11.9: Handle cases where parent phone is not registered
- [ ] 11.10: Configure queue worker on server
- [ ] 11.11: Test failure scenarios and retries

**Depends on:** Task 4

**Estimated:** 3-4 hours

---

## Task 12: Add Authentication & Authorization
Secure the attendance system with proper access control

### Subtasks
- [ ] 12.1: Add auth middleware to all attendance routes except webhook
- [ ] 12.2: Create AttendancePolicy for authorization rules
- [ ] 12.3: Restrict student/class deletion based on permissions
- [ ] 12.4: Add role check for settings modification
- [ ] 12.5: Create admin/guru role if not exists
- [ ] 12.6: Test unauthorized access scenarios
- [ ] 12.7: Add CSRF protection to forms
- [ ] 12.8: Secure webhook with token or IP whitelist

**Depends on:** Task 7, Task 8, Task 9

**Estimated:** 2-3 hours

---

## Task 13: Add UI Polish & Dark Mode
Apply consistent styling and dark mode support

### Subtasks
- [ ] 13.1: Create attendance layout template extending main layout
- [ ] 13.2: Add sidebar navigation for attendance modules
- [ ] 13.3: Apply Bootstrap 5 styling consistently
- [ ] 13.4: Add CSS variables for dark mode
- [ ] 13.5: Test all pages in light mode
- [ ] 13.6: Test all pages in dark mode
- [ ] 13.7: Add appropriate icons (FontAwesome)
- [ ] 13.8: Make all tables responsive
- [ ] 13.9: Add loading states for Livewire actions
- [ ] 13.10: Add success/error toast notifications
- [ ] 13.11: Optimize mobile view for dashboard
- [ ] 13.12: Test on different screen sizes

**Depends on:** Task 6, Task 7, Task 8, Task 9, Task 10

**Estimated:** 4-5 hours

---

## Task 14: Write Tests
Create comprehensive test suite

### Subtasks
- [ ] 14.1: Write unit tests for AttendanceService::processCheckIn()
- [ ] 14.2: Write unit tests for AttendanceService::processCheckOut()
- [ ] 14.3: Write unit tests for AttendanceStatusService::determineStatus()
- [ ] 14.4: Write unit tests for AttendanceService::markAbsentStudents()
- [ ] 14.5: Write feature test for webhook check-in flow
- [ ] 14.6: Write feature test for webhook check-out flow
- [ ] 14.7: Write feature test for duplicate check-in rejection
- [ ] 14.8: Write feature test for unregistered phone rejection
- [ ] 14.9: Write integration test for parent notification
- [ ] 14.10: Write integration test for Excel import
- [ ] 14.11: Write integration test for Excel export
- [ ] 14.12: Run all tests and ensure passing
- [ ] 14.13: Achieve minimum 70% code coverage

**Depends on:** Task 1-11

**Estimated:** 5-6 hours

---

## Task 15: Deployment & Configuration
Deploy to production server (aaPanel)

### Subtasks
- [ ] 15.1: Setup attendance database tables on production
- [ ] 15.2: Configure .env variables for production
- [ ] 15.3: Setup whatsapp-server-absensi on port 3001
- [ ] 15.4: Configure PM2 for whatsapp-absensi process
- [ ] 15.5: Setup cron job for Laravel scheduler
- [ ] 15.6: Configure queue worker for notifications
- [ ] 15.7: Test WhatsApp gateway connection
- [ ] 15.8: Scan QR code and authenticate WhatsApp
- [ ] 15.9: Test end-to-end flow on production
- [ ] 15.10: Import initial student data
- [ ] 15.11: Configure initial settings (times, tolerance)
- [ ] 15.12: Create admin user accounts
- [ ] 15.13: Perform smoke testing
- [ ] 15.14: Monitor logs for first day

**Depends on:** Task 1-14

**Estimated:** 4-5 hours

---

## Task 16: Documentation & Training
Create user documentation and train staff

### Subtasks
- [ ] 16.1: Write admin user guide (how to manage students, classes, settings)
- [ ] 16.2: Write student guide (how to use WhatsApp attendance)
- [ ] 16.3: Write parent guide (what notifications to expect)
- [ ] 16.4: Document troubleshooting common issues
- [ ] 16.5: Create quick reference card for admin
- [ ] 16.6: Conduct training session for admin/guru
- [ ] 16.7: Conduct demo for sample students
- [ ] 16.8: Collect feedback from training
- [ ] 16.9: Update documentation based on feedback
- [ ] 16.10: Create video tutorial (optional)

**Depends on:** Task 15

**Estimated:** 3-4 hours

---

## Summary

**Total Tasks:** 16
**Total Subtasks:** 195
**Estimated Time:** 60-70 hours (approximately 8-9 working days)

### Critical Path
Task 1 → Task 2 → Task 3 → Task 4 → Task 5 → Task 11 → Task 14 → Task 15

### Parallel Work Opportunities
- Tasks 7, 8, 9 can be done in parallel (all depend only on Task 1)
- Task 10 can start after Task 2
- Task 13 (UI Polish) can be done incrementally alongside other tasks

