<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('group_name', 50);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('group_name', 'idx_group');
            $table->index('key', 'idx_key');
        });

        // Insert default settings
        DB::table('attendance_settings')->insert([
            [
                'key' => 'check_in_time',
                'value' => '07:00',
                'group_name' => 'time',
                'description' => 'Jam masuk resmi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'check_out_time',
                'value' => '15:00',
                'group_name' => 'time',
                'description' => 'Jam pulang resmi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tolerance_minutes',
                'value' => '15',
                'group_name' => 'tolerance',
                'description' => 'Toleransi keterlambatan (menit)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cutoff_time',
                'value' => '09:00',
                'group_name' => 'time',
                'description' => 'Batas waktu absen masuk (setelah ini = alpha)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_parent_notification',
                'value' => 'true',
                'group_name' => 'notification',
                'description' => 'Aktifkan notifikasi orang tua',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'include_photo_in_notification',
                'value' => 'false',
                'group_name' => 'notification',
                'description' => 'Sertakan foto dalam notifikasi WA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_name',
                'value' => 'SMK PGRI BLORA',
                'group_name' => 'general',
                'description' => 'Nama sekolah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
