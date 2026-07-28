<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AttendanceExportService
{
    /**
     * Export attendance records to Excel.
     *
     * @param array $filters Filter options
     * @return string Path to generated Excel file
     */
    public function exportToExcel(array $filters = []): string
    {
        // Get filtered records
        $records = $this->getFilteredRecords($filters);

        // Generate filename
        $filename = 'attendance_export_' . now()->format('Ymd_His') . '.xlsx';
        $path = 'exports/' . $filename;

        // Create Excel export
        Excel::store(new AttendanceExport($records), $path, 'local');

        return $path;
    }

    /**
     * Get filtered attendance records.
     *
     * @param array $filters
     * @return Collection
     */
    private function getFilteredRecords(array $filters): Collection
    {
        $query = AttendanceRecord::with(['student.kelas']);

        // Date range filter
        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        // Single date filter (if date range not provided)
        if (empty($filters['start_date']) && empty($filters['end_date']) && !empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['class_id']);
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Student filter
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Order by date and time
        $query->orderBy('date', 'desc')
              ->orderBy('check_in_time', 'asc');

        return $query->get();
    }

    /**
     * Format records for Excel export.
     *
     * @param Collection $records
     * @return array
     */
    public function formatForExcel(Collection $records): array
    {
        $data = [];

        // Header row
        $data[] = [
            'Tanggal',
            'NIS',
            'Nama',
            'Kelas',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Catatan',
        ];

        // Data rows
        foreach ($records as $record) {
            $data[] = [
                $record->date->format('d/m/Y'),
                $record->student->nis,
                $record->student->nama,
                $record->student->kelas->nama_kelas,
                $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : '-',
                $record->check_out_time ? Carbon::parse($record->check_out_time)->format('H:i') : '-',
                $this->getStatusLabel($record->status),
                $record->notes ?? '',
            ];
        }

        return $data;
    }

    /**
     * Get human-readable status label.
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'alpha' => 'Alpha',
            'izin' => 'Izin',
            default => ucfirst($status),
        };
    }

    /**
     * Export attendance summary/statistics to Excel.
     *
     * @param array $filters
     * @return string Path to generated Excel file
     */
    public function exportSummaryToExcel(array $filters = []): string
    {
        $records = $this->getFilteredRecords($filters);

        // Group by student
        $summary = $records->groupBy('student_id')->map(function ($studentRecords) {
            $student = $studentRecords->first()->student;

            return [
                'nis' => $student->nis,
                'nama' => $student->nama,
                'kelas' => $student->kelas->nama_kelas,
                'total' => $studentRecords->count(),
                'hadir' => $studentRecords->where('status', 'hadir')->count(),
                'terlambat' => $studentRecords->where('status', 'terlambat')->count(),
                'alpha' => $studentRecords->where('status', 'alpha')->count(),
                'izin' => $studentRecords->where('status', 'izin')->count(),
            ];
        });

        // Generate filename
        $filename = 'attendance_summary_' . now()->format('Ymd_His') . '.xlsx';
        $path = 'exports/' . $filename;

        // Create Excel export
        Excel::store(new AttendanceSummaryExport($summary), $path, 'local');

        return $path;
    }
}

/**
 * Excel export class for detailed attendance records.
 */
class AttendanceExport implements \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping
{
    public function __construct(private Collection $records) {}

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIS',
            'Nama',
            'Kelas',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Catatan',
        ];
    }

    public function map($record): array
    {
        return [
            $record->date->format('d/m/Y'),
            $record->student->nis,
            $record->student->nama,
            $record->student->kelas->nama_kelas,
            $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : '-',
            $record->check_out_time ? Carbon::parse($record->check_out_time)->format('H:i') : '-',
            $record->status_label,
            $record->notes ?? '',
        ];
    }
}

/**
 * Excel export class for attendance summary.
 */
class AttendanceSummaryExport implements \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithHeadings
{
    public function __construct(private Collection $summary) {}

    public function collection()
    {
        return $this->summary->map(function ($item) {
            return [
                'nis' => $item['nis'],
                'nama' => $item['nama'],
                'kelas' => $item['kelas'],
                'total' => $item['total'],
                'hadir' => $item['hadir'],
                'terlambat' => $item['terlambat'],
                'alpha' => $item['alpha'],
                'izin' => $item['izin'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama',
            'Kelas',
            'Total Hari',
            'Hadir',
            'Terlambat',
            'Alpha',
            'Izin',
        ];
    }
}
