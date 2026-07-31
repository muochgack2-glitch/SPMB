<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  🎓 SMK PGRI BLORA - Database Seeder');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');

        // Seed attendance settings (konfigurasi default)
        $this->call([
            AttendanceSettingsSeeder::class,
            AdminUserSeeder::class,
            AttendanceClassSeeder::class,
            AttendanceStudentSeeder::class,
            AttendanceRecordSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  🎉 Database seeding completed successfully!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('📋 What was seeded:');
        $this->command->line('  ✅ Attendance settings (default configuration)');
        $this->command->line('  ✅ Admin users & Teachers (Administrator, Operator, Petugas, Guru)');
        $this->command->line('  ✅ Classes (18 kelas: X, XI, XII - MPLB, AKL, BUSANA)');
        $this->command->line('  ✅ Students (~500 siswa dengan QR Code)');
        $this->command->line('  ✅ Attendance Records (30 hari terakhir)');
        $this->command->info('');
        $this->command->info('🔐 Login Credentials:');
        $this->command->line('  📧 Admin     : admin@smkpgriblora.sch.id / admin123');
        $this->command->line('  📧 Operator  : operator@smkpgriblora.sch.id / operator123');
        $this->command->line('  📧 Petugas   : petugas@smkpgriblora.sch.id / petugas123');
        $this->command->line('  📧 Guru      : [email guru] / guru123');
        $this->command->info('');
        $this->command->warn('⚠️  PENTING: Ganti password setelah login pertama kali!');
        $this->command->info('');
        $this->command->info('🚀 Ready to use!');
        $this->command->line('  • Dashboard tersedia di: /attendance/dashboard');
        $this->command->line('  • Scanner tersedia di: /attendance/scanner');
        $this->command->line('  • Data lengkap sudah ter-generate untuk testing');
        $this->command->info('');
    }
}
