<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanExport implements WithMultipleSheets
{
    protected $pendaftars;
    protected $pendaftarDiterima;

    public function __construct($pendaftars, $pendaftarDiterima = null)
    {
        $this->pendaftars = $pendaftars;
        $this->pendaftarDiterima = $pendaftarDiterima ?? collect();
    }

    /**
     * Return multiple sheets
     */
    public function sheets(): array
    {
        $sheets = [
            new LaporanAllSheet($this->pendaftars),
        ];

        // Add sheet for pendaftar diterima if data exists
        if ($this->pendaftarDiterima->count() > 0) {
            $sheets[] = new LaporanDiterimaSheet($this->pendaftarDiterima);
        }

        return $sheets;
    }
}

/**
 * Sheet 1: All Pendaftar
 */
class LaporanAllSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $pendaftars;

    public function __construct($pendaftars)
    {
        $this->pendaftars = $pendaftars;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->pendaftars;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No. Registrasi',
            'NISN',
            'Nama Lengkap',
            'Asal Sekolah',
            'Jurusan',
            'Alamat',
            'Nama Jaringan',
            'Gelombang',
            'Tanggal Daftar',
            'Status Siswa',
            'Status Daftar Ulang',
            'Ukuran Kaos',
            'Status Kain',
            'Status Kaos',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($pendaftar): array
    {
        $tglDaftar = $pendaftar->tgl_daftar 
            ? $pendaftar->tgl_daftar->format('d/m/Y H:i') 
            : ($pendaftar->created_at ? $pendaftar->created_at->format('d/m/Y H:i') : '-');

        return [
            $pendaftar->no_registrasi,
            $pendaftar->nisn,
            $pendaftar->nama_lengkap,
            $pendaftar->asal_sekolah,
            $pendaftar->jurusan,
            $pendaftar->alamat,
            $pendaftar->nama_jaringan ?: '-',
            'Gelombang ' . $pendaftar->gelombang,
            $tglDaftar,
            $pendaftar->status_siswa ?: '-',
            optional($pendaftar->logistik)->status_bayar === 'Lunas' ? 'Sudah Daftar Ulang' : 'Belum Daftar Ulang',
            optional($pendaftar->logistik)->ukuran_kaos ?: '-',
            optional($pendaftar->logistik)->status_kain ?: '-',
            optional($pendaftar->logistik)->status_kaos ?: '-',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10b981']
                ],
            ],
        ];
    }
}

/**
 * Sheet 2: Pendaftar Diterima Only
 */
class LaporanDiterimaSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $pendaftarDiterima;

    public function __construct($pendaftarDiterima)
    {
        $this->pendaftarDiterima = $pendaftarDiterima;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->pendaftarDiterima;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No. Registrasi',
            'NISN',
            'Nama Lengkap',
            'Asal Sekolah',
            'Jurusan',
            'Alamat',
            'No. Telepon Siswa',
            'No. HP Orang Tua',
            'No. HP Wali',
            'Nama Jaringan',
            'Gelombang',
            'Tanggal Daftar',
            'Status Daftar Ulang',
            'Ukuran Kaos',
            'Status Kain',
            'Status Kaos',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($pendaftar): array
    {
        $tglDaftar = $pendaftar->tgl_daftar 
            ? $pendaftar->tgl_daftar->format('d/m/Y H:i') 
            : ($pendaftar->created_at ? $pendaftar->created_at->format('d/m/Y H:i') : '-');

        return [
            $pendaftar->no_registrasi,
            $pendaftar->nisn,
            $pendaftar->nama_lengkap,
            $pendaftar->asal_sekolah,
            $pendaftar->jurusan,
            $pendaftar->alamat,
            $pendaftar->no_telepon ?: '-',
            $pendaftar->no_hp_ortu ?: '-',
            $pendaftar->no_hp_wali ?: '-',
            $pendaftar->nama_jaringan ?: '-',
            'Gelombang ' . $pendaftar->gelombang,
            $tglDaftar,
            optional($pendaftar->logistik)->status_bayar === 'Lunas' ? 'Sudah Daftar Ulang' : 'Belum Daftar Ulang',
            optional($pendaftar->logistik)->ukuran_kaos ?: '-',
            optional($pendaftar->logistik)->status_kain ?: '-',
            optional($pendaftar->logistik)->status_kaos ?: '-',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366f1']
                ],
            ],
        ];
    }
}
