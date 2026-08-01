<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert announcement setting if not exists
        $exists = DB::table('attendance_settings')
            ->where('key', 'announcement')
            ->exists();

        if (!$exists) {
            DB::table('attendance_settings')->insert([
                'key' => 'announcement',
                'value' => 'Siswa harap scan QR Code saat masuk gerbang sekolah. Jangan lupa bawa kartu siswa!',
                'group_name' => 'general',
                'description' => 'Pengumuman yang ditampilkan di halaman scanner publik',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove announcement setting
        DB::table('attendance_settings')
            ->where('key', 'announcement')
            ->delete();
    }
};
