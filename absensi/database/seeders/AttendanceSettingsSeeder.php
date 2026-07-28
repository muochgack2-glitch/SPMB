<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds default attendance settings for the QR Code Scanner attendance system.
     * These settings can be modified through the admin interface.
     */
    public function run(): void
    {
        // Clear existing settings to avoid duplicates when re-seeding
        DB::table('attendance_settings')->truncate();
        
        // Clear settings cache to ensure fresh values are retrieved
        \App\Models\AttendanceSetting::clearCache();

        // Insert default attendance settings
        DB::table('attendance_settings')->insert([
            [
                'key' => 'check_in_time',
                'value' => '07:00',
                'group_name' => 'schedule',
                'description' => 'Official check-in time for students',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'check_out_time',
                'value' => '15:00',
                'group_name' => 'schedule',
                'description' => 'Official check-out time for students',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tolerance_minutes',
                'value' => '15',
                'group_name' => 'schedule',
                'description' => 'Tolerance period in minutes after check-in time before marking as late',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cutoff_time',
                'value' => '09:00',
                'group_name' => 'schedule',
                'description' => 'Cutoff time after which students are automatically marked absent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_parent_notification',
                'value' => 'true',
                'group_name' => 'notification',
                'description' => 'Enable or disable WhatsApp notifications to parents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'include_photo_in_notification',
                'value' => 'false',
                'group_name' => 'notification',
                'description' => 'Include captured photo in WhatsApp notifications to parents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_name',
                'value' => 'SMK Negeri 1',
                'group_name' => 'general',
                'description' => 'School name displayed in notifications and reports',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
