<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceStudent;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class TodayAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates attendance records for today with various statuses:
     * - 70% Hadir (on time: 06:30-07:00)
     * - 15% Terlambat (late: 07:01-08:00)
     * - 5% Izin (permission)
     * - 5% Sakit (sick)
     * - 5% Alpha (no records created - counted as absent)
     */
    public function run(): void
    {
        $today = Carbon::today();
        $students = AttendanceStudent::where('is_active', true)->get();
        
        if ($students->isEmpty()) {
            $this->command->warn('⚠️  No active students found. Please run AttendanceStudentSeeder first.');
            return;
        }

        $totalStudents = $students->count();
        $this->command->info("📊 Creating attendance records for {$totalStudents} students...");

        // Clear existing records for today
        AttendanceRecord::whereDate('date', $today)->delete();

        // Distribution percentages
        $hadirCount = (int)($totalStudents * 0.70);      // 70% hadir
        $terlambatCount = (int)($totalStudents * 0.15);  // 15% terlambat
        $izinCount = (int)($totalStudents * 0.05);       // 5% izin
        $sakitCount = (int)($totalStudents * 0.05);      // 5% sakit
        // Remaining 5% will be alpha (no record)

        $processed = 0;
        
        // Shuffle students for random distribution
        $shuffledStudents = $students->shuffle();

        // 1. HADIR - Check in on time (06:30-07:00)
        foreach ($shuffledStudents->slice($processed, $hadirCount) as $student) {
            $checkInTime = Carbon::today()
                ->setHour(6)
                ->setMinute(rand(30, 59))
                ->setSecond(rand(0, 59));
            
            $checkOutTime = Carbon::today()
                ->setHour(15)
                ->setMinute(rand(0, 29))
                ->setSecond(rand(0, 59));

            AttendanceRecord::create([
                'student_id' => $student->id,
                'date' => $today,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'status' => 'hadir',
                'notes' => null,
            ]);
        }
        $processed += $hadirCount;
        $this->command->info("✅ Created {$hadirCount} HADIR records (06:30-07:00)");

        // 2. TERLAMBAT - Check in late (07:01-08:00)
        foreach ($shuffledStudents->slice($processed, $terlambatCount) as $student) {
            $checkInTime = Carbon::today()
                ->setHour(7)
                ->setMinute(rand(1, 59))
                ->setSecond(rand(0, 59));
            
            // Some late students check out, some don't
            $checkOutTime = rand(0, 100) > 30 ? Carbon::today()
                ->setHour(15)
                ->setMinute(rand(0, 29))
                ->setSecond(rand(0, 59)) : null;

            AttendanceRecord::create([
                'student_id' => $student->id,
                'date' => $today,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'status' => 'terlambat',
                'notes' => 'Datang terlambat',
            ]);
        }
        $processed += $terlambatCount;
        $this->command->info("⏰ Created {$terlambatCount} TERLAMBAT records (07:01-08:00)");

        // 3. IZIN - Permission (no check-in/out)
        foreach ($shuffledStudents->slice($processed, $izinCount) as $student) {
            AttendanceRecord::create([
                'student_id' => $student->id,
                'date' => $today,
                'check_in_time' => null,
                'check_out_time' => null,
                'status' => 'izin',
                'notes' => 'Izin keperluan keluarga',
            ]);
        }
        $processed += $izinCount;
        $this->command->info("📝 Created {$izinCount} IZIN records");

        // 4. SAKIT - Sick (no check-in/out)
        foreach ($shuffledStudents->slice($processed, $sakitCount) as $student) {
            AttendanceRecord::create([
                'student_id' => $student->id,
                'date' => $today,
                'check_in_time' => null,
                'check_out_time' => null,
                'status' => 'sakit',
                'notes' => 'Sakit demam',
            ]);
        }
        $processed += $sakitCount;
        $this->command->info("🤒 Created {$sakitCount} SAKIT records");

        // 5. ALPHA - No record created (remaining students)
        $alphaCount = $totalStudents - $processed;
        $this->command->info("❌ {$alphaCount} students marked as ALPHA (no record)");

        // Summary
        $this->command->newLine();
        $this->command->info("🎉 Today's attendance seeding completed!");
        $this->command->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['✅ Hadir', $hadirCount, round(($hadirCount / $totalStudents) * 100, 1) . '%'],
                ['⏰ Terlambat', $terlambatCount, round(($terlambatCount / $totalStudents) * 100, 1) . '%'],
                ['📝 Izin', $izinCount, round(($izinCount / $totalStudents) * 100, 1) . '%'],
                ['🤒 Sakit', $sakitCount, round(($sakitCount / $totalStudents) * 100, 1) . '%'],
                ['❌ Alpha', $alphaCount, round(($alphaCount / $totalStudents) * 100, 1) . '%'],
                ['📊 TOTAL', $totalStudents, '100%'],
            ]
        );
    }
}
