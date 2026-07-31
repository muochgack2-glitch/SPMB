<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceStudent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat data rekam absensi untuk testing
     * Data dibuat untuk 30 hari terakhir
     */
    public function run(): void
    {
        $students = AttendanceStudent::where('is_active', true)->get();

        if ($students->isEmpty()) {
            $this->command->error('⚠️  Tidak ada siswa! Jalankan AttendanceStudentSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('Membuat rekam absensi untuk ' . $students->count() . ' siswa...');

        // Create attendance records for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->subDay(); // Until yesterday
        $totalRecords = 0;

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip weekends
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            $this->command->line("Membuat data untuk tanggal: {$currentDate->format('d-m-Y')}");

            foreach ($students as $student) {
                // Random attendance pattern (85% hadir, 10% terlambat, 3% sakit, 2% izin, 0% alpha for testing)
                $rand = rand(1, 100);

                if ($rand <= 85) {
                    // Hadir
                    $status = 'hadir';
                    $checkInTime = $currentDate->copy()->setTime(7, rand(0, 30), rand(0, 59));
                    $checkOutTime = $currentDate->copy()->setTime(14, rand(30, 59), rand(0, 59));
                } elseif ($rand <= 95) {
                    // Terlambat
                    $status = 'terlambat';
                    $checkInTime = $currentDate->copy()->setTime(7, rand(31, 59), rand(0, 59));
                    $checkOutTime = $currentDate->copy()->setTime(14, rand(30, 59), rand(0, 59));
                } elseif ($rand <= 98) {
                    // Sakit (no check in/out)
                    $status = 'sakit';
                    $checkInTime = null;
                    $checkOutTime = null;
                } else {
                    // Izin (no check in/out)
                    $status = 'izin';
                    $checkInTime = null;
                    $checkOutTime = null;
                }

                // Check if record already exists
                $exists = AttendanceRecord::where('student_id', $student->id)
                    ->whereDate('date', $currentDate->format('Y-m-d'))
                    ->exists();

                if (!$exists) {
                    AttendanceRecord::create([
                        'student_id' => $student->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'status' => $status,
                        'notes' => $this->generateNotes($status),
                    ]);

                    $totalRecords++;
                }
            }

            $currentDate->addDay();
        }

        $this->command->info("✅ {$totalRecords} rekam absensi berhasil dibuat!");
        $this->command->line('');
        $this->command->line('Statistik:');
        $this->command->line('  • Total Siswa: ' . $students->count());
        $this->command->line('  • Total Hari Kerja: ' . $this->countWorkdays($startDate, $endDate));
        $this->command->line('  • Total Rekam: ' . $totalRecords);
        $this->command->line('');
        $this->command->line('Status Distribution:');
        $this->command->line('  • Hadir: ~85%');
        $this->command->line('  • Terlambat: ~10%');
        $this->command->line('  • Sakit: ~3%');
        $this->command->line('  • Izin: ~2%');
    }

    /**
     * Generate notes based on status
     */
    private function generateNotes(string $status): ?string
    {
        $notes = [
            'sakit' => ['Demam', 'Flu', 'Sakit perut', 'Pusing', 'Batuk pilek'],
            'izin' => ['Keperluan keluarga', 'Acara keluarga', 'Mengikuti lomba', 'Urusan pribadi'],
        ];

        if (isset($notes[$status])) {
            return $notes[$status][array_rand($notes[$status])];
        }

        return null;
    }

    /**
     * Count workdays between two dates (exclude weekends)
     */
    private function countWorkdays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (!$current->isWeekend()) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }
}
