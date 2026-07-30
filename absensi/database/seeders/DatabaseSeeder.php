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
        // Seed attendance settings (konfigurasi default)
        $this->call([
            AttendanceSettingsSeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  🎉 Database seeding completed successfully!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('📋 What was seeded:');
        $this->command->line('  ✅ Attendance settings (default configuration)');
        $this->command->line('  ✅ Admin users (Administrator, Operator, Petugas)');
        $this->command->info('');
        $this->command->info('🚀 Next steps:');
        $this->command->line('  1. Login with admin credentials');
        $this->command->line('  2. Create classes (Kelas)');
        $this->command->line('  3. Add students (Siswa)');
        $this->command->line('  4. Generate QR Codes');
        $this->command->line('  5. Start scanning attendance!');
        $this->command->info('');
    }
}
