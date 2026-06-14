# Requirements Document

## Introduction

Sistem Absensi Siswa adalah aplikasi MVP (Minimum Viable Product) untuk SMK/sekolah kejuruan di Indonesia yang memungkinkan siswa melakukan absensi kehadiran melalui QR Code Scanner dengan foto capture otomatis. Sistem ini menyediakan notifikasi real-time kepada orang tua dan dashboard monitoring untuk admin/guru.

Tujuan utama adalah menciptakan sistem absensi yang:
- Mudah dan cepat (siswa hanya perlu tunjukkan QR Code)
- Anti-titip absen dengan foto capture otomatis
- Memberikan transparansi kepada orang tua secara real-time
- Memudahkan admin/guru dalam monitoring kehadiran dengan bukti foto
- Response time cepat (< 2 detik dari scan hingga notifikasi)

## Glossary

- **Absensi_System**: Sistem absensi siswa berbasis QR Code Scanner dengan foto capture otomatis yang mengelola kehadiran siswa
- **QR_Scanner**: Perangkat komputer/tablet dengan kamera yang berfungsi sebagai scanner QR Code dan photo capture
- **QR_Code**: Kode unik berbentuk QR yang berisi NIS atau ID siswa, bisa berupa kartu fisik atau ditampilkan di HP siswa
- **Photo_Capture**: Proses otomatis mengambil foto siswa saat scan QR Code sebagai bukti kehadiran dan anti-titip absen
- **WhatsApp_Gateway**: Layanan gateway WhatsApp yang berjalan pada port 3001 untuk mengirim notifikasi ke orang tua
- **Student**: Siswa yang terdaftar dalam sistem dengan QR Code unik dan nomor HP orang tua
- **Parent**: Orang tua atau wali siswa yang menerima notifikasi kehadiran via WhatsApp
- **Admin**: Pengguna sistem dengan role administrator atau guru yang dapat mengelola data dan monitoring
- **Petugas**: Petugas piket atau satpam yang mengoperasikan QR Scanner dan melakukan verifikasi manual
- **Attendance_Record**: Record absensi yang mencatat waktu masuk/pulang, status kehadiran, dan link foto siswa
- **Attendance_Status**: Status kehadiran siswa yang dapat berupa: Hadir, Terlambat, atau Alpha
- **Check_In**: Proses absensi masuk siswa dengan scan QR Code di awal hari sekolah
- **Check_Out**: Proses absensi pulang siswa dengan scan QR Code di akhir hari sekolah
- **Cut_Off_Time**: Batas waktu maksimal untuk absensi masuk, setelah waktu ini siswa otomatis tercatat Alpha
- **Tolerance_Period**: Periode toleransi keterlambatan setelah jam masuk resmi (dalam menit)
- **Dashboard**: Halaman monitoring real-time untuk admin/guru dengan preview foto absensi
- **Excel_Import**: Fitur untuk mengimpor data siswa dari file Excel
- **Excel_Export**: Fitur untuk mengekspor data absensi ke file Excel

## Requirements

### Requirement 1: Registrasi Data Siswa dan Generate QR Code

**User Story:** Sebagai Admin, saya ingin mengelola data siswa dan generate QR Code unik untuk setiap siswa, sehingga siswa dapat menggunakan QR Code tersebut untuk absensi

#### Acceptance Criteria

1. THE Admin SHALL create student records with fields: Nama, NIS, Kelas, No HP Orang Tua, Foto Profil
2. THE Admin SHALL update existing student records
3. THE Admin SHALL delete student records
4. THE Admin SHALL view a list of all registered students
5. WHEN the Admin creates or updates a student record, THE Absensi_System SHALL automatically generate a unique QR Code containing the student's NIS
6. THE Absensi_System SHALL provide QR Code in multiple formats: downloadable PNG image and printable PDF (for ID card)
7. THE Absensi_System SHALL allow students to view their QR Code on a web page (for display on mobile phone)
8. WHEN the Admin imports an Excel file with student data, THE Absensi_System SHALL parse the file, create student records, and generate QR Codes for all students
9. IF an Excel import contains invalid data, THEN THE Absensi_System SHALL return a validation error message indicating which rows are invalid
10. THE Absensi_System SHALL store parent WhatsApp phone numbers in international format (e.g., 628123456789)

### Requirement 2: Absensi Masuk via QR Code Scanner dengan Foto Capture

**User Story:** Sebagai Siswa, saya ingin melakukan absensi masuk dengan menunjukkan QR Code saya ke scanner, sehingga saya dapat tercatat hadir dengan cepat dan mudah

#### Acceptance Criteria

1. WHEN a Student shows their QR Code to THE QR_Scanner, THE Absensi_System SHALL decode the QR Code to extract the student's NIS
2. THE Absensi_System SHALL automatically capture a photo of the student at the moment of QR scan using the scanner's camera
3. WHEN a valid QR Code is scanned AND the student has not checked in today, THE Absensi_System SHALL create an Attendance_Record with check-in timestamp and save the captured photo
4. WHEN a QR Code is scanned AND the student has already checked in today, THE Absensi_System SHALL display "Anda sudah absen masuk hari ini" on the scanner screen
5. WHEN an invalid or unregistered QR Code is scanned, THE Absensi_System SHALL display "QR Code tidak terdaftar"
6. WHEN a check-in is recorded, THE Absensi_System SHALL display confirmation on scanner screen within 2 seconds showing: student name, time, status, and captured photo
7. THE Absensi_System SHALL accept check-in scans between 05:00 and the Cut_Off_Time on the same day
8. WHEN a Student attempts check-in before 05:00 or after Cut_Off_Time, THE Absensi_System SHALL display "Waktu absensi masuk telah berakhir"
9. THE Absensi_System SHALL save captured photos with naming format: {NIS}_{date}_{checkin/checkout}_{timestamp}.jpg
10. THE Absensi_System SHALL store photos in the server storage and save the file path in the attendance record

### Requirement 3: Absensi Pulang via QR Code Scanner dengan Foto Capture

**User Story:** Sebagai Siswa, saya ingin melakukan absensi pulang dengan menunjukkan QR Code saya ke scanner, sehingga sistem mencatat waktu kepulangan saya

#### Acceptance Criteria

1. WHEN a Student shows their QR Code to THE QR_Scanner for check-out, THE Absensi_System SHALL decode the QR Code to extract the student's NIS
2. THE Absensi_System SHALL automatically capture a photo of the student at the moment of QR scan
3. WHEN a Student who has checked in scans their QR Code AND has not checked out today, THE Absensi_System SHALL update the Attendance_Record with check-out timestamp and save the captured photo
4. WHEN a Student who has not checked in scans their QR Code for check-out, THE Absensi_System SHALL display "Anda belum absen masuk hari ini"
5. WHEN a Student scans their QR Code AND has already checked out today, THE Absensi_System SHALL display "Anda sudah absen pulang hari ini"
6. WHEN a check-out is recorded, THE Absensi_System SHALL display confirmation on scanner screen within 2 seconds showing: student name, time, and captured photo
7. THE Absensi_System SHALL accept check-out scans only after the student's check-in time on the same day
8. THE Absensi_System SHALL save check-out photos separately from check-in photos

### Requirement 4: Penentuan Status Kehadiran

**User Story:** Sebagai Admin, saya ingin sistem otomatis menentukan status kehadiran siswa berdasarkan waktu absensi, sehingga tidak perlu input manual

#### Acceptance Criteria

1. WHEN a Student checks in before or at (check-in time + Tolerance_Period), THE Absensi_System SHALL set Attendance_Status to "Hadir"
2. WHEN a Student checks in after (check-in time + Tolerance_Period) but before Cut_Off_Time, THE Absensi_System SHALL set Attendance_Status to "Terlambat"
3. WHEN the current time reaches Cut_Off_Time AND a Student has not checked in, THE Absensi_System SHALL create an Attendance_Record with Attendance_Status "Alpha"
4. THE Absensi_System SHALL include the Attendance_Status in the confirmation message sent to the Student
5. THE Absensi_System SHALL allow Students to check in on weekends and holidays with Attendance_Status marked accordingly

### Requirement 5: Notifikasi WhatsApp kepada Orang Tua

**User Story:** Sebagai Orang Tua, saya ingin menerima notifikasi WhatsApp ketika anak saya absen masuk atau pulang, sehingga saya mengetahui keberadaan anak secara real-time

#### Acceptance Criteria

1. WHEN a Student check-in is recorded, THE Absensi_System SHALL send a WhatsApp notification to the Parent's registered phone number within 5 seconds
2. WHEN a Student check-out is recorded, THE Absensi_System SHALL send a WhatsApp notification to the Parent's registered phone number within 5 seconds
3. THE Absensi_System SHALL format the check-in notification as: "[ABSENSI] Ananda [Nama] telah tiba di sekolah pada [Waktu]. Status: [Hadir/Terlambat]"
4. THE Absensi_System SHALL format the check-out notification as: "[ABSENSI] Ananda [Nama] telah pulang dari sekolah pada [Waktu]"
5. THE Absensi_System MAY optionally include the captured photo as an attachment in the WhatsApp notification (configurable in settings)
6. IF the Parent's phone number is not registered, THE Absensi_System SHALL skip the notification without failing the attendance recording

### Requirement 6: Dashboard Real-Time untuk Admin dengan Preview Foto

**User Story:** Sebagai Admin, saya ingin melihat dashboard kehadiran real-time hari ini dengan preview foto absensi, sehingga saya dapat memonitor siswa yang sudah dan belum hadir serta memverifikasi kebenaran absensi

#### Acceptance Criteria

1. THE Dashboard SHALL display a list of all students with their current attendance status for today
2. THE Dashboard SHALL display a summary count of: Total Hadir, Total Terlambat, Total Alpha, Total Belum Absen
3. THE Dashboard SHALL refresh the attendance data every 30 seconds without page reload
4. WHEN the Admin selects a class filter, THE Dashboard SHALL display only students from that class
5. THE Dashboard SHALL display check-in and check-out timestamps for each student who has attendance records today
6. THE Dashboard SHALL indicate visually which students have not yet checked in using distinct styling
7. THE Dashboard SHALL display thumbnail preview of check-in and check-out photos for each attendance record
8. WHEN the Admin clicks on a photo thumbnail, THE Dashboard SHALL display the full-size photo in a modal/lightbox
9. THE Dashboard SHALL allow Admin to view attendance history with photos for previous dates

### Requirement 7: Export Laporan Absensi

**User Story:** Sebagai Admin, saya ingin mengekspor data absensi ke Excel, sehingga saya dapat membuat laporan dan arsip

#### Acceptance Criteria

1. WHEN the Admin requests an Excel export, THE Absensi_System SHALL generate an Excel file containing attendance data
2. THE Absensi_System SHALL include columns: Tanggal, NIS, Nama, Kelas, Jam Masuk, Jam Pulang, Status
3. WHEN the Admin applies a date range filter, THE Absensi_System SHALL export only attendance records within that range
4. WHEN the Admin applies a class filter, THE Absensi_System SHALL export only attendance records for students in that class
5. THE Absensi_System SHALL generate and download the Excel file within 10 seconds for up to 1000 records
6. THE Absensi_System SHALL name the exported file with format: "Absensi_[StartDate]_to_[EndDate].xlsx"

### Requirement 8: QR Code Scanner Interface untuk Petugas

**User Story:** Sebagai Petugas, saya ingin menggunakan interface scanner yang mudah untuk scan QR Code siswa dan melihat hasilnya dengan jelas, sehingga proses absensi berjalan lancar

#### Acceptance Criteria

1. THE QR_Scanner interface SHALL display a live camera feed showing the area being scanned
2. THE QR_Scanner interface SHALL automatically detect and decode QR Codes in the camera view
3. WHEN a QR Code is successfully scanned, THE interface SHALL display: Student name, NIS, Class, Status (Hadir/Terlambat), Timestamp, and Captured photo
4. THE QR_Scanner interface SHALL provide audio feedback (beep sound) when a QR Code is successfully scanned
5. THE QR_Scanner interface SHALL provide different audio feedback for errors (e.g., already checked in, invalid QR)
6. THE QR_Scanner interface SHALL display a "REJECT" button that allows Petugas to manually reject an attendance if something is suspicious
7. WHEN the Petugas clicks REJECT, THE Absensi_System SHALL not record the attendance and log the rejection with reason
8. THE QR_Scanner interface SHALL automatically return to ready state after 3 seconds of displaying scan result
9. THE QR_Scanner interface SHALL work on desktop browsers (Chrome, Edge) with webcam support
10. THE QR_Scanner interface SHALL display current time, date, and connectivity status

### Requirement 9: Konfigurasi Jam dan Toleransi

**User Story:** Sebagai Admin, saya ingin mengatur jam masuk, jam pulang, toleransi keterlambatan, dan cut-off time, sehingga sistem dapat disesuaikan dengan aturan sekolah

#### Acceptance Criteria

1. THE Admin SHALL configure the official check-in time (e.g., 07:00)
2. THE Admin SHALL configure the official check-out time (e.g., 15:00)
3. THE Admin SHALL configure the Tolerance_Period in minutes (e.g., 15 minutes)
4. THE Admin SHALL configure the Cut_Off_Time for automatic Alpha marking (e.g., 09:00)
5. THE Admin SHALL configure whether to include photos in parent notifications (enabled/disabled)
6. WHEN the Admin saves attendance settings, THE Absensi_System SHALL apply the new settings to all subsequent attendance recordings
7. THE Absensi_System SHALL validate that Cut_Off_Time is after (check-in time + Tolerance_Period)
8. IF the Admin enters invalid time settings, THE Absensi_System SHALL display a validation error message

### Requirement 10: Pengelolaan Data Kelas

**User Story:** Sebagai Admin, saya ingin mengelola data kelas, sehingga siswa dapat dikelompokkan berdasarkan kelas mereka

#### Acceptance Criteria

1. THE Admin SHALL create class records with fields: Nama Kelas, Tingkat, Jurusan
2. THE Admin SHALL update existing class records
3. THE Admin SHALL delete class records that have no associated students
4. THE Admin SHALL view a list of all classes
5. WHEN the Admin attempts to delete a class with enrolled students, THE Absensi_System SHALL prevent deletion and display an error message
6. THE Admin SHALL assign students to a specific class during student creation or update

### Requirement 11: Validasi Absensi Harian

**User Story:** Sebagai sistem, saya perlu memastikan integritas data absensi, sehingga tidak ada duplikasi atau data yang tidak valid

#### Acceptance Criteria

1. THE Absensi_System SHALL allow only one check-in record per Student per day
2. THE Absensi_System SHALL allow only one check-out record per Student per day
3. THE Absensi_System SHALL prevent check-out if no check-in exists for that day
4. THE Absensi_System SHALL store all timestamps in WIB (UTC+7) timezone
5. WHEN a Student attempts multiple check-ins on the same day, THE Absensi_System SHALL retain the first check-in timestamp and reject subsequent attempts
6. THE Absensi_System SHALL create a new attendance day at 00:00 WIB for the next calendar day
7. THE Absensi_System SHALL store captured photos with minimum resolution 640x480 and maximum file size 500KB (compressed)

## Out of Scope for MVP

The following features are explicitly excluded from this MVP and will be considered for future versions:

- Face recognition untuk validasi otomatis
- GPS/Location validation
- Reminder scheduler (broadcast manual)
- Izin/Sakit dengan upload surat
- Grafik dan visualisasi lanjutan
- Multi-semester atau tahun ajaran
- Integrasi dengan sistem akademik lain
- Mobile native applications
- QR Code dinamis dengan time-based token
