# Requirements Document

## Introduction

Sistem Absensi Siswa adalah aplikasi MVP (Minimum Viable Product) untuk SMK/sekolah kejuruan di Indonesia yang memungkinkan siswa melakukan absensi kehadiran melalui WhatsApp tanpa perlu membuka aplikasi tambahan. Sistem ini menyediakan notifikasi real-time kepada orang tua dan dashboard monitoring untuk admin/guru.

Tujuan utama adalah menciptakan sistem absensi yang:
- Zero learning curve untuk siswa (cukup kirim pesan WhatsApp)
- Memberikan transparansi kepada orang tua secara real-time
- Memudahkan admin/guru dalam monitoring kehadiran
- Response time cepat (< 3 detik)

## Glossary

- **Absensi_System**: Sistem absensi siswa berbasis WhatsApp yang mengelola kehadiran siswa
- **WhatsApp_Gateway**: Layanan gateway WhatsApp yang berjalan pada port 3001 untuk menerima dan mengirim pesan
- **Student**: Siswa yang terdaftar dalam sistem dengan nomor WhatsApp yang terverifikasi
- **Parent**: Orang tua atau wali siswa yang menerima notifikasi kehadiran
- **Admin**: Pengguna sistem dengan role administrator atau guru yang dapat mengelola data dan monitoring
- **Attendance_Record**: Record absensi yang mencatat waktu masuk/pulang dan status kehadiran
- **Attendance_Status**: Status kehadiran siswa yang dapat berupa: Hadir, Terlambat, atau Alpha
- **Check_In**: Proses absensi masuk siswa di awal hari sekolah
- **Check_Out**: Proses absensi pulang siswa di akhir hari sekolah
- **Cut_Off_Time**: Batas waktu maksimal untuk absensi masuk, setelah waktu ini siswa otomatis tercatat Alpha
- **Tolerance_Period**: Periode toleransi keterlambatan setelah jam masuk resmi (dalam menit)
- **Dashboard**: Halaman monitoring real-time untuk admin/guru
- **Excel_Import**: Fitur untuk mengimpor data siswa dari file Excel
- **Excel_Export**: Fitur untuk mengekspor data absensi ke file Excel

## Requirements

### Requirement 1: Registrasi Nomor WhatsApp Siswa

**User Story:** Sebagai Admin, saya ingin mengelola data siswa dan nomor WhatsApp mereka, sehingga sistem dapat mengidentifikasi siswa yang melakukan absensi

#### Acceptance Criteria

1. THE Admin SHALL create student records with fields: Nama, NIS, Kelas, No HP Siswa, No HP Orang Tua
2. THE Admin SHALL update existing student records
3. THE Admin SHALL delete student records
4. THE Admin SHALL view a list of all registered students
5. WHEN the Admin imports an Excel file with student data, THE Absensi_System SHALL parse the file and create student records
6. IF an Excel import contains invalid data, THEN THE Absensi_System SHALL return a validation error message indicating which rows are invalid
7. THE Absensi_System SHALL store student WhatsApp phone numbers in international format (e.g., 628123456789)

### Requirement 2: Absensi Masuk via WhatsApp

**User Story:** Sebagai Student, saya ingin melakukan absensi masuk dengan mengirim pesan WhatsApp, sehingga saya tidak perlu membuka aplikasi tambahan

#### Acceptance Criteria

1. WHEN a Student sends a WhatsApp message containing "ABSEN MASUK" to THE WhatsApp_Gateway, THE Absensi_System SHALL check if the sender's phone number is registered
2. WHEN a registered Student sends "ABSEN MASUK" AND has not checked in today, THE Absensi_System SHALL create an Attendance_Record with check-in timestamp
3. WHEN a registered Student sends "ABSEN MASUK" AND has already checked in today, THE Absensi_System SHALL reply with "Anda sudah absen masuk hari ini"
4. WHEN an unregistered phone number sends "ABSEN MASUK", THE Absensi_System SHALL reply with "Nomor tidak terdaftar"
5. WHEN a check-in is recorded, THE Absensi_System SHALL reply to the Student within 3 seconds with confirmation message including timestamp
6. THE Absensi_System SHALL accept check-in messages between 05:00 and the Cut_Off_Time on the same day
7. WHEN a Student attempts check-in before 05:00 or after Cut_Off_Time, THE Absensi_System SHALL reply with "Waktu absensi masuk telah berakhir"

### Requirement 3: Absensi Pulang via WhatsApp

**User Story:** Sebagai Student, saya ingin melakukan absensi pulang dengan mengirim pesan WhatsApp, sehingga sistem mencatat waktu kepulangan saya

#### Acceptance Criteria

1. WHEN a Student sends a WhatsApp message containing "ABSEN PULANG" to THE WhatsApp_Gateway, THE Absensi_System SHALL check if the sender has checked in today
2. WHEN a Student who has checked in sends "ABSEN PULANG" AND has not checked out today, THE Absensi_System SHALL update the Attendance_Record with check-out timestamp
3. WHEN a Student who has not checked in sends "ABSEN PULANG", THE Absensi_System SHALL reply with "Anda belum absen masuk hari ini"
4. WHEN a Student sends "ABSEN PULANG" AND has already checked out today, THE Absensi_System SHALL reply with "Anda sudah absen pulang hari ini"
5. WHEN a check-out is recorded, THE Absensi_System SHALL reply to the Student within 3 seconds with confirmation message including timestamp
6. THE Absensi_System SHALL accept check-out messages only after the student's check-in time on the same day

### Requirement 4: Penentuan Status Kehadiran

**User Story:** Sebagai Admin, saya ingin sistem otomatis menentukan status kehadiran siswa berdasarkan waktu absensi, sehingga tidak perlu input manual

#### Acceptance Criteria

1. WHEN a Student checks in before or at (check-in time + Tolerance_Period), THE Absensi_System SHALL set Attendance_Status to "Hadir"
2. WHEN a Student checks in after (check-in time + Tolerance_Period) but before Cut_Off_Time, THE Absensi_System SHALL set Attendance_Status to "Terlambat"
3. WHEN the current time reaches Cut_Off_Time AND a Student has not checked in, THE Absensi_System SHALL create an Attendance_Record with Attendance_Status "Alpha"
4. THE Absensi_System SHALL include the Attendance_Status in the confirmation message sent to the Student
5. THE Absensi_System SHALL allow Students to check in on weekends and holidays with Attendance_Status marked accordingly

### Requirement 5: Notifikasi kepada Orang Tua

**User Story:** Sebagai Parent, saya ingin menerima notifikasi WhatsApp ketika anak saya absen masuk atau pulang, sehingga saya mengetahui keberadaan anak secara real-time

#### Acceptance Criteria

1. WHEN a Student check-in is recorded, THE Absensi_System SHALL send a WhatsApp notification to the Parent's registered phone number
2. WHEN a Student check-out is recorded, THE Absensi_System SHALL send a WhatsApp notification to the Parent's registered phone number
3. THE Absensi_System SHALL format the check-in notification as: "[ABSENSI] Ananda [Nama] telah absen masuk pada [Waktu]. Status: [Hadir/Terlambat]"
4. THE Absensi_System SHALL format the check-out notification as: "[ABSENSI] Ananda [Nama] telah absen pulang pada [Waktu]"
5. THE Absensi_System SHALL send parent notifications within 5 seconds of recording the attendance
6. IF the Parent's phone number is not registered, THE Absensi_System SHALL skip the notification without failing the attendance recording

### Requirement 6: Dashboard Real-Time untuk Admin

**User Story:** Sebagai Admin, saya ingin melihat dashboard kehadiran real-time hari ini, sehingga saya dapat memonitor siswa yang sudah dan belum hadir

#### Acceptance Criteria

1. THE Dashboard SHALL display a list of all students with their current attendance status for today
2. THE Dashboard SHALL display a summary count of: Total Hadir, Total Terlambat, Total Alpha, Total Belum Absen
3. THE Dashboard SHALL refresh the attendance data every 30 seconds without page reload
4. WHEN the Admin selects a class filter, THE Dashboard SHALL display only students from that class
5. THE Dashboard SHALL display check-in and check-out timestamps for each student who has attendance records today
6. THE Dashboard SHALL indicate visually which students have not yet checked in using distinct styling

### Requirement 7: Export Laporan Absensi

**User Story:** Sebagai Admin, saya ingin mengekspor data absensi ke Excel, sehingga saya dapat membuat laporan dan arsip

#### Acceptance Criteria

1. WHEN the Admin requests an Excel export, THE Absensi_System SHALL generate an Excel file containing attendance data
2. THE Absensi_System SHALL include columns: Tanggal, NIS, Nama, Kelas, Jam Masuk, Jam Pulang, Status
3. WHEN the Admin applies a date range filter, THE Absensi_System SHALL export only attendance records within that range
4. WHEN the Admin applies a class filter, THE Absensi_System SHALL export only attendance records for students in that class
5. THE Absensi_System SHALL generate and download the Excel file within 10 seconds for up to 1000 records
6. THE Absensi_System SHALL name the exported file with format: "Absensi_[StartDate]_to_[EndDate].xlsx"

### Requirement 8: Konfigurasi Jam dan Toleransi

**User Story:** Sebagai Admin, saya ingin mengatur jam masuk, jam pulang, toleransi keterlambatan, dan cut-off time, sehingga sistem dapat disesuaikan dengan aturan sekolah

#### Acceptance Criteria

1. THE Admin SHALL configure the official check-in time (e.g., 07:00)
2. THE Admin SHALL configure the official check-out time (e.g., 15:00)
3. THE Admin SHALL configure the Tolerance_Period in minutes (e.g., 15 minutes)
4. THE Admin SHALL configure the Cut_Off_Time for automatic Alpha marking (e.g., 09:00)
5. WHEN the Admin saves attendance settings, THE Absensi_System SHALL apply the new settings to all subsequent attendance recordings
6. THE Absensi_System SHALL validate that Cut_Off_Time is after (check-in time + Tolerance_Period)
7. IF the Admin enters invalid time settings, THE Absensi_System SHALL display a validation error message

### Requirement 9: Pengelolaan Data Kelas

**User Story:** Sebagai Admin, saya ingin mengelola data kelas, sehingga siswa dapat dikelompokkan berdasarkan kelas mereka

#### Acceptance Criteria

1. THE Admin SHALL create class records with fields: Nama Kelas, Tingkat, Jurusan
2. THE Admin SHALL update existing class records
3. THE Admin SHALL delete class records that have no associated students
4. THE Admin SHALL view a list of all classes
5. WHEN the Admin attempts to delete a class with enrolled students, THE Absensi_System SHALL prevent deletion and display an error message
6. THE Admin SHALL assign students to a specific class during student creation or update

### Requirement 10: Validasi Absensi Harian

**User Story:** Sebagai sistem, saya perlu memastikan integritas data absensi, sehingga tidak ada duplikasi atau data yang tidak valid

#### Acceptance Criteria

1. THE Absensi_System SHALL allow only one check-in record per Student per day
2. THE Absensi_System SHALL allow only one check-out record per Student per day
3. THE Absensi_System SHALL prevent check-out if no check-in exists for that day
4. THE Absensi_System SHALL store all timestamps in WIB (UTC+7) timezone
5. WHEN a Student attempts multiple check-ins on the same day, THE Absensi_System SHALL retain the first check-in timestamp and reject subsequent attempts
6. THE Absensi_System SHALL create a new attendance day at 00:00 WIB for the next calendar day

## Out of Scope for MVP

The following features are explicitly excluded from this MVP and will be considered for future versions:

- QR Code scanning untuk absensi
- GPS/Location validation
- Reminder scheduler (broadcast manual)
- Izin/Sakit dengan upload surat
- Grafik dan visualisasi lanjutan
- Multi-semester atau tahun ajaran
- Integrasi dengan sistem akademik lain
- Biometric authentication
- Mobile native applications
