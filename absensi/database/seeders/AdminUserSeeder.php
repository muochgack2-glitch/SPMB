<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder ini membuat user admin default untuk sistem absensi.
     * 
     * Credentials:
     * - Email: admin@smkpgriblora.sch.id
     * - Password: admin123
     * 
     * PENTING: Ganti password setelah login pertama kali!
     */
    public function run(): void
    {
        // Cek apakah user admin sudah ada
        $adminExists = User::where('email', 'admin@smkpgriblora.sch.id')->exists();

        if ($adminExists) {
            $this->command->warn('Admin user already exists. Skipping...');
            return;
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@smkpgriblora.sch.id',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);

        $this->command->info('✅ Admin user created successfully!');
        $this->command->line('');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line('  📧 Email    : admin@smkpgriblora.sch.id');
        $this->command->line('  🔑 Password : admin123');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line('');
        $this->command->warn('⚠️  IMPORTANT: Change password after first login!');

        // Optional: Create additional users
        $this->createAdditionalUsers();
    }

    /**
     * Create additional users (optional)
     */
    private function createAdditionalUsers(): void
    {
        // Check if operator user already exists
        $operatorExists = User::where('email', 'operator@smkpgriblora.sch.id')->exists();

        if (!$operatorExists) {
            User::create([
                'name' => 'Operator',
                'email' => 'operator@smkpgriblora.sch.id',
                'email_verified_at' => now(),
                'password' => Hash::make('operator123'),
            ]);

            $this->command->info('✅ Operator user created successfully!');
            $this->command->line('  📧 Email    : operator@smkpgriblora.sch.id');
            $this->command->line('  🔑 Password : operator123');
            $this->command->line('');
        }

        // Check if petugas scanner user already exists
        $petugasExists = User::where('email', 'petugas@smkpgriblora.sch.id')->exists();

        if (!$petugasExists) {
            User::create([
                'name' => 'Petugas Scanner',
                'email' => 'petugas@smkpgriblora.sch.id',
                'email_verified_at' => now(),
                'password' => Hash::make('petugas123'),
            ]);

            $this->command->info('✅ Petugas Scanner user created successfully!');
            $this->command->line('  📧 Email    : petugas@smkpgriblora.sch.id');
            $this->command->line('  🔑 Password : petugas123');
            $this->command->line('');
        }

        // Create wali kelas / guru
        $teachers = [
            ['name' => 'Budi Santoso, S.Pd', 'email' => 'budi.santoso@smkpgriblora.sch.id'],
            ['name' => 'Siti Nurhaliza, S.Pd', 'email' => 'siti.nurhaliza@smkpgriblora.sch.id'],
            ['name' => 'Ahmad Fauzi, S.Kom', 'email' => 'ahmad.fauzi@smkpgriblora.sch.id'],
            ['name' => 'Dewi Lestari, S.Pd', 'email' => 'dewi.lestari@smkpgriblora.sch.id'],
            ['name' => 'Rizki Pratama, S.Pd', 'email' => 'rizki.pratama@smkpgriblora.sch.id'],
            ['name' => 'Nur Aini, S.Pd', 'email' => 'nur.aini@smkpgriblora.sch.id'],
            ['name' => 'Bambang Setiawan, S.Pd', 'email' => 'bambang.setiawan@smkpgriblora.sch.id'],
            ['name' => 'Yuni Astuti, S.Pd', 'email' => 'yuni.astuti@smkpgriblora.sch.id'],
            ['name' => 'Agus Wijaya, S.Pd', 'email' => 'agus.wijaya@smkpgriblora.sch.id'],
        ];

        $created = 0;
        foreach ($teachers as $teacher) {
            $exists = User::where('email', $teacher['email'])->exists();
            if (!$exists) {
                User::create([
                    'name' => $teacher['name'],
                    'email' => $teacher['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('guru123'),
                ]);
                $created++;
            }
        }

        if ($created > 0) {
            $this->command->info("✅ {$created} guru/wali kelas created successfully!");
            $this->command->line('  🔑 Default Password : guru123');
            $this->command->line('');
        }
    }
}
