<?php

namespace App\Livewire;

use App\Models\AttendanceClass;
use App\Models\AttendanceRecord;
use App\Models\AttendanceStudent;
use App\Services\AttendanceService;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;

class AttendanceDashboard extends Component
{
    public $selectedClass = null;
    public $selectedDate;
    public $stats = [];
    public $selectedPhoto = null;
    public $showPhotoModal = false;

    protected $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadData();
    }

    /**
     * Load dashboard data
     */
    public function loadData()
    {
        $this->stats = $this->attendanceService->getAttendanceStats(
            $this->selectedDate,
            $this->selectedClass
        );
    }

    /**
     * Change selected class filter
     */
    public function setClass($classId)
    {
        $this->selectedClass = $classId == 'all' ? null : $classId;
        $this->loadData();
    }

    /**
     * Change selected date
     */
    public function setDate($date)
    {
        $this->selectedDate = $date;
        $this->loadData();
    }

    /**
     * View photo in lightbox
     */
    public function viewPhoto($photoPath, $type = 'check_in')
    {
        $this->selectedPhoto = [
            'path' => $photoPath,
            'type' => $type
        ];
        $this->showPhotoModal = true;
    }

    /**
     * Close photo modal
     */
    public function closePhotoModal()
    {
        $this->showPhotoModal = false;
        $this->selectedPhoto = null;
    }

    /**
     * Refresh data (called every 30 seconds via wire:poll)
     */
    #[On('refresh-dashboard')]
    public function refresh()
    {
        $this->loadData();
    }

    /**
     * Get attendance records for display
     */
    public function getAttendanceRecordsProperty()
    {
        $query = AttendanceRecord::with(['student.kelas'])
            ->whereDate('date', $this->selectedDate);

        if ($this->selectedClass) {
            $query->whereHas('student', function ($q) {
                $q->where('kelas_id', $this->selectedClass);
            });
        }

        return $query->orderBy('check_in_time', 'asc')->get();
    }

    /**
     * Get students who haven't checked in
     */
    public function getAbsentStudentsProperty()
    {
        $query = AttendanceStudent::with('kelas')
            ->where('is_active', true)
            ->whereDoesntHave('attendanceRecords', function ($q) {
                $q->whereDate('date', $this->selectedDate);
            });

        if ($this->selectedClass) {
            $query->where('kelas_id', $this->selectedClass);
        }

        return $query->get();
    }

    /**
     * Get all active classes for filter
     */
    public function getClassesProperty()
    {
        return AttendanceClass::where('is_active', true)
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->get();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.attendance-dashboard', [
            'attendanceRecords' => $this->attendanceRecords,
            'absentStudents' => $this->absentStudents,
            'classes' => $this->classes,
        ]);
    }
}
