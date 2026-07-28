<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceClass;
use App\Models\AttendanceStudent;

class AttendanceStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds sample students across different classes.
     * Uses realistic Indonesian names and phone numbers.
     */
    public function run(): void
    {
        // Get all classes
        $classes = AttendanceClass::all();
        
        if ($classes->isEmpty()) {
            $this->command->error('No classes found! Please run AttendanceClassSeeder first.');
            return;
        }

        // Sample Indonesian names
        $names = [
            // Male names
            'Ahmad Fauzi', 'Budi Santoso', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Ramadhan',
            'Gilang Pratama', 'Hendra Wijaya', 'Ilham Saputra', 'Joko Susilo', 'Krisna Putra',
            'Lukman Hakim', 'Muhammad Rizki', 'Nugroho Adi', 'Oki Firmansyah', 'Putra Wibowo',
            // Female names
            'Ayu Lestari', 'Bella Safitri', 'Cantika Dewi', 'Dina Amelia', 'Eka Putri',
            'Fitri Rahmawati', 'Gita Puspita', 'Hani Kartika', 'Indah Permata', 'Jesica Anggun',
            'Komang Sari', 'Lina Marlina', 'Maya Sari', 'Nanda Wulandari', 'Olivia Putri',
            'Putri Maharani', 'Rika Septiani', 'Siti Nurhaliza', 'Tari Wijayanti', 'Ulfah Hasanah',
            'Vina Anggraini', 'Wulan Dari', 'Yanti Kusuma', 'Zahra Ramadani',
            // Additional names
            'Adi Nugroho', 'Bambang Irawan', 'Cahyo Wicaksono', 'Dimas Setiawan', 'Fikri Hidayat',
            'Galuh Pramudya', 'Hanif Akbar', 'Ivan Setyawan', 'Jaya Kusuma', 'Kemal Fauzan',
        ];

        $students = [];
        $nisCounter = 24001; // Starting NIS: 24001 (year 2024)

        // Distribute students across classes (approximately 6-8 per class)
        foreach ($classes as $class) {
            $studentsPerClass = rand(6, 8);
            
            for ($i = 0; $i < $studentsPerClass; $i++) {
                if (empty($names)) {
                    break 2; // Exit both loops if we run out of names
                }

                // Pick random name and remove from array to avoid duplicates
                $randomIndex = array_rand($names);
                $name = $names[$randomIndex];
                unset($names[$randomIndex]);
                $names = array_values($names); // Re-index array

                // Generate random Indonesian mobile phone number (628xxx format)
                $phoneNumber = '628' . rand(100000000, 999999999);

                $students[] = [
                    'nis' => (string)$nisCounter,
                    'nama' => $name,
                    'kelas_id' => $class->id,
                    'no_hp_ortu' => $phoneNumber,
                    'qr_code_path' => null, // Will be generated in Task 9
                    'foto_profil' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $nisCounter++;
            }
        }

        // Insert all students
        foreach ($students as $studentData) {
            AttendanceStudent::updateOrCreate(
                ['nis' => $studentData['nis']],
                $studentData
            );
        }

        $this->command->info('Successfully seeded ' . count($students) . ' students across ' . $classes->count() . ' classes.');
    }
}
