# Technical Design Document

## Overview

This document outlines the technical design for the complete UI/UX redesign of the Student Attendance System. The redesign focuses on modernizing the frontend using eRapor8 component library while maintaining the existing Laravel backend infrastructure.

### Design Goals

- Modern, premium UI with blue gradient color scheme
- Responsive design (mobile, tablet, desktop)
- Dark mode support with user preference persistence
- Smooth animations and transitions
- Real-time dashboard updates
- Enhanced user experience with interactive components

### Key Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 13.8 | Backend framework |
| Livewire | 3.x | Full-stack reactive components |
| Alpine.js | 3.14.0 | UI interactions & state management |
| Tailwind CSS | 4.0 | Styling framework |
| ApexCharts | 3.50.0 | Data visualization |
| html5-qrcode | 2.3.8 | QR code scanning |
| Flatpickr | 4.6.13 | Date range picker |
| Font Awesome | 6.x | Icon library |
| Vite | 8.0 | Asset bundling |

---

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser Client                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Alpine.js   │  │  Livewire    │  │  Vanilla JS  │      │
│  │  (UI Logic)  │  │  (Components)│  │  (Libraries) │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│         │                  │                  │              │
│         └──────────────────┴──────────────────┘              │
│                           │                                  │
└───────────────────────────┼──────────────────────────────────┘
                            │ HTTP/WebSocket
┌───────────────────────────┼──────────────────────────────────┐
│                    Laravel Backend                           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Controllers Layer                        │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌───────────┐  │   │
│  │  │  Dashboard   │  │  Scanner     │  │  Reports  │  │   │
│  │  │  Controller  │  │  Controller  │  │ Controller│  │   │
│  │  └──────────────┘  └──────────────┘  └───────────┘  │   │
│  └──────────────────────────────────────────────────────┘   │
│                           │                                  │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Services Layer                           │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌───────────┐  │   │
│  │  │ Attendance   │  │ Photo        │  │  Status   │  │   │
│  │  │  Service     │  │  Service     │  │  Service  │  │   │
│  │  └──────────────┘  └──────────────┘  └───────────┘  │   │
│  └──────────────────────────────────────────────────────┘   │
│                           │                                  │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                 Models Layer                          │   │
│  │  ┌────────┐  ┌────────┐  ┌────────┐  ┌──────────┐  │   │
│  │  │Student │  │ Class  │  │ Record │  │ Setting  │  │   │
│  │  └────────┘  └────────┘  └────────┘  └──────────┘  │   │
│  └──────────────────────────────────────────────────────┘   │
│                           │                                  │
└───────────────────────────┼──────────────────────────────────┘
                            │
┌───────────────────────────┼──────────────────────────────────┐
│                     MySQL Database                           │
└──────────────────────────────────────────────────────────────┘
```


### Component Architecture

```
resources/views/
├── layouts/
│   ├── app.blade.php              # Main layout wrapper
│   ├── sidebar.blade.php          # Collapsible sidebar navigation
│   └── navbar.blade.php           # Top navigation bar
│
├── components/                     # eRapor8 Blade Components
│   ├── card.blade.php
│   ├── stat-card.blade.php
│   ├── button.blade.php
│   ├── modal.blade.php
│   ├── form-group.blade.php
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── table.blade.php
│   ├── alert.blade.php
│   ├── toast-container.blade.php
│   ├── status-badge.blade.php     # NEW: Status indicators
│   ├── date-range-picker.blade.php # NEW: Date picker
│   └── photo-lightbox.blade.php   # NEW: Image viewer
│
├── livewire/                       # Livewire Components
│   ├── attendance-dashboard.blade.php
│   ├── student-table.blade.php
│   ├── class-table.blade.php
│   ├── report-generator.blade.php
│   └── settings-form.blade.php
│
└── attendance/                     # Page Views
    ├── dashboard/
    │   └── index.blade.php
    ├── scanner/
    │   └── index.blade.php
    ├── students/
    │   ├── index.blade.php
    │   └── form.blade.php
    ├── classes/
    │   ├── index.blade.php
    │   └── form.blade.php
    ├── reports/
    │   └── index.blade.php
    └── settings/
        └── index.blade.php
```

---

## Components and Interfaces

### Blade Components (eRapor8 Library)

All reusable UI components following eRapor8 design system.

**Card Components:**
- `<x-card>` - Base card wrapper
- `<x-stat-card>` - Statistics display with icon, value, trend
- `<x-section-card>` - Content section with header
- `<x-info-card>` - Alert/notification cards
- `<x-empty-state>` - No data placeholder
- `<x-action-card>` - Interactive navigation cards

**Form Components:**
- `<x-form-group>` - Form field wrapper with label and error
- `<x-input>` - Text input with icon support
- `<x-textarea>` - Multi-line text input
- `<x-select>` - Dropdown select with custom styling
- `<x-checkbox>` - Custom checkbox
- `<x-radio>` - Custom radio button
- `<x-switch>` - Toggle switch (3 sizes)
- `<x-file-upload>` - File upload with preview

**Table Components:**
- `<x-table>` - Modern table with striped rows
- `<x-table-actions>` - Action buttons per row
- `<x-sortable-header>` - Sortable column header
- `<x-table-search>` - Search input for table
- `<x-table-filter>` - Filter dropdown for table
- `<x-pagination>` - Pagination with page info

**Feedback Components:**
- `<x-alert>` - Inline alert (info, success, warning, danger)
- `<x-toast-container>` - Toast notification system
- `<x-status-badge>` - Status indicator with color
- `<x-button>` - Button with 8 variants
- `<x-icon-button>` - Icon-only button
- `<x-modal>` - Modal dialog with backdrop

**New Components:**
- `<x-date-range-picker>` - Date range selection
- `<x-photo-lightbox>` - Image viewer with zoom

### Livewire Components

**AttendanceDashboard**
- **Purpose:** Real-time dashboard with statistics and charts
- **Public Properties:**
  - `$date` (string) - Selected date filter
  - `$classId` (int|null) - Selected class filter  
  - `$stats` (array) - Attendance statistics
  - `$recentActivity` (Collection) - Latest check-ins
- **Public Methods:**
  - `mount()` - Initialize component
  - `refreshStats()` - Fetch latest data
  - `setDate($date)` - Update date filter
  - `setClass($classId)` - Update class filter
- **Events Emitted:** None
- **Events Listened:** `refresh-dashboard`

**StudentTable**
- **Purpose:** Student CRUD with search, filter, and pagination
- **Public Properties:**
  - `$search` (string) - Search query
  - `$classFilter` (int|null) - Class filter
  - `$sortField` (string) - Sort column
  - `$sortDirection` (string) - asc/desc
  - `$perPage` (int) - Items per page
- **Public Methods:**
  - `sortBy($field)` - Toggle sort direction
  - `deleteStudent($id)` - Soft delete student
  - `toggleStatus($id)` - Toggle active status
- **Computed Properties:**
  - `getStudentsProperty()` - Filtered & paginated students
- **Events Emitted:** `student-deleted`, `student-updated`
- **Events Listened:** `refresh-table`

**ClassTable**
- **Purpose:** Class CRUD management
- **Public Properties:**
  - `$search` (string) - Search query
  - `$sortField` (string) - Sort column
  - `$sortDirection` (string) - asc/desc
- **Public Methods:**
  - `sortBy($field)` - Toggle sort direction
  - `deleteClass($id)` - Delete class
- **Computed Properties:**
  - `getClassesProperty()` - Filtered classes
- **Events Emitted:** `class-deleted`, `class-updated`
- **Events Listened:** `refresh-table`

**ReportGenerator**
- **Purpose:** Generate attendance reports with filters
- **Public Properties:**
  - `$startDate` (string) - Start date
  - `$endDate` (string) - End date
  - `$classId` (int|null) - Class filter
  - `$statusFilter` (string|null) - Status filter
  - `$records` (Collection) - Attendance records
  - `$summary` (array) - Statistics summary
  - `$loading` (bool) - Loading state
- **Public Methods:**
  - `generateReport()` - Fetch and display report
  - `exportExcel()` - Download Excel file
  - `calculateSummary()` - Compute statistics
- **Events Emitted:** None
- **Events Listened:** `date-range-changed`

**SettingsForm**
- **Purpose:** System settings configuration
- **Public Properties:**
  - `$checkInStart` (string) - Check-in start time
  - `$checkInCutoff` (string) - Late cutoff time
  - `$checkInEnd` (string) - Check-in end time
  - `$checkOutTime` (string) - Check-out time
  - `$lateTolerance` (int) - Minutes tolerance
- **Public Methods:**
  - `save()` - Save settings
  - `resetToDefaults()` - Restore defaults
- **Events Emitted:** `settings-saved`
- **Events Listened:** None

### JavaScript Modules

**QR Scanner Module (`public/js/qr-scanner.js`)**
- **Exports:** `QRScanner` class
- **Methods:**
  - `init()` - Initialize camera and scanner
  - `start()` - Start scanning
  - `stop()` - Stop scanning
  - `onScanSuccess(decodedText)` - Handle successful scan
  - `capturePhoto()` - Capture photo from video stream
  - `submitAttendance(nis, photo, action)` - Submit to API
- **Dependencies:** html5-qrcode

**Charts Module (`public/js/charts.js`)**
- **Exports:** Chart initialization functions
- **Functions:**
  - `initLineTrendChart(element, data)` - Create line chart
  - `initDonutStatusChart(element, data)` - Create donut chart
  - `initBarClassChart(element, data)` - Create bar chart
  - `updateChart(chart, newData)` - Update existing chart
- **Dependencies:** apexcharts

**Toast Module (`public/js/toast.js`)**
- **Exports:** `Toast` global object
- **Methods:**
  - `success(message, title)` - Show success toast
  - `error(message, title)` - Show error toast
  - `info(message, title)` - Show info toast
  - `warning(message, title)` - Show warning toast
  - `show(message, title, type)` - Generic show method
- **Dependencies:** Alpine.js

**Lightbox Module (`public/js/lightbox.js`)**
- **Exports:** Alpine.js `photoLightbox` data component
- **Properties:**
  - `open` (bool) - Lightbox visibility
  - `currentPhoto` (object) - Current photo data
  - `photos` (array) - All photos
  - `currentIndex` (int) - Current photo index
  - `zoom` (float) - Zoom level
- **Methods:**
  - `show(photos, index)` - Open lightbox
  - `close()` - Close lightbox
  - `next()` - Next photo
  - `prev()` - Previous photo
  - `zoomIn()` / `zoomOut()` / `resetZoom()` - Zoom controls
- **Dependencies:** Alpine.js

**Date Picker Module (`public/js/date-picker.js`)**
- **Exports:** Date picker initialization
- **Functions:**
  - `initDateRangePicker(element, options)` - Initialize picker
  - `setPreset(presetName)` - Apply date preset
- **Dependencies:** flatpickr

### API Endpoints

**Attendance Dashboard API**
- `GET /api/attendance/stats?date={date}&class_id={id}` - Get statistics
- `GET /api/attendance/today-summary?class_id={id}` - Get today's summary
- `GET /attendance/photo/{recordId}/{type}` - Get check-in/out photo

**Attendance Scanner API**
- `POST /api/attendance/scan` - Process QR scan
  - Body: `{ nis, photo_base64, action }`
  - Response: `{ success, message, data }`
- `POST /api/attendance/reject` - Reject scan
  - Body: `{ nis, reason }`

**Students API**
- `GET /api/students?search={query}&class_id={id}` - List students
- `POST /api/students` - Create student
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student
- `GET /api/students/{id}/qr-code` - Get QR code image

**Classes API**
- `GET /api/classes` - List classes
- `POST /api/classes` - Create class
- `PUT /api/classes/{id}` - Update class
- `DELETE /api/classes/{id}` - Delete class

**Reports API**
- `POST /api/reports/generate` - Generate report
  - Body: `{ start_date, end_date, class_id, status }`
- `POST /api/reports/export-excel` - Export to Excel
  - Body: `{ start_date, end_date, class_id, status }`

**Settings API**
- `GET /api/settings` - Get all settings
- `PUT /api/settings` - Update settings
  - Body: `{ check_in_start, check_in_cutoff, ... }`
- `POST /api/settings/reset` - Reset to defaults

---

## Data Models

### 1. Layout System

#### 1.1 Main Layout (app.blade.php)

**Purpose:** Root layout wrapper with sidebar, navbar, and content area

**Structure:**

```blade
<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: false }" x-init="darkMode = localStorage.getItem('darkMode') === 'true'" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi Siswa')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    
    <!-- Sidebar -->
    @include('layouts.sidebar')
    
    <!-- Main Content Wrapper -->
    <div x-data="{ sidebarOpen: true }" :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="transition-all duration-300">
        
        <!-- Top Navbar -->
        @include('layouts.navbar')
        
        <!-- Page Content -->
        <main class="pt-20 min-h-screen p-6">
            @yield('content')
        </main>
    </div>
    
    <!-- Toast Container -->
    <x-toast-container />
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
```

#### 1.2 Sidebar Component (sidebar.blade.php)

**Features:**
- Collapsible (wide ↔ narrow)
- Active menu highlighting
- Gradient background (blue-900 → blue-800)
- Dark mode support
- Tooltips on collapsed state
- localStorage persistence

**Alpine.js State:**
```javascript
{
    sidebarOpen: true,  // Read from localStorage
    activeMenu: 'dashboard',
    darkMode: false
}
```


**Navigation Items:**
- Dashboard (fas fa-chart-line)
- QR Scanner (fas fa-qrcode)
- Data Siswa (fas fa-users)
- Data Kelas (fas fa-school)
- Laporan (fas fa-file-alt)
- Settings (fas fa-cog)

**Bottom Actions:**
- Dark Mode Toggle (fas fa-moon / fa-sun)
- Collapse Toggle (fas fa-chevron-left / fa-chevron-right)

#### 1.3 Top Navbar (navbar.blade.php)

**Features:**
- Page title & breadcrumb
- Search bar (global)
- Notification bell with badge
- User profile dropdown

**Right Section Items:**
- Notification icon + badge count
- User avatar + name + dropdown
  - Profile
  - Settings  
  - Logout

---

### 2. Dashboard Components

#### 2.1 Livewire Dashboard Component

**File:** `app/Livewire/AttendanceDashboard.php`

**Properties:**
```php
public $date;           // Selected date filter
public $classId;        // Selected class filter
public $stats;          // Statistics data
public $recentActivity; // Latest check-ins
```

**Methods:**
```php
public function mount()              // Initialize data
public function refreshStats()       // Fetch latest stats
public function setDate($date)       // Update date filter
public function setClass($classId)   // Update class filter
```


**Polling:**
```blade
<div wire:poll.30s="refreshStats">
    <!-- Stats cards auto-refresh every 30 seconds -->
</div>
```

#### 2.2 Stat Cards Layout

**Grid System:**
- Desktop (≥1200px): 4 columns
- Tablet (768-1199px): 2 columns
- Mobile (<768px): 1 column

**Stat Card Components:**
1. Total Students (blue, fas fa-users)
2. Hadir (green, fas fa-check-circle)
3. Terlambat (yellow, fas fa-clock)
4. Alpha (red, fas fa-times-circle)

**Card Structure:**
```blade
<x-stat-card 
    title="Hadir"
    value="{{ $stats['hadir'] }}"
    trend="+5.2%"
    trend-up="true"
    icon="fas fa-check-circle"
    color="green"
/>
```

#### 2.3 ApexCharts Integration

**Charts Directory:** `public/js/charts/`

**Dashboard Charts:**

**a) Line Chart - Attendance Trend (7 days)**
```javascript
// charts/attendance-trend.js
const options = {
    series: [
        { name: 'Hadir', data: [85, 90, 88, 92, 87, 91, 89] },
        { name: 'Terlambat', data: [10, 8, 9, 6, 8, 7, 9] },
        { name: 'Alpha', data: [5, 2, 3, 2, 5, 2, 2] }
    ],
    chart: {
        type: 'line',
        height: 350,
        animations: { enabled: true, speed: 1500 },
        toolbar: { show: false }
    },
    stroke: { curve: 'smooth', width: 3 },
    colors: ['#10b981', '#f59e0b', '#ef4444']
};
```


**b) Donut Chart - Status Breakdown**
```javascript
// charts/status-donut.js
const options = {
    series: [stats.hadir, stats.terlambat, stats.alpha, stats.izin],
    chart: {
        type: 'donut',
        height: 300
    },
    labels: ['Hadir', 'Terlambat', 'Alpha', 'Izin'],
    colors: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
    legend: { position: 'bottom' }
};
```

**c) Bar Chart - Attendance by Class**
```javascript
// charts/class-bar.js
const options = {
    series: [{ name: 'Percentage', data: [95, 88, 92, 85, 90] }],
    chart: {
        type: 'bar',
        height: 350,
        horizontal: true
    },
    xaxis: {
        categories: ['X RPL 1', 'X RPL 2', 'XI RPL 1', 'XI RPL 2', 'XII RPL']
    },
    colors: ['#3b82f6']
};
```

---

### 3. QR Scanner Component

#### 3.1 Scanner Interface

**File:** `public/js/qr-scanner.js`

**html5-qrcode Configuration:**
```javascript
const config = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    aspectRatio: 1.0
};

const html5QrCode = new Html5Qrcode("qr-reader");

html5QrCode.start(
    { facingMode: "environment" }, // Back camera
    config,
    onScanSuccess,
    onScanFailure
);
```


**Scan Success Handler:**
```javascript
async function onScanSuccess(decodedText) {
    // 1. Parse QR code to get NIS
    const nis = decodedText;
    
    // 2. Fetch student info
    const student = await fetchStudent(nis);
    
    // 3. Show student preview card
    displayStudentPreview(student);
    
    // 4. Determine action (check-in or check-out)
    const action = student.hasCheckedInToday ? 'check_out' : 'check_in';
    
    // 5. Enable action button
    enableActionButton(action);
}
```

**Photo Capture Flow:**
```javascript
async function capturePhoto() {
    // 1. Request camera access
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    
    // 2. Capture frame from video
    const canvas = document.createElement('canvas');
    const video = document.getElementById('camera-preview');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    // 3. Convert to base64
    const photoBase64 = canvas.toDataURL('image/jpeg', 0.8);
    
    // 4. Send to backend
    await submitAttendance(nis, photoBase64, action);
}
```

#### 3.2 Scanner UI States

**States:**
1. **Initializing:** Show spinner while requesting camera permission
2. **Scanning:** Display camera preview with animated scan frame
3. **Student Preview:** Show student card after successful scan
4. **Capturing Photo:** Show loading overlay during photo capture
5. **Success:** Show success toast and clear preview
6. **Error:** Show error toast with retry button

---

### 4. CRUD Components (Students & Classes)

#### 4.1 Livewire Table Component

**File:** `app/Livewire/StudentTable.php`


**Properties:**
```php
public $search = '';
public $classFilter = null;
public $sortField = 'nama';
public $sortDirection = 'asc';
public $perPage = 10;
```

**Methods:**
```php
public function updatingSearch()              // Reset pagination on search
public function sortBy($field)                // Toggle sort direction
public function getStudentsProperty()         // Computed property for students
public function deleteStudent($id)            // Soft delete student
public function toggleStatus($id)             // Toggle active status
```

**Computed Property:**
```php
public function getStudentsProperty()
{
    return AttendanceStudent::query()
        ->with('kelas')
        ->when($this->search, fn($q) => 
            $q->where('nis', 'like', '%'.$this->search.'%')
              ->orWhere('nama', 'like', '%'.$this->search.'%')
        )
        ->when($this->classFilter, fn($q) => 
            $q->where('kelas_id', $this->classFilter)
        )
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);
}
```

#### 4.2 Modal Form Component

**Alpine.js Modal State:**
```javascript
{
    modalOpen: false,
    formData: {
        nis: '',
        nama: '',
        kelas_id: null,
        no_hp_ortu: '',
        foto_profil: null
    },
    editMode: false
}
```

**Form Validation:**
- Client-side: Alpine.js validation
- Server-side: Laravel Form Request


**Submit Handler:**
```javascript
async function submitForm() {
    const formData = new FormData();
    Object.keys(this.formData).forEach(key => {
        formData.append(key, this.formData[key]);
    });
    
    const endpoint = this.editMode 
        ? `/students/${this.formData.id}` 
        : '/students';
    
    const response = await fetch(endpoint, {
        method: this.editMode ? 'PUT' : 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    if (response.ok) {
        Toast.success('Data siswa berhasil disimpan');
        this.modalOpen = false;
        Livewire.dispatch('refresh-table');
    } else {
        const errors = await response.json();
        Toast.error(errors.message);
    }
}
```

---

### 5. Reports Component

#### 5.1 Date Range Picker Component

**Library:** Flatpickr

**Configuration:**
```javascript
// public/js/date-picker.js
flatpickr("#dateRange", {
    mode: "range",
    dateFormat: "Y-m-d",
    maxDate: "today",
    locale: "id",
    onChange: function(selectedDates) {
        if (selectedDates.length === 2) {
            Livewire.dispatch('date-range-changed', {
                start: selectedDates[0],
                end: selectedDates[1]
            });
        }
    }
});
```

**Preset Buttons:**
```javascript
const presets = {
    'today': [new Date(), new Date()],
    'yesterday': [yesterday, yesterday],
    'last7days': [sevenDaysAgo, today],
    'last30days': [thirtyDaysAgo, today],
    'thisMonth': [startOfMonth, endOfMonth],
    'lastMonth': [startOfLastMonth, endOfLastMonth]
};
```


#### 5.2 Report Generator (Livewire)

**File:** `app/Livewire/ReportGenerator.php`

**Properties:**
```php
public $startDate;
public $endDate;
public $classId;
public $statusFilter;
public $records = [];
public $summary = [];
public $loading = false;
```

**Methods:**
```php
public function generateReport()
{
    $this->validate([
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
    ]);
    
    $this->loading = true;
    
    $query = AttendanceRecord::with('student.kelas')
        ->whereBetween('date', [$this->startDate, $this->endDate]);
    
    if ($this->classId) {
        $query->whereHas('student', fn($q) => $q->where('kelas_id', $this->classId));
    }
    
    if ($this->statusFilter) {
        $query->where('status', $this->statusFilter);
    }
    
    $this->records = $query->orderBy('date', 'desc')->get();
    $this->calculateSummary();
    
    $this->loading = false;
}

public function exportExcel()
{
    return Excel::download(
        new AttendanceExport($this->records),
        'laporan-absensi-'.$this->startDate.'-'.$this->endDate.'.xlsx'
    );
}
```

---

### 6. Reusable UI Components

#### 6.1 Status Badge Component

**File:** `resources/views/components/status-badge.blade.php`

**Props:**
- `status` (string): hadir, terlambat, alpha, izin, sakit


**Color Mapping:**
```php
$colors = [
    'hadir' => 'bg-green-500 text-white',
    'terlambat' => 'bg-yellow-500 text-gray-900',
    'alpha' => 'bg-red-500 text-white',
    'izin' => 'bg-blue-500 text-white',
    'sakit' => 'bg-purple-500 text-white',
];

$icons = [
    'hadir' => 'fa-check-circle',
    'terlambat' => 'fa-clock',
    'alpha' => 'fa-times-circle',
    'izin' => 'fa-info-circle',
    'sakit' => 'fa-heart',
];
```

**Usage:**
```blade
<x-status-badge status="hadir" />
<x-status-badge status="terlambat" />
```

#### 6.2 Photo Lightbox Component

**File:** `public/js/lightbox.js`

**Alpine.js Component:**
```javascript
Alpine.data('photoLightbox', () => ({
    open: false,
    currentPhoto: null,
    photos: [],
    currentIndex: 0,
    zoom: 1,
    
    show(photos, index = 0) {
        this.photos = photos;
        this.currentIndex = index;
        this.currentPhoto = photos[index];
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    
    close() {
        this.open = false;
        this.zoom = 1;
        document.body.style.overflow = '';
    },
    
    next() {
        this.currentIndex = (this.currentIndex + 1) % this.photos.length;
        this.currentPhoto = this.photos[this.currentIndex];
    },
    
    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.photos.length) % this.photos.length;
        this.currentPhoto = this.photos[this.currentIndex];
    },
    
    zoomIn() { this.zoom = Math.min(this.zoom + 0.25, 3); },
    zoomOut() { this.zoom = Math.max(this.zoom - 0.25, 0.5); },
    resetZoom() { this.zoom = 1; }
}));
```


#### 6.3 Toast Notification System

**File:** `public/js/toast.js`

**Global Toast API:**
```javascript
window.Toast = {
    success(message, title = 'Berhasil') {
        this.show(message, title, 'success');
    },
    
    error(message, title = 'Error') {
        this.show(message, title, 'error');
    },
    
    info(message, title = 'Info') {
        this.show(message, title, 'info');
    },
    
    warning(message, title = 'Peringatan') {
        this.show(message, title, 'warning');
    },
    
    show(message, title, type) {
        const toast = {
            id: Date.now(),
            title,
            message,
            type,
            show: true
        };
        
        window.dispatchEvent(new CustomEvent('toast-show', { detail: toast }));
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            window.dispatchEvent(new CustomEvent('toast-hide', { detail: toast.id }));
        }, 5000);
    }
};
```

**Alpine.js Toast Container:**
```javascript
Alpine.data('toastContainer', () => ({
    toasts: [],
    
    init() {
        window.addEventListener('toast-show', (e) => {
            this.toasts.push(e.detail);
        });
        
        window.addEventListener('toast-hide', (e) => {
            const index = this.toasts.findIndex(t => t.id === e.detail);
            if (index > -1) {
                this.toasts.splice(index, 1);
            }
        });
    },
    
    remove(id) {
        window.dispatchEvent(new CustomEvent('toast-hide', { detail: id }));
    }
}));
```


---

## Color System & Theming

### Color Palette

#### Light Mode
```css
:root {
    /* Primary Colors - Blue Theme */
    --color-primary-900: #1e3a8a;  /* Dark blue */
    --color-primary-800: #1e40af;
    --color-primary-700: #1d4ed8;
    --color-primary-600: #2563eb;
    --color-primary-500: #3b82f6;  /* Main blue */
    --color-primary-400: #60a5fa;  /* Light blue */
    --color-primary-300: #93c5fd;
    --color-primary-200: #bfdbfe;
    --color-primary-100: #dbeafe;
    
    /* Status Colors */
    --color-success: #10b981;      /* Green - Hadir */
    --color-warning: #f59e0b;      /* Yellow - Terlambat */
    --color-danger: #ef4444;       /* Red - Alpha */
    --color-info: #3b82f6;         /* Blue - Izin */
    --color-purple: #8b5cf6;       /* Purple - Sakit */
    
    /* Neutral Colors */
    --color-gray-50: #f9fafb;
    --color-gray-100: #f3f4f6;
    --color-gray-200: #e5e7eb;
    --color-gray-300: #d1d5db;
    --color-gray-500: #6b7280;
    --color-gray-700: #374151;
    --color-gray-900: #111827;
    
    /* Background */
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --bg-sidebar: linear-gradient(180deg, #1e3a8a, #1e40af);
    
    /* Text */
    --text-primary: #111827;
    --text-secondary: #6b7280;
    --text-inverse: #ffffff;
}
```

#### Dark Mode
```css
.dark {
    /* Background */
    --bg-primary: #1a1a1a;
    --bg-secondary: #2d2d2d;
    --bg-card: #242424;
    --bg-sidebar: linear-gradient(180deg, #0f172a, #1e293b);
    
    /* Text */
    --text-primary: #e0e0e0;
    --text-secondary: #a0a0a0;
    
    /* Borders */
    --border-color: #374151;
}
```


### Gradient System

**Primary Gradient (Buttons, Sidebar):**
```css
background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
```

**Hover Gradient:**
```css
background: linear-gradient(135deg, #1e40af 0%, #60a5fa 100%);
```

**Text Gradient (Active Menu):**
```css
background: linear-gradient(135deg, #60a5fa, #3b82f6);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
```

---

## Animation & Transition System

### Transition Speeds
```css
--transition-fast: 150ms;
--transition-base: 200ms;
--transition-slow: 300ms;
--transition-slower: 500ms;
```

### Common Animations

#### Fade In
```css
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}
```

#### Slide Up (Modal)
```css
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-enter {
    animation: slideUp 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
```


#### Count Up (Statistics)
```javascript
function countUp(element, target, duration = 1500) {
    const start = parseInt(element.textContent) || 0;
    const increment = (target - start) / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}
```

#### Pulse (Badge)
```css
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.badge-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

#### Scan Line (QR Scanner)
```css
@keyframes scanLine {
    0% {
        transform: translateY(-100%);
    }
    100% {
        transform: translateY(100%);
    }
}

.scan-line {
    animation: scanLine 2s linear infinite;
}
```

---

## Responsive Design Breakpoints

### Breakpoint System
```javascript
const breakpoints = {
    sm: '640px',   // Mobile landscape
    md: '768px',   // Tablet
    lg: '1024px',  // Desktop
    xl: '1280px',  // Large desktop
    '2xl': '1536px' // Extra large
};
```

### Layout Adjustments


#### Dashboard Grid
```css
/* Desktop (≥1200px) */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

/* Tablet (768px - 1199px) */
@media (max-width: 1199px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile (<768px) */
@media (max-width: 767px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
```

#### Sidebar Behavior
```css
/* Desktop: Always visible */
@media (min-width: 1024px) {
    .sidebar {
        position: fixed;
        left: 0;
    }
}

/* Mobile: Overlay when open */
@media (max-width: 1023px) {
    .sidebar {
        position: fixed;
        left: 0;
        transform: translateX(-100%);
        transition: transform 0.3s;
    }
    
    .sidebar.open {
        transform: translateX(0);
        z-index: 50;
    }
    
    .sidebar-backdrop {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 40;
    }
}
```

#### Table Responsive
```blade
<div class="overflow-x-auto">
    <table class="min-w-full">
        <!-- Table content -->
    </table>
</div>
```

---

## State Management

### Alpine.js Global Store

**File:** `resources/js/stores/app.js`

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.store('app', {
        // Sidebar state
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' || true,
        
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            localStorage.setItem('sidebarOpen', this.sidebarOpen);
        },
        
        // Dark mode state
        darkMode: localStorage.getItem('darkMode') === 'true' || false,
        
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            document.documentElement.classList.toggle('dark', this.darkMode);
        },
        
        initDarkMode() {
            document.documentElement.classList.toggle('dark', this.darkMode);
        },
        
        // Active menu
        activeMenu: window.location.pathname.split('/')[1] || 'dashboard',
        
        setActiveMenu(menu) {
            this.activeMenu = menu;
        }
    });
});
```


### Livewire Events

**Event Communication:**
```php
// Dispatch from component
$this->dispatch('refresh-table');
$this->dispatch('student-saved', ['id' => $student->id]);

// Listen in view
<div wire:on="refresh-table">
    <!-- Content updates automatically -->
</div>
```

**Browser Events (Alpine ↔ Livewire):**
```javascript
// From Alpine to Livewire
Livewire.dispatch('date-range-changed', { 
    start: '2024-01-01', 
    end: '2024-01-31' 
});

// From Livewire to Alpine
window.addEventListener('show-toast', event => {
    Toast.success(event.detail.message);
});
```

---

## Data Flow Architecture

### Dashboard Real-Time Updates

```
┌─────────────────────────────────────────────────────────┐
│                  Browser (Alpine.js)                     │
│  ┌────────────────────────────────────────────────┐    │
│  │  Every 30 seconds (wire:poll.30s)              │    │
│  └────────────────┬───────────────────────────────┘    │
│                   │                                      │
└───────────────────┼──────────────────────────────────────┘
                    │ HTTP Request
┌───────────────────▼──────────────────────────────────────┐
│           Livewire Component (PHP)                       │
│  ┌────────────────────────────────────────────────┐    │
│  │  refreshStats() method                          │    │
│  │  - Fetch latest attendance data                │    │
│  │  - Calculate statistics                        │    │
│  │  - Update component properties                 │    │
│  └────────────────┬───────────────────────────────┘    │
│                   │                                      │
└───────────────────┼──────────────────────────────────────┘
                    │ Query Database
┌───────────────────▼──────────────────────────────────────┐
│              Laravel Backend                             │
│  ┌────────────────────────────────────────────────┐    │
│  │  AttendanceService                              │    │
│  │  - getAttendanceStats()                        │    │
│  │  - getTodayAttendance()                        │    │
│  └────────────────┬───────────────────────────────┘    │
│                   │                                      │
└───────────────────┼──────────────────────────────────────┘
                    │
┌───────────────────▼──────────────────────────────────────┐
│                MySQL Database                            │
│  - attendance_records                                    │
│  - attendance_students                                   │
│  - attendance_classes                                    │
└──────────────────────────────────────────────────────────┘
```


### QR Scanner Flow

```
┌─────────────────────────────────────────────────────────┐
│             User Opens Scanner Page                      │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│       Request Camera Permission (html5-qrcode)          │
└────────────────┬────────────────────────────────────────┘
                 │
         ┌───────┴───────┐
         │  Granted?     │
         └───┬───────┬───┘
             │       │
        Yes  │       │ No
             │       │
┌────────────▼───┐  │  ┌──────────────────────────────┐
│  Start Camera  │  │  │  Show Error Message          │
│  Preview       │  │  │  + Retry Button              │
└────────────┬───┘  │  └──────────────────────────────┘
                 │       │
                 │       └─────────────────┐
┌────────────────▼────────────────────┐    │
│     QR Code Detected                │    │
│     - Decode NIS                    │    │
│     - Fetch Student Info via API    │    │
└────────────────┬────────────────────┘    │
                 │                          │
┌────────────────▼────────────────────┐    │
│   Display Student Preview Card      │    │
│   - Photo, Name, Class              │    │
│   - Current Status                  │    │
│   - Action Button (Check-In/Out)    │    │
└────────────────┬────────────────────┘    │
                 │ User clicks action       │
┌────────────────▼────────────────────┐    │
│      Capture Photo (Camera)         │    │
│      - Convert to Base64            │    │
└────────────────┬────────────────────┘    │
                 │                          │
┌────────────────▼────────────────────┐    │
│  POST /api/attendance/scan          │    │
│  {                                  │    │
│    nis: "12345",                    │    │
│    photo_base64: "data:image...",   │    │
│    action: "check_in"               │    │
│  }                                  │    │
└────────────────┬────────────────────┘    │
                 │                          │
         ┌───────┴───────┐                 │
         │  Success?     │                 │
         └───┬───────┬───┘                 │
             │       │                      │
        Yes  │       │ No                   │
             │       │                      │
┌────────────▼───┐  │  ┌──────────────┐   │
│ Show Success   │  │  │ Show Error   │   │
│ Toast + Clear  │  │  │ Toast        │   │
│ Preview        │  │  └──────────────┘   │
└────────────────┘  │                      │
                    └──────────────────────┘
```


---

## Performance Optimization

### Asset Loading Strategy

#### Critical CSS (Inline in <head>)
```html
<style>
    /* Critical above-the-fold styles */
    body { margin: 0; font-family: system-ui, sans-serif; }
    .sidebar { /* Critical sidebar styles */ }
    .navbar { /* Critical navbar styles */ }
</style>
```

#### Lazy Load Libraries
```javascript
// Load ApexCharts only on dashboard
if (document.querySelector('[data-chart]')) {
    import('apexcharts').then(module => {
        window.ApexCharts = module.default;
        initCharts();
    });
}

// Load html5-qrcode only on scanner page
if (document.querySelector('#qr-reader')) {
    import('html5-qrcode').then(module => {
        window.Html5Qrcode = module.Html5Qrcode;
        initScanner();
    });
}
```

### Image Optimization

```blade
<!-- Use WebP with fallback -->
<picture>
    <source srcset="{{ $student->foto_profil_webp }}" type="image/webp">
    <img src="{{ $student->foto_profil }}" alt="{{ $student->nama }}" loading="lazy">
</picture>

<!-- Thumbnail for table listing -->
<img 
    src="{{ Storage::url($student->foto_profil) }}" 
    alt="{{ $student->nama }}"
    class="h-10 w-10 rounded-full object-cover"
    loading="lazy"
>
```

### Database Query Optimization

```php
// Eager load relationships
$students = AttendanceStudent::with(['kelas', 'attendanceRecords' => function($q) {
    $q->whereDate('date', today())->latest();
}])->paginate(15);

// Select specific columns
$stats = AttendanceRecord::select('status', DB::raw('count(*) as count'))
    ->whereDate('date', today())
    ->groupBy('status')
    ->get();

// Index important columns
Schema::table('attendance_records', function (Blueprint $table) {
    $table->index(['date', 'status']);
    $table->index('student_id');
});
```


### Livewire Performance

```php
// Use lazy loading for heavy data
public function getStudentsProperty()
{
    return once(function () {
        return AttendanceStudent::with('kelas')
            ->where('is_active', true)
            ->get();
    });
}

// Defer loading for non-critical data
<div wire:init="loadRecentActivity">
    @if($recentActivity)
        <!-- Display activity -->
    @else
        <!-- Loading skeleton -->
    @endif
</div>

// Optimize polling with targeted updates
<div wire:poll.30s.keep-alive="refreshStats">
    <!-- Only this section re-renders -->
</div>
```

---

## Security Considerations

### CSRF Protection

```blade
<!-- All forms include CSRF token -->
<form method="POST" action="/students">
    @csrf
    <!-- Form fields -->
</form>

<!-- AJAX requests -->
<script>
fetch('/api/attendance/scan', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(data)
});
</script>
```

### XSS Prevention

```blade
<!-- Always escape output -->
<p>{{ $student->nama }}</p>  <!-- Safe -->

<!-- Raw HTML only when necessary and sanitized -->
<div>{!! Purifier::clean($content) !!}</div>
```

### SQL Injection Prevention

```php
// Use Eloquent/Query Builder (auto-escaping)
AttendanceStudent::where('nis', $request->nis)->first(); // Safe

// Use parameter binding for raw queries
DB::select('SELECT * FROM students WHERE nis = ?', [$nis]); // Safe
```

### File Upload Validation

```php
$request->validate([
    'foto_profil' => 'required|image|mimes:jpeg,png,jpg|max:2048'
]);

// Store with random name
$path = $request->file('foto_profil')->store('students', 'public');
```


---

## Testing Strategy

### Component Testing

#### Livewire Component Tests
```php
// tests/Feature/Livewire/StudentTableTest.php
use Livewire\Livewire;

test('can search students', function () {
    $student = AttendanceStudent::factory()->create(['nama' => 'John Doe']);
    
    Livewire::test(StudentTable::class)
        ->set('search', 'John')
        ->assertSee('John Doe');
});

test('can filter by class', function () {
    $class = AttendanceClass::factory()->create();
    $student = AttendanceStudent::factory()->create(['kelas_id' => $class->id]);
    
    Livewire::test(StudentTable::class)
        ->set('classFilter', $class->id)
        ->assertSee($student->nama);
});
```

#### JavaScript Component Tests
```javascript
// tests/js/toast.test.js
import { Toast } from '@/js/toast';

describe('Toast', () => {
    test('shows success toast', () => {
        Toast.success('Test message');
        expect(document.querySelector('.toast-success')).toBeInTheDocument();
    });
    
    test('auto-dismisses after 5 seconds', async () => {
        Toast.success('Test message');
        await new Promise(r => setTimeout(r, 5100));
        expect(document.querySelector('.toast-success')).not.toBeInTheDocument();
    });
});
```

### Browser Testing (Dusk)

```php
// tests/Browser/AttendanceTest.php
test('admin can view dashboard', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs(User::find(1))
                ->visit('/dashboard')
                ->assertSee('Dashboard Absensi')
                ->assertPresent('[data-stat-card]');
    });
});

test('petugas can scan QR code', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs(User::find(1))
                ->visit('/scanner')
                ->assertPresent('#qr-reader')
                ->assertVisible('.scan-frame');
    });
});
```


---

## Deployment & Build Process

### NPM Scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "lint": "eslint resources/js",
    "format": "prettier --write resources/js/**/*.js"
  }
}
```

### Vite Configuration

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/charts.js',
                'resources/js/qr-scanner.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs'],
                    'charts': ['apexcharts'],
                    'qr': ['html5-qrcode'],
                    'date': ['flatpickr'],
                }
            }
        }
    }
});
```

### Production Build

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize images
php artisan optimize:images
```

---

## Accessibility (WCAG 2.1 AA)

### Keyboard Navigation

```html
<!-- All interactive elements are keyboard accessible -->
<button 
    type="button" 
    @click="toggleSidebar"
    @keydown.enter="toggleSidebar"
    tabindex="0"
>
    <i class="fas fa-bars"></i>
</button>

<!-- Skip to main content -->
<a href="#main-content" class="sr-only focus:not-sr-only">
    Skip to main content
</a>
```


### ARIA Labels

```html
<!-- Screen reader friendly -->
<button aria-label="Toggle dark mode">
    <i class="fas fa-moon"></i>
</button>

<!-- Live regions for dynamic content -->
<div role="status" aria-live="polite" aria-atomic="true">
    {{ $successMessage }}
</div>

<!-- Form labels -->
<label for="nis" class="block text-sm font-medium">
    NIS
</label>
<input 
    id="nis" 
    type="text" 
    aria-required="true"
    aria-describedby="nis-help"
>
<p id="nis-help" class="text-xs text-gray-500">
    Nomor Induk Siswa 10 digit
</p>
```

### Color Contrast

```css
/* Ensure 4.5:1 contrast ratio for text */
.text-primary { color: #111827; } /* 21:1 on white */
.text-secondary { color: #6b7280; } /* 5.4:1 on white */

/* Status badges meet contrast requirements */
.badge-success { 
    background: #10b981; /* Green */
    color: #ffffff;      /* 4.5:1 contrast */
}
```

### Focus Indicators

```css
/* Visible focus styles */
button:focus,
input:focus,
select:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Focus within for complex components */
.card:focus-within {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}
```

---

## Error Handling

### Frontend Error Handling

```javascript
// Global error handler
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
    Toast.error('Terjadi kesalahan. Silakan refresh halaman.');
});

// Async error handling
async function fetchData() {
    try {
        const response = await fetch('/api/data');
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        Toast.error('Gagal memuat data. Silakan coba lagi.');
        return null;
    }
}
```


### Backend Error Handling

```php
// API error responses
public function scan(ScanAttendanceRequest $request): JsonResponse
{
    try {
        $result = $this->attendanceService->processScan(
            $request->nis,
            $request->photo_base64,
            $request->action
        );
        
        return response()->json($result, $result['success'] ? 200 : 422);
        
    } catch (\Exception $e) {
        Log::error('Scan error: ' . $e->getMessage(), [
            'nis' => $request->nis,
            'action' => $request->action
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
        ], 500);
    }
}
```

### Validation Error Display

```blade
<!-- Form errors -->
@error('nis')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror

<!-- Livewire validation -->
<div>
    <input wire:model="nis" type="text" />
    @error('nis') 
        <span class="text-red-500 text-xs">{{ $message }}</span> 
    @enderror
</div>
```

---

## Migration Path

### Phase 1: Setup & Dependencies (Week 1)
1. Install NPM packages (Alpine.js, ApexCharts, html5-qrcode, Flatpickr)
2. Install Composer packages (Livewire)
3. Setup Vite configuration
4. Create directory structure

### Phase 2: Core Components (Week 1-2)
1. Create layout system (sidebar, navbar)
2. Implement dark mode toggle
3. Build eRapor8 Blade components
4. Setup color system & animations

### Phase 3: Dashboard (Week 2-3)
1. Create Livewire dashboard component
2. Integrate ApexCharts
3. Implement stat cards
4. Add real-time polling

### Phase 4: QR Scanner (Week 3)
1. Integrate html5-qrcode library
2. Build camera interface
3. Implement photo capture
4. Connect to backend API

### Phase 5: CRUD Pages (Week 4-5)
1. Create student table component
2. Build class table component
3. Implement modal forms
4. Add search & filters

### Phase 6: Reports (Week 5-6)
1. Integrate Flatpickr date picker
2. Build report generator
3. Implement Excel export
4. Add photo lightbox

### Phase 7: Testing & Polish (Week 6-7)
1. Write component tests
2. Perform browser testing
3. Optimize performance
4. Fix accessibility issues

### Phase 8: Deployment (Week 7)
1. Build production assets
2. Optimize Laravel caching
3. Deploy to production
4. Monitor & fix issues

---

## Maintenance & Updates

### Update Strategy

```bash
# Weekly dependency updates
npm update
composer update

# Check for security vulnerabilities
npm audit
composer audit

# Update Livewire
composer update livewire/livewire

# Update Tailwind CSS
npm update tailwindcss
```

### Monitoring

```php
// Log Livewire performance
Livewire::listen('component.dehydrate', function ($component) {
    if ($component->renderTime > 1000) {
        Log::warning('Slow Livewire component', [
            'component' => get_class($component),
            'time' => $component->renderTime
        ]);
    }
});

// Track API errors
Log::channel('api')->error('Scanner error', [
    'nis' => $nis,
    'error' => $exception->getMessage()
]);
```

---

## Conclusion

This technical design provides a comprehensive blueprint for redesigning the Student Attendance System UI/UX. The design leverages modern web technologies while maintaining compatibility with the existing Laravel backend.

### Key Benefits

1. **Modern UI:** Clean, professional interface with blue gradient theme
2. **Responsive:** Works seamlessly on mobile, tablet, and desktop
3. **Dark Mode:** Reduces eye strain and saves battery
4. **Real-time:** Live dashboard updates every 30 seconds
5. **Performance:** Optimized asset loading and database queries
6. **Accessibility:** WCAG 2.1 AA compliant
7. **Maintainable:** Component-based architecture with clear separation of concerns
8. **Scalable:** Can easily add new features and pages

### Next Steps

1. Review and approve design document
2. Create detailed task list from design
3. Begin implementation in phases
4. Conduct regular code reviews
5. Test thoroughly before deployment

---

**Document Version:** 1.0  
**Last Updated:** {{ date('Y-m-d') }}  
**Status:** Ready for Implementation

### AttendanceStudent Model

**Table:** `attendance_students`

**Columns:**
- `id` (bigint, PK)
- `nis` (string, unique, indexed)
- `nama` (string)
- `kelas_id` (bigint, FK → attendance_classes)
- `no_hp_ortu` (string, nullable)
- `qr_code_path` (string, nullable)
- `foto_profil` (string, nullable)
- `is_active` (boolean, default true)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships:**
- `belongsTo` AttendanceClass (kelas)
- `hasMany` AttendanceRecord (attendanceRecords)
- `hasMany` AttendanceLog (logs)

**Computed Properties:**
- `qr_code_url` - Full URL to QR code image
- `foto_profil_url` - Full URL to profile photo

**Methods:**
- `getTodayAttendance()` - Get today's attendance record
- `hasCheckedInToday()` - Boolean check for check-in
- `hasCheckedOutToday()` - Boolean check for check-out

### AttendanceClass Model

**Table:** `attendance_classes`

**Columns:**
- `id` (bigint, PK)
- `nama_kelas` (string)
- `tingkat` (string) - X, XI, XII
- `jurusan` (string) - RPL, TKJ, MM, etc.
- `wali_kelas_id` (bigint, nullable, FK → users)
- `is_active` (boolean, default true)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships:**
- `hasMany` AttendanceStudent (students)
- `belongsTo` User (waliKelas)

**Scopes:**
- `active()` - Only active classes

**Methods:**
- `getStudentCount()` - Count of active students

### AttendanceRecord Model

**Table:** `attendance_records`

**Columns:**
- `id` (bigint, PK)
- `student_id` (bigint, FK → attendance_students)
- `date` (date, indexed)
- `check_in_time` (time, nullable)
- `check_out_time` (time, nullable)
- `check_in_photo` (string, nullable)
- `check_out_photo` (string, nullable)
- `status` (enum: hadir, terlambat, alpha, izin, sakit, indexed)
- `notes` (text, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Indexes:**
- `(date, status)` - For filtering reports
- `student_id` - For student history

**Relationships:**
- `belongsTo` AttendanceStudent (student)

**Scopes:**
- `today()` - Records for today
- `byStatus($status)` - Filter by status
- `byClass($classId)` - Filter by class

**Computed Properties:**
- `check_in_photo_url` - Full URL to check-in photo
- `check_out_photo_url` - Full URL to check-out photo
- `status_label` - Human-readable status

### AttendanceSetting Model

**Table:** `attendance_settings`

**Columns:**
- `id` (bigint, PK)
- `key` (string, unique)
- `value` (text)
- `group_name` (string) - general, time, notification
- `description` (text, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Static Methods:**
- `get($key, $default)` - Get setting value (cached)
- `set($key, $value, $group, $description)` - Update setting
- `getGrouped()` - Get all settings grouped by group_name
- `getByGroup($groupName)` - Get settings for specific group
- `clearCache()` - Clear all settings cache

**Common Settings:**
- `check_in_start` - "06:00"
- `check_in_cutoff` - "07:00" (late cutoff)
- `check_in_end` - "09:00" (end of check-in window)
- `check_out_time` - "15:00"
- `late_tolerance_minutes` - 5
- `school_name` - "SMK Negeri 1"

### AttendanceLog Model

**Table:** `attendance_logs`

**Columns:**
- `id` (bigint, PK)
- `student_id` (bigint, nullable, FK → attendance_students)
- `action` (string) - qr_scan, check_in, check_out, reject, auto_alpha
- `message` (text)
- `response` (text, nullable) - JSON response data
- `status` (enum: pending, success, failed)
- `created_at` (timestamp)

**Relationships:**
- `belongsTo` AttendanceStudent (student)

**Purpose:** Audit trail for all attendance actions

---

## Correctness Properties

### Data Integrity

**Invariants:**
1. A student can only have ONE attendance record per date
2. Check-out time must be after check-in time
3. Status must be one of: hadir, terlambat, alpha, izin, sakit
4. NIS must be unique across all students
5. Active students must belong to an active class

**Validation Rules:**
```php
// Student validation
'nis' => 'required|string|unique:attendance_students,nis|max:20',
'nama' => 'required|string|max:100',
'kelas_id' => 'required|exists:attendance_classes,id',
'no_hp_ortu' => 'nullable|string|regex:/^[0-9]{10,15}$/',
'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

// Attendance scan validation
'nis' => 'required|string|exists:attendance_students,nis',
'photo_base64' => 'required|string',
'action' => 'required|in:check_in,check_out',

// Settings validation
'check_in_start' => 'required|date_format:H:i',
'check_in_cutoff' => 'required|date_format:H:i|after:check_in_start',
'check_in_end' => 'required|date_format:H:i|after:check_in_cutoff',
'late_tolerance_minutes' => 'required|integer|min:0|max:60',
```

### Business Logic Constraints

**Check-In Rules:**
1. Student cannot check-in twice on the same date
2. Check-in must be within time window (check_in_start to check_in_end)
3. Status is "hadir" if before cutoff, "terlambat" if after
4. Photo must be captured and validated
5. Student must be active

**Check-Out Rules:**
1. Student must have checked-in first
2. Student cannot check-out twice on the same date
3. Photo must be captured and validated
4. Check-out time is stored but doesn't affect status

**Auto Alpha Rules:**
1. Runs at cutoff time + late_tolerance_minutes
2. Only marks students with no attendance record for the day
3. Skips students marked as izin/sakit manually
4. Creates log entry for audit

### State Transitions

**Attendance Status State Machine:**
```
[No Record] ──────────────────────┐
     │                            │
     │ check-in before cutoff     │ auto-alpha at cutoff
     ▼                            │
  [hadir] ───────────────────┐    │
                             │    │
     │ check-in after cutoff │    │
     ▼                       │    ▼
[terlambat] ────────────────┴─▶ [alpha]
     │                            ▲
     │ manual mark                │
     ▼                            │
  [izin/sakit] ──────────────────┘
```

**Valid Transitions:**
- `null` → `hadir` (check-in before cutoff)
- `null` → `terlambat` (check-in after cutoff)
- `null` → `alpha` (auto-mark or manual)
- `null` → `izin` (manual mark)
- `null` → `sakit` (manual mark)
- `alpha` → `izin` (manual correction)
- `alpha` → `sakit` (manual correction)

**Invalid Transitions:**
- `hadir` → `alpha` (cannot revert)
- `terlambat` → `hadir` (cannot improve)
- `izin` → `hadir` (status is final)

### Error Handling Guarantees

**Database Transactions:**
All attendance operations wrapped in transactions to ensure atomicity:
```php
DB::beginTransaction();
try {
    // Process scan
    // Save photo
    // Update record
    // Create log
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Return error response
}
```

**Idempotency:**
- QR scan can be repeated without side effects (returns existing record)
- Settings update is idempotent (upsert pattern)
- Report generation is side-effect free (read-only)

**Failure Recovery:**
- Failed photo uploads don't block check-in (optional fallback)
- API timeout doesn't lose scan data (retry mechanism)
- Database connection loss triggers graceful error (no silent failures)

### Performance Guarantees

**Response Time Targets:**
- Dashboard stats API: < 500ms
- QR scan submission: < 1s
- Table pagination: < 300ms
- Report generation: < 2s (up to 1000 records)
- Chart rendering: < 500ms

**Scalability Targets:**
- Support 1000+ concurrent users
- Handle 10,000+ students
- Process 100+ scans per minute
- Store 1 year of attendance data

**Caching Strategy:**
- Settings cached for 1 hour
- Dashboard stats cached for 30 seconds
- Student list cached until CRUD operation
- Class list cached until CRUD operation

---

**Document Complete**
