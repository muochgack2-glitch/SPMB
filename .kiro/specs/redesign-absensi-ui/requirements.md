# Requirements Document

## Introduction

Sistem Absensi Siswa SMK saat ini sudah memiliki backend Laravel yang lengkap dan fungsional dengan fitur QR Code scanning, manajemen data siswa/kelas, dan laporan kehadiran. Dokumen ini mendefinisikan requirements untuk complete redesign UI/UX frontend menggunakan komponen modern dari eRapor8 library yang sudah tersedia di `ui-ux-reference.md`.

Tujuan utama redesign adalah meningkatkan user experience dengan tampilan yang lebih modern, clean, responsive, dan feature-rich dengan dark mode support, real-time updates, smooth animations, dan visualisasi data yang lebih baik menggunakan ApexCharts.

## Glossary

- **Attendance_System**: Sistem absensi siswa berbasis QR Code
- **Dashboard**: Halaman utama yang menampilkan statistik dan visualisasi kehadiran
- **QR_Scanner**: Interface untuk scan QR Code siswa menggunakan camera device
- **Admin**: User yang memiliki akses penuh untuk manage data dan settings
- **Petugas**: User yang bertugas melakukan scanning kehadiran siswa
- **Dark_Mode**: Mode tampilan dengan background gelap untuk mengurangi eye strain
- **Toast_Notification**: Notifikasi popup sementara yang muncul untuk feedback user action
- **Lightbox**: Modal untuk preview image dalam ukuran besar dengan zoom functionality
- **Status_Badge**: Visual indicator untuk status kehadiran dengan color coding
- **ApexCharts**: Library JavaScript untuk membuat interactive charts dan graphs
- **eRapor8_Component**: Komponen UI modern yang sudah tersedia di ui-ux-reference.md
- **Real_Time_Update**: Update data secara otomatis tanpa refresh halaman menggunakan polling
- **Responsive_Design**: Desain yang menyesuaikan dengan berbagai ukuran layar (mobile, tablet, desktop)
- **Check_In**: Proses pencatatan waktu kedatangan siswa
- **Check_Out**: Proses pencatatan waktu pulang siswa
- **Date_Range_Picker**: Komponen untuk memilih rentang tanggal untuk filter laporan
- **Attendance_Record**: Data kehadiran siswa yang berisi check-in time, check-out time, status, dan foto
- **Photo_Capture**: Proses pengambilan foto siswa saat check-in/check-out
- **Export_Excel**: Fitur untuk export laporan ke format Excel
- **Attendance_Status**: Status kehadiran siswa (hadir, terlambat, alpha, izin, sakit)


## Requirements

### Requirement 1: Dashboard Page Redesign

**User Story:** As an Admin, I want to see a modern dashboard with real-time statistics and interactive charts, so that I can monitor attendance at a glance

#### Acceptance Criteria

1. THE Dashboard SHALL display total students count using eRapor8 Stat_Card component with icon
2. THE Dashboard SHALL display attendance statistics (hadir, terlambat, alpha, izin) using color-coded Stat_Card components
3. THE Dashboard SHALL display attendance percentage with trend indicator (up/down arrow)
4. WHEN data is loaded, THE Dashboard SHALL render ApexCharts for attendance visualization (line chart untuk trend, donut chart untuk status breakdown)
5. THE Dashboard SHALL display recent activity list showing latest 10 check-in/check-out records with timestamp and status badge
6. WHEN user selects date filter, THE Dashboard SHALL update statistics and charts for selected date
7. WHEN user selects class filter, THE Dashboard SHALL update statistics and charts for selected class
8. THE Dashboard SHALL auto-refresh statistics every 30 seconds using AJAX polling
9. THE Dashboard SHALL display loading skeleton during data fetch
10. THE Dashboard SHALL use 4-column grid layout on desktop, 2-column on tablet, 1-column on mobile


### Requirement 2: QR Scanner Interface Redesign

**User Story:** As a Petugas, I want to use a smooth camera interface to scan student QR codes, so that I can quickly process check-in/check-out

#### Acceptance Criteria

1. THE QR_Scanner SHALL display camera preview in centered card with rounded corners
2. THE QR_Scanner SHALL display scanning frame overlay with animated corners
3. WHEN QR code is detected, THE QR_Scanner SHALL decode the NIS and display student preview card
4. THE Student_Preview_Card SHALL display student photo, name, NIS, class, and current attendance status
5. THE QR_Scanner SHALL display action buttons (Check-In/Check-Out) with gradient styling based on current status
6. WHEN Check-In button is clicked, THE QR_Scanner SHALL capture photo using device camera
7. WHEN photo is captured, THE QR_Scanner SHALL send photo_base64 and NIS to backend API
8. WHEN scan is successful, THE QR_Scanner SHALL show success Toast_Notification with student name and status
9. WHEN scan fails, THE QR_Scanner SHALL show error Toast_Notification with error message
10. THE QR_Scanner SHALL display scan history panel showing last 5 scanned students with timestamp
11. THE QR_Scanner SHALL integrate html5-qrcode library for QR scanning functionality
12. THE QR_Scanner SHALL request camera permission on page load
13. WHEN camera permission is denied, THE QR_Scanner SHALL display error message with retry button
14. THE QR_Scanner SHALL display loading spinner during photo capture and API call
15. THE QR_Scanner SHALL be responsive and functional on mobile devices


### Requirement 3: Data Siswa CRUD Redesign

**User Story:** As an Admin, I want to manage student data with modern table and form interfaces, so that I can efficiently add, edit, and delete student records

#### Acceptance Criteria

1. THE Data_Siswa_Page SHALL display students table using eRapor8 modern table component with striped rows
2. THE Students_Table SHALL display columns: foto, NIS, nama, kelas, no HP ortu, QR code, status aktif, actions
3. THE Students_Table SHALL display foto profil as thumbnail with hover zoom effect
4. THE Students_Table SHALL display Status_Badge for is_active field (green for active, gray for inactive)
5. THE Students_Table SHALL include search input to filter by NIS or nama
6. THE Students_Table SHALL include filter dropdown to filter by kelas
7. THE Students_Table SHALL include sortable headers for NIS, nama, and kelas columns
8. THE Students_Table SHALL display pagination with page numbers and total records info
9. WHEN Add Student button is clicked, THE Attendance_System SHALL show modal form with eRapor8 form components
10. THE Student_Form SHALL include input fields: NIS, nama, kelas dropdown, no HP ortu, foto profil upload
11. WHEN foto profil is uploaded, THE Student_Form SHALL show image preview below upload input
12. WHEN form is submitted, THE Attendance_System SHALL validate required fields and show validation errors
13. WHEN student is created successfully, THE Attendance_System SHALL show success Toast_Notification and refresh table
14. WHEN Edit button is clicked, THE Attendance_System SHALL show modal form pre-filled with student data
15. WHEN Delete button is clicked, THE Attendance_System SHALL show confirmation modal with student name
16. WHEN delete is confirmed, THE Attendance_System SHALL soft-delete student and show success notification
17. THE Data_Siswa_Page SHALL include bulk import button to upload Excel file
18. WHEN QR Code icon is clicked, THE Attendance_System SHALL show modal with QR code image and download button
19. THE Students_Table SHALL display empty state message when no students found
20. THE Data_Siswa_Page SHALL be responsive on mobile with horizontal scroll for table


### Requirement 4: Data Kelas CRUD Redesign

**User Story:** As an Admin, I want to manage class data with modern interface, so that I can organize students by class

#### Acceptance Criteria

1. THE Data_Kelas_Page SHALL display classes table using eRapor8 modern table component
2. THE Classes_Table SHALL display columns: nama kelas, tingkat, jurusan, wali kelas, jumlah siswa, status aktif, actions
3. THE Classes_Table SHALL display Status_Badge for is_active field (green for active, gray for inactive)
4. THE Classes_Table SHALL include search input to filter by nama kelas
5. THE Classes_Table SHALL include sortable headers for all columns
6. WHEN Add Class button is clicked, THE Attendance_System SHALL show modal form
7. THE Class_Form SHALL include input fields: nama kelas, tingkat dropdown, jurusan, wali kelas (optional)
8. WHEN form is submitted, THE Attendance_System SHALL validate required fields
9. WHEN class is created successfully, THE Attendance_System SHALL show success Toast_Notification and refresh table
10. WHEN Edit button is clicked, THE Attendance_System SHALL show modal form pre-filled with class data
11. WHEN Delete button is clicked, THE Attendance_System SHALL show confirmation modal
12. WHEN delete is confirmed and class has students, THE Attendance_System SHALL show error message
13. WHEN delete is confirmed and class has no students, THE Attendance_System SHALL delete class
14. THE Classes_Table SHALL display student count with clickable link to filtered student list
15. THE Data_Kelas_Page SHALL be responsive on mobile devices


### Requirement 5: Laporan Kehadiran Redesign

**User Story:** As an Admin, I want to generate and view attendance reports with filters and export functionality, so that I can analyze attendance patterns

#### Acceptance Criteria

1. THE Laporan_Page SHALL display filter card with date range picker, class dropdown, and status dropdown
2. THE Date_Range_Picker SHALL allow user to select start date and end date
3. THE Date_Range_Picker SHALL include preset buttons (Today, This Week, This Month)
4. WHEN filter is applied, THE Attendance_System SHALL fetch attendance records via AJAX
5. THE Laporan_Page SHALL display attendance records table with columns: tanggal, NIS, nama, kelas, check-in time, check-out time, status, foto
6. THE Attendance_Records_Table SHALL display Status_Badge with color coding (hadir=green, terlambat=yellow, alpha=red, izin=blue, sakit=purple)
7. WHEN foto column is clicked, THE Attendance_System SHALL open Lightbox modal showing check-in and check-out photos
8. THE Lightbox SHALL display photo with zoom controls and close button
9. THE Laporan_Page SHALL display summary statistics card above table (total records, hadir count, terlambat count, alpha count, izin count)
10. THE Laporan_Page SHALL include Export to Excel button with gradient styling
11. WHEN Export button is clicked, THE Attendance_System SHALL download Excel file with filtered data
12. THE Attendance_Records_Table SHALL include pagination for large datasets
13. THE Attendance_Records_Table SHALL display loading spinner during data fetch
14. THE Attendance_Records_Table SHALL display empty state when no records found for filter
15. THE Laporan_Page SHALL be responsive with horizontal scroll for table on mobile


### Requirement 6: Settings Page Redesign

**User Story:** As an Admin, I want to configure system settings with modern interface, so that I can customize attendance rules

#### Acceptance Criteria

1. THE Settings_Page SHALL display settings form using eRapor8 Section_Card component
2. THE Settings_Form SHALL include time picker for check-in start time
3. THE Settings_Form SHALL include time picker for check-in cutoff time (batas tepat waktu)
4. THE Settings_Form SHALL include time picker for check-in end time (batas akhir absen)
5. THE Settings_Form SHALL include time picker for check-out time
6. THE Settings_Form SHALL include number input for late tolerance minutes
7. THE Settings_Form SHALL display help text below each input explaining the setting
8. WHEN form is submitted, THE Attendance_System SHALL validate time values (start < cutoff < end)
9. WHEN validation fails, THE Settings_Form SHALL display error messages below fields
10. WHEN settings are saved successfully, THE Attendance_System SHALL show success Toast_Notification
11. THE Settings_Form SHALL include Reset to Default button
12. WHEN Reset button is clicked, THE Attendance_System SHALL show confirmation modal
13. WHEN reset is confirmed, THE Attendance_System SHALL restore default settings
14. THE Settings_Page SHALL display current settings values on page load
15. THE Settings_Form SHALL use eRapor8 input components with proper styling


### Requirement 7: Dark Mode Toggle

**User Story:** As a User, I want to toggle between light and dark mode, so that I can reduce eye strain in low-light environments

#### Acceptance Criteria

1. THE Navigation_Menu SHALL display dark mode toggle switch in header
2. THE Dark_Mode_Toggle SHALL display moon icon for dark mode and sun icon for light mode
3. WHEN toggle is clicked, THE Attendance_System SHALL apply dark mode CSS variables to document root
4. THE Dark_Mode SHALL change background colors, text colors, card backgrounds, and border colors
5. THE Attendance_System SHALL save dark mode preference to localStorage
6. WHEN page loads, THE Attendance_System SHALL read localStorage and apply saved dark mode preference
7. THE Dark_Mode SHALL maintain all component colors with proper contrast ratios
8. THE Dark_Mode SHALL apply to all pages (Dashboard, Scanner, Data Siswa, Data Kelas, Laporan, Settings)
9. THE Dark_Mode_Toggle SHALL have smooth transition animation (0.3s ease)
10. THE Dark_Mode SHALL use dark background (#1a1a1a), dark card background (#2d2d2d), and light text (#e0e0e0)


### Requirement 8: Responsive Design Implementation

**User Story:** As a User, I want to access the system from mobile devices, so that I can use it anywhere

#### Acceptance Criteria

1. THE Attendance_System SHALL use Bootstrap 5 responsive grid system for all layouts
2. THE Dashboard SHALL display 4 columns on desktop (≥1200px), 2 columns on tablet (768px-1199px), 1 column on mobile (<768px)
3. THE Navigation_Menu SHALL collapse to hamburger menu on mobile devices
4. THE Tables SHALL have horizontal scroll on mobile when content exceeds viewport width
5. THE Modal_Forms SHALL resize to fit mobile viewport with proper padding
6. THE QR_Scanner SHALL display camera preview in full width on mobile
7. THE Buttons SHALL have minimum 44px touch target size on mobile for accessibility
8. THE Form_Inputs SHALL have comfortable touch targets with 16px font size minimum
9. THE Charts SHALL resize responsively and maintain readability on small screens
10. WHEN device orientation changes, THE Attendance_System SHALL adjust layout accordingly
11. THE Attendance_System SHALL use viewport meta tag for proper mobile scaling
12. THE Images SHALL use responsive sizing with max-width 100%
13. THE Typography SHALL scale appropriately for mobile readability
14. THE Spacing SHALL reduce on mobile to maximize content area
15. THE Attendance_System SHALL be tested on devices with viewport widths 320px, 768px, 1024px, 1920px


### Requirement 9: Animations and Transitions

**User Story:** As a User, I want smooth animations and transitions, so that the interface feels modern and polished

#### Acceptance Criteria

1. THE Page_Content SHALL fade in with 0.3s ease-in animation on load
2. THE Cards SHALL have hover effect with translateY(-4px) and shadow increase
3. THE Buttons SHALL have hover effect with translateY(-2px) and shadow increase
4. THE Modal SHALL slide up with 0.35s ease-out animation on open
5. THE Modal SHALL fade out with 0.28s ease-in animation on close
6. THE Toast_Notifications SHALL slide in from right with 0.3s ease-out animation
7. THE Toast_Notifications SHALL slide out to right with 0.3s ease-in animation on dismiss
8. THE Table_Rows SHALL have hover background color transition with 0.2s ease
9. THE Status_Badges SHALL pulse animation on status change
10. THE Loading_Spinners SHALL use CSS keyframe rotation animation
11. THE Count_Numbers SHALL animate from 0 to value with JavaScript countup effect
12. THE Charts SHALL animate on render with 1.5s ease-out animation
13. THE Form_Inputs SHALL have focus state transition with 0.2s ease for border and shadow
14. THE Sidebar_Navigation SHALL slide in/out with 0.3s ease transition
15. THE Image_Lightbox SHALL zoom in with 0.3s ease-out animation on open


### Requirement 10: Toast Notifications System

**User Story:** As a User, I want to receive visual feedback for my actions, so that I know if operations succeeded or failed

#### Acceptance Criteria

1. THE Attendance_System SHALL display Toast_Notification for successful check-in with green background
2. THE Attendance_System SHALL display Toast_Notification for successful check-out with green background
3. THE Attendance_System SHALL display Toast_Notification for failed scan with red background
4. THE Attendance_System SHALL display Toast_Notification for successful CRUD operations with green background
5. THE Attendance_System SHALL display Toast_Notification for validation errors with red background
6. THE Attendance_System SHALL display Toast_Notification for info messages with blue background
7. THE Toast_Notification SHALL display icon corresponding to type (check circle for success, x circle for error, info circle for info)
8. THE Toast_Notification SHALL display message text in white color
9. THE Toast_Notification SHALL auto-dismiss after 5 seconds
10. THE Toast_Notification SHALL have close button for manual dismiss
11. THE Toast_Notification SHALL display in top-right position
12. THE Toast_Notification SHALL stack multiple notifications vertically
13. THE Toast_Notification SHALL have progress bar showing time until auto-dismiss
14. THE Attendance_System SHALL use Toast global API from eRapor8 components
15. THE Toast_Notification SHALL be accessible with screen readers (ARIA live region)


### Requirement 11: ApexCharts Integration

**User Story:** As an Admin, I want to see visual data representations, so that I can understand attendance patterns quickly

#### Acceptance Criteria

1. THE Dashboard SHALL render Line_Chart showing attendance trend for last 7 days
2. THE Line_Chart SHALL display lines for hadir, terlambat, alpha with different colors
3. THE Line_Chart SHALL have smooth curved lines with gradient fill
4. THE Line_Chart SHALL have interactive tooltips showing exact values on hover
5. THE Dashboard SHALL render Donut_Chart showing attendance status breakdown
6. THE Donut_Chart SHALL use color coding (hadir=green, terlambat=yellow, alpha=red, izin=blue)
7. THE Donut_Chart SHALL display percentage labels inside segments
8. THE Donut_Chart SHALL have legend showing status labels with counts
9. THE Dashboard SHALL render Bar_Chart showing attendance by class
10. THE Bar_Chart SHALL display horizontal bars sorted by attendance percentage
11. THE Charts SHALL use ApexCharts library version 3.x
12. THE Charts SHALL animate on initial render with 1.5s duration
13. THE Charts SHALL be responsive and resize on window resize
14. THE Charts SHALL have loading skeleton while data is being fetched
15. THE Charts SHALL display "No data available" message when data is empty


### Requirement 12: Photo Lightbox Component

**User Story:** As a User, I want to view check-in/check-out photos in larger size, so that I can verify student identity

#### Acceptance Criteria

1. WHEN photo thumbnail is clicked in attendance record, THE Attendance_System SHALL open Lightbox modal
2. THE Lightbox SHALL display photo in large size (max 90% viewport width/height)
3. THE Lightbox SHALL display check-in photo and check-out photo side by side if both exist
4. THE Lightbox SHALL have dark backdrop with 80% opacity
5. THE Lightbox SHALL have close button (X) in top-right corner
6. WHEN backdrop is clicked, THE Lightbox SHALL close
7. WHEN ESC key is pressed, THE Lightbox SHALL close
8. THE Lightbox SHALL display student name, NIS, and timestamp below photo
9. THE Lightbox SHALL have zoom controls (zoom in, zoom out, reset)
10. THE Lightbox SHALL support pinch-to-zoom on mobile devices
11. THE Lightbox SHALL display loading spinner while photo is loading
12. WHEN photo fails to load, THE Lightbox SHALL display error message
13. THE Lightbox SHALL have navigation arrows if multiple photos exist
14. THE Lightbox SHALL animate zoom in with 0.3s ease-out on open
15. THE Lightbox SHALL be accessible with keyboard navigation (ESC to close, arrow keys for navigation)


### Requirement 13: Date Range Picker Component

**User Story:** As an Admin, I want to easily select date ranges for reports, so that I can filter attendance data efficiently

#### Acceptance Criteria

1. THE Date_Range_Picker SHALL display two date inputs (start date and end date) with calendar icon
2. WHEN calendar icon is clicked, THE Date_Range_Picker SHALL open calendar dropdown
3. THE Calendar_Dropdown SHALL display two months side by side
4. THE Date_Range_Picker SHALL highlight selected date range in calendar
5. THE Date_Range_Picker SHALL include preset buttons (Today, Yesterday, Last 7 Days, Last 30 Days, This Month, Last Month)
6. WHEN preset button is clicked, THE Date_Range_Picker SHALL set start and end dates automatically
7. THE Date_Range_Picker SHALL validate that end date is not before start date
8. WHEN invalid date range is selected, THE Date_Range_Picker SHALL display error message
9. THE Date_Range_Picker SHALL format dates as DD/MM/YYYY in input display
10. THE Date_Range_Picker SHALL send dates as YYYY-MM-DD format to backend
11. THE Calendar_Dropdown SHALL close when date range is selected
12. THE Calendar_Dropdown SHALL close when clicked outside
13. THE Date_Range_Picker SHALL have Clear button to reset selection
14. THE Date_Range_Picker SHALL be keyboard accessible (arrow keys for date navigation)
15. THE Date_Range_Picker SHALL work on mobile devices with touch interaction


### Requirement 14: Status Badge Component

**User Story:** As a User, I want to see color-coded status indicators, so that I can quickly identify attendance status

#### Acceptance Criteria

1. THE Status_Badge SHALL display "Hadir" with green background (#10b981) and white text
2. THE Status_Badge SHALL display "Terlambat" with yellow background (#f59e0b) and dark text
3. THE Status_Badge SHALL display "Alpha" with red background (#ef4444) and white text
4. THE Status_Badge SHALL display "Izin" with blue background (#3b82f6) and white text
5. THE Status_Badge SHALL display "Sakit" with purple background (#8b5cf6) and white text
6. THE Status_Badge SHALL have rounded corners (border-radius: 9999px for pill shape)
7. THE Status_Badge SHALL have padding of 4px horizontal and 8px vertical
8. THE Status_Badge SHALL have font size of 12px and font weight of 600
9. THE Status_Badge SHALL display uppercase text
10. THE Status_Badge SHALL have icon before text (check for hadir, clock for terlambat, x for alpha, info for izin, heart for sakit)
11. THE Status_Badge SHALL have hover effect with opacity reduction to 0.8
12. THE Status_Badge SHALL be reusable Blade component accepting status as parameter
13. THE Status_Badge SHALL use eRapor8 color variables from CSS
14. THE Status_Badge SHALL have transition animation for hover (0.2s ease)
15. THE Status_Badge SHALL maintain minimum contrast ratio of 4.5:1 for accessibility


### Requirement 15: Real-Time Dashboard Updates

**User Story:** As an Admin, I want the dashboard to update automatically, so that I always see current attendance data

#### Acceptance Criteria

1. THE Dashboard SHALL poll attendance statistics API every 30 seconds using JavaScript setInterval
2. WHEN new data is received, THE Dashboard SHALL update stat card values with count-up animation
3. WHEN new data is received, THE Dashboard SHALL update charts with smooth transition
4. THE Dashboard SHALL display last updated timestamp in footer
5. THE Dashboard SHALL pause polling when browser tab is inactive to save resources
6. THE Dashboard SHALL resume polling when browser tab becomes active
7. WHEN API request fails, THE Dashboard SHALL retry after 10 seconds
8. THE Dashboard SHALL display connection error Toast_Notification when API fails 3 consecutive times
9. THE Dashboard SHALL have manual refresh button to force data update
10. WHEN manual refresh button is clicked, THE Dashboard SHALL show loading spinner and fetch latest data
11. THE Dashboard SHALL compare new data with old data and highlight changed values with flash animation
12. THE Recent_Activity_List SHALL prepend new check-in/check-out records at top
13. THE Recent_Activity_List SHALL fade in new items with slide-down animation
14. THE Real_Time_Updates SHALL not disrupt user interaction (charts, filters)
15. THE Dashboard SHALL use optimistic UI update for better perceived performance


### Requirement 16: Component Library Integration

**User Story:** As a Developer, I want to use eRapor8 components consistently, so that UI is cohesive and maintainable

#### Acceptance Criteria

1. THE Attendance_System SHALL use eRapor8 Card component for all card layouts
2. THE Attendance_System SHALL use eRapor8 Stat_Card component for dashboard statistics
3. THE Attendance_System SHALL use eRapor8 Button component for all buttons with gradient styling
4. THE Attendance_System SHALL use eRapor8 Form_Group component for all form fields
5. THE Attendance_System SHALL use eRapor8 Input component for text inputs with icon support
6. THE Attendance_System SHALL use eRapor8 Select component for dropdown selects
7. THE Attendance_System SHALL use eRapor8 Table component for all data tables
8. THE Attendance_System SHALL use eRapor8 Modal component for all modals
9. THE Attendance_System SHALL use eRapor8 Alert component for inline alerts
10. THE Attendance_System SHALL use eRapor8 Toast component for notifications
11. THE Attendance_System SHALL use eRapor8 Section_Card component for settings sections
12. THE Attendance_System SHALL use eRapor8 Empty_State component when data is empty
13. THE Attendance_System SHALL use eRapor8 CSS variables for colors, spacing, and typography
14. THE Attendance_System SHALL include eRapor8 utility classes (modern-utilities.css)
15. THE Attendance_System SHALL follow eRapor8 component documentation from ui-ux-reference.md


### Requirement 17: Color Scheme and Branding

**User Story:** As a User, I want consistent visual branding, so that the application looks professional and cohesive

#### Acceptance Criteria

1. THE Attendance_System SHALL use primary color blue (#1e3a8a, #1e40af) for branding elements
2. THE Attendance_System SHALL use secondary color light blue (#3b82f6, #60a5fa) for accents
3. THE Attendance_System SHALL use gradient background (from #1e3a8a to #3b82f6) for primary buttons
4. THE Attendance_System SHALL use white background (#ffffff) for light mode cards
5. THE Attendance_System SHALL use dark background (#1a1a1a) for dark mode page background
6. THE Attendance_System SHALL use dark card background (#2d2d2d) for dark mode cards
7. THE Attendance_System SHALL use status colors (green=#10b981, yellow=#f59e0b, red=#ef4444, blue=#3b82f6, purple=#8b5cf6)
8. THE Attendance_System SHALL use gray scale for text (900 for headings, 700 for body, 500 for secondary)
9. THE Attendance_System SHALL use border color (#e5e7eb) in light mode and (#374151) in dark mode
10. THE Attendance_System SHALL use shadow colors with appropriate opacity
11. THE Attendance_System SHALL use accent gradient in header and navigation
12. THE Attendance_System SHALL maintain consistent spacing using 8px base system
13. THE Attendance_System SHALL use consistent border radius (8px for cards, 6px for buttons, 4px for inputs)
14. THE Attendance_System SHALL use Font Awesome 6 icons consistently
15. THE Attendance_System SHALL use system font stack (system-ui, -apple-system, sans-serif) for typography


### Requirement 18: Loading States and Skeletons

**User Story:** As a User, I want to see loading indicators, so that I know the system is processing

#### Acceptance Criteria

1. THE Dashboard SHALL display skeleton cards while statistics are loading
2. THE Skeleton_Card SHALL have pulsing gray background animation
3. THE Tables SHALL display skeleton rows (5 rows) while data is loading
4. THE Skeleton_Row SHALL mimic actual table structure with gray bars
5. THE Charts SHALL display skeleton placeholder while data is loading
6. THE QR_Scanner SHALL display spinner while camera is initializing
7. THE Photo_Capture SHALL display spinner overlay while photo is being processed
8. THE Form_Submit SHALL show loading spinner inside button and disable button during submission
9. THE Button_Spinner SHALL replace button text or show next to text based on button size
10. THE Modal SHALL display loading spinner in body while async data is loading
11. THE Image_Thumbnails SHALL display skeleton placeholder while image is loading
12. THE Date_Range_Picker SHALL show loading state while fetching data for date range
13. THE Export_Excel SHALL show loading spinner in button with text "Exporting..." during download
14. THE Loading_Spinner SHALL use CSS animation with rotation and color matching theme
15. THE Skeleton_Loaders SHALL maintain layout structure to prevent content jumping


### Requirement 19: Navigation and Layout Structure

**User Story:** As a User, I want easy navigation between pages, so that I can access features quickly

#### Acceptance Criteria

1. THE Navigation_Menu SHALL display vertical sidebar with logo at top
2. THE Sidebar SHALL include navigation items: Dashboard, QR Scanner, Data Siswa, Data Kelas, Laporan, Settings
3. THE Navigation_Item SHALL display icon and text label
4. THE Navigation_Item SHALL highlight active page with gradient background and bold text
5. THE Sidebar SHALL have collapse toggle button at bottom
6. WHEN collapse toggle is clicked, THE Sidebar SHALL collapse to icon-only view
7. THE Collapsed_Sidebar SHALL show tooltips on icon hover
8. THE Sidebar SHALL save collapse state to localStorage
9. THE Header SHALL display page title, user info, and dark mode toggle
10. THE User_Info SHALL display user avatar, name, and role
11. THE User_Info SHALL have dropdown menu with Profile and Logout links
12. THE Main_Content_Area SHALL have max-width container with padding
13. THE Page_Header SHALL display breadcrumb navigation
14. THE Footer SHALL display copyright text and last updated timestamp
15. THE Layout SHALL be responsive with hamburger menu on mobile replacing sidebar


### Requirement 20: Accessibility Compliance

**User Story:** As a User with disabilities, I want accessible interface, so that I can use the system effectively

#### Acceptance Criteria

1. THE Attendance_System SHALL use semantic HTML5 elements (header, nav, main, section, article, footer)
2. THE Form_Inputs SHALL have associated label elements with for attribute
3. THE Buttons SHALL have descriptive text or aria-label for icon-only buttons
4. THE Images SHALL have alt text describing image content
5. THE Status_Badges SHALL have aria-label describing status for screen readers
6. THE Modal SHALL trap focus within modal when open
7. THE Modal SHALL return focus to trigger element when closed
8. THE Modal SHALL be closable with ESC key
9. THE Navigation SHALL be keyboard accessible with Tab and arrow keys
10. THE Interactive_Elements SHALL have visible focus indicators with outline or ring
11. THE Color_Contrast SHALL meet WCAG AA standard (4.5:1 for normal text, 3:1 for large text)
12. THE Toast_Notifications SHALL use ARIA live region for screen reader announcements
13. THE Charts SHALL have data table alternative for screen readers
14. THE Date_Range_Picker SHALL be keyboard navigable
15. THE Attendance_System SHALL support browser zoom up to 200% without breaking layout

