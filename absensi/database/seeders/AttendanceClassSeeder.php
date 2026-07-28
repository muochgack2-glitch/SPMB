<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceClass;

class AttendanceClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds sample attendance classes for the QR Code Scanner attendance system.
     */
    public function run(): void
    {
        $classes = [
            // RPL (Rekayasa Perangkat Lunak) - Software Engineering
            [
                'nama_kelas' => '10 RPL',
                'tingkat' => '10',
                'jurusan' => 'RPL',
                'wali_kelas_id' => null,
                'is_active' => true,
            ],
            [
                'nama_kelas' => '11 RPL',
                'tingkat' => '11',
                'jurusan' => 'RPL',
                'wali_kelas_id' => null,
                'is_active' => true,
            ],
            [
                'nama_kelas' => '12 RPL',
                'tingkat' => '12',
                'jurusan' => 'RPL',
                'wali_kelas_id' => null,
                'is_active' => true,
            ],
            // TKJ (Teknik Komputer dan Jaringan) - Computer & Network Engineering
            [
                'nama_kelas' => '10 TKJ',
                'tingkat' => '10',
                'jurusan' => 'TKJ',
                'wali_kelas_id' => null,
                'is_active' => true,
            ],
            [
                'nama_kelas' => '11 TKJ',
                'tingkat' => '11',
                'jurusan' => 'TKJ',
                'wali_kelas_id' => null,
                'is_active' => true,
            ],
            [
                'nama_kelas' => '12 TKJ',
                'tingkat' => '12',
                'jurusan' => 'TKJ',
                'wali_kelas_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($classes as $classData) {
            AttendanceClass::updateOrCreate(
                ['nama_kelas' => $classData['nama_kelas'], 'tingkat' => $classData['tingkat']],
                $classData
            );
        }
    }
}
