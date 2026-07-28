<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data ?: $this->getDefaultData();
    }

    /**
     * Return array of data for the export.
     */
    public function array(): array
    {
        return [
            ['24001', 'Contoh Siswa 1', '1', '628123456789'],
            ['24002', 'Contoh Siswa 2', '1', '628123456790'],
            ['24003', 'Contoh Siswa 3', '2', '628123456791'],
        ];
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            'NIS',
            'Nama',
            'Kelas ID',
            'No HP Ortu',
        ];
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'], // Blue-600
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style data rows
        $sheet->getStyle('A2:D4')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set row height
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    /**
     * Define column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // NIS
            'B' => 30, // Nama
            'C' => 12, // Kelas ID
            'D' => 20, // No HP Ortu
        ];
    }

    /**
     * Get default template data.
     */
    private function getDefaultData(): array
    {
        return [
            ['NIS', 'Nama', 'Kelas ID', 'No HP Ortu'],
            ['24001', 'Contoh Siswa 1', '1', '628123456789'],
            ['24002', 'Contoh Siswa 2', '1', '628123456790'],
        ];
    }
}
