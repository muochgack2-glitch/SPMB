<?php

namespace Database\Seeders;

use App\Models\AttendanceClass;
use App\Models\AttendanceStudent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AttendanceStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat data siswa untuk setiap kelas di SMK PGRI Blora
     */
    public function run(): void
    {
        $classes = AttendanceClass::all();

        if ($classes->isEmpty()) {
            $this->command->error('⚠️  Tidak ada kelas! Jalankan AttendanceClassSeeder terlebih dahulu.');
            return;
        }

        // Nama-nama siswa untuk variasi
        $namaDepan = [
            'Ahmad', 'Budi', 'Dani', 'Eko', 'Fajar', 'Galih', 'Hadi', 'Indra', 'Joko', 'Kiki',
            'Ayu', 'Bella', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hani', 'Intan', 'Jihan',
            'Lina', 'Maya', 'Nisa', 'Putri', 'Rani', 'Sari', 'Tina', 'Uci', 'Vina', 'Wulan',
        ];

        $namaBelakang = [
            'Santoso', 'Pratama', 'Wijaya', 'Kusuma', 'Saputra', 'Lestari', 'Hidayat', 'Permana',
            'Nugroho', 'Wibowo', 'Setiawan', 'Rahmawati', 'Setyawan', 'Purnama', 'Utami', 'Sari',
        ];

        $totalCreated = 0;
        $nisCounter = 2024001; // Starting NIS number

        foreach ($classes as $class) {
            $this->command->info("Membuat siswa untuk kelas {$class->nama_kelas}...");

            // Create 25-30 students per class
            $studentsPerClass = rand(25, 30);

            for ($i = 1; $i <= $studentsPerClass; $i++) {
                $nis = str_pad($nisCounter, 7, '0', STR_PAD_LEFT);
                $nisCounter++;

                // Check if student already exists
                $exists = AttendanceStudent::where('nis', $nis)->exists();
                if ($exists) {
                    continue;
                }

                // Generate random name
                $nama = $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)];

                // Generate phone number
                $noHpOrtu = '08' . rand(1000000000, 9999999999);

                // Create student
                $student = AttendanceStudent::create([
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas_id' => $class->id,
                    'no_hp_ortu' => $noHpOrtu,
                    'is_active' => true,
                ]);

                // Generate QR Code
                $this->generateQRCode($student);

                $totalCreated++;
            }
        }

        $this->command->info("✅ {$totalCreated} siswa berhasil dibuat!");
        $this->command->line('');
        $this->command->line('Detail:');
        foreach ($classes as $class) {
            $count = $class->students()->count();
            $this->command->line("  • {$class->nama_kelas}: {$count} siswa");
        }
    }

    /**
     * Generate QR Code for student
     */
    private function generateQRCode(AttendanceStudent $student): void
    {
        try {
            // Check if QrCode facade exists
            if (!class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
                // Skip QR generation if package not installed
                return;
            }

            // Create qrcodes directory if it doesn't exist in public storage
            if (!Storage::disk('public')->exists('qrcodes')) {
                Storage::disk('public')->makeDirectory('qrcodes');
            }

            // Generate QR code content (NIS)
            $qrContent = $student->nis;

            // Generate QR code image (SVG format to match QRCodeService)
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->generate($qrContent);

            // Save QR code to public storage
            $path = "qrcodes/{$student->nis}.svg";
            Storage::disk('public')->put($path, $qrCode);

            // Update student with QR code path
            $student->update([
                'qr_code_path' => $path
            ]);

            // Optional: Show success message for first few students
            if ($student->id <= 5) {
                $this->command->info("  ✅ QR code generated for {$student->nama}");
            }
        } catch (\Exception $e) {
            $this->command->warn("  ⚠️  Gagal generate QR code untuk {$student->nama}: " . $e->getMessage());
        }
    }
}
