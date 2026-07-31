<?php

namespace Database\Seeders;

use App\Models\AttendanceClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat data kelas untuk SMK PGRI Blora
     * Jurusan: MPLB, AKL, BUSANA
     * Tingkat: X, XI, XII
     */
    public function run(): void
    {
        // Get users for wali kelas
        $users = User::all();
        $userIndex = 1; // Skip admin (index 0)

        $classes = [
            // Tingkat X (Kelas 10)
            ['tingkat' => 'X', 'nama_kelas' => 'X MPLB 1', 'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'short' => 'MPLB'],
            ['tingkat' => 'X', 'nama_kelas' => 'X MPLB 2', 'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'short' => 'MPLB'],
            ['tingkat' => 'X', 'nama_kelas' => 'X AKL 1', 'jurusan' => 'Akuntansi dan Keuangan Lembaga', 'short' => 'AKL'],
            ['tingkat' => 'X', 'nama_kelas' => 'X AKL 2', 'jurusan' => 'Akuntansi dan Keuangan Lembaga', 'short' => 'AKL'],
            ['tingkat' => 'X', 'nama_kelas' => 'X BUSANA 1', 'jurusan' => 'Tata Busana', 'short' => 'BUSANA'],
            ['tingkat' => 'X', 'nama_kelas' => 'X BUSANA 2', 'jurusan' => 'Tata Busana', 'short' => 'BUSANA'],

            // Tingkat XI (Kelas 11)
            ['tingkat' => 'XI', 'nama_kelas' => 'XI MPLB 1', 'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'short' => 'MPLB'],
            ['tingkat' => 'XI', 'nama_kelas' => 'XI MPLB 2', 'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'short' => 'MPLB'],
            ['tingkat' => 'XI', 'nama_kelas' => 'XI AKL 1', 'jurusan' => 'Akuntansi dan Keuangan Lembaga', 'short' => 'AKL'],
            ['tingkat' => 'XI', 'nama_kelas' => 'XI AKL 2', 'jurusan' => 'Akuntansi dan Keuangan Lembaga', 'short' => 'AKL'],
            ['tingkat' => 'XI', 'nama_kelas' => 'XI BUSANA 1', 'jurusan' => 'Tata Busana', 'short' => 'BUSANA'],
            ['tingkat' => 'XI', 'nama_kelas' => 'XI BUSANA 2', 'jurusan' => 'Tata Busana', 'short' => 'BUSANA'],

            // Tingkat XII (Kelas 12)
            ['tingkat' => 'XII', 'nama_kelas' => 'XII MPLB 1', 'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'short' => 'MPLB'],
            ['tingkat' => 'XII', 'nama_kelas' => 'XII MPLB 2', 'jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'short' => 'MPLB'],
            ['tingkat' => 'XII', 'nama_kelas' => 'XII AKL 1', 'jurusan' => 'Akuntansi dan Keuangan Lembaga', 'short' => 'AKL'],
            ['tingkat' => 'XII', 'nama_kelas' => 'XII AKL 2', 'jurusan' => 'Akuntansi dan Keuangan Lembaga', 'short' => 'AKL'],
            ['tingkat' => 'XII', 'nama_kelas' => 'XII BUSANA 1', 'jurusan' => 'Tata Busana', 'short' => 'BUSANA'],
            ['tingkat' => 'XII', 'nama_kelas' => 'XII BUSANA 2', 'jurusan' => 'Tata Busana', 'short' => 'BUSANA'],
        ];

        foreach ($classes as $index => $classData) {
            // Check if class already exists
            $exists = AttendanceClass::where('nama_kelas', $classData['nama_kelas'])
                ->where('tingkat', $classData['tingkat'])
                ->exists();

            if (!$exists) {
                // Assign wali kelas from users (cycle through available users)
                $waliKelasId = null;
                if ($users->count() > $userIndex) {
                    $waliKelasId = $users[$userIndex]->id;
                    $userIndex++;
                    // Reset to user 1 if we run out of users
                    if ($userIndex >= $users->count()) {
                        $userIndex = 1;
                    }
                }

                AttendanceClass::create([
                    'nama_kelas' => $classData['nama_kelas'],
                    'tingkat' => $classData['tingkat'],
                    'jurusan' => $classData['jurusan'],
                    'wali_kelas_id' => $waliKelasId,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ ' . count($classes) . ' kelas berhasil dibuat untuk SMK PGRI Blora');
        $this->command->line('');
        $this->command->line('Jurusan:');
        $this->command->line('  • MPLB (Manajemen Perkantoran dan Layanan Bisnis)');
        $this->command->line('  • AKL (Akuntansi dan Keuangan Lembaga)');
        $this->command->line('  • BUSANA (Tata Busana)');
        $this->command->line('');
        $this->command->line('Tingkat: X, XI, XII');
        $this->command->line('Total Kelas: ' . count($classes));
    }
}
