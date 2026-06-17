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
        // Add external broadcast rate limiting settings
        $settings = [
            [
                'key' => 'wa_external_broadcast_min_delay',
                'value' => '2',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Min Delay (detik)',
                'description' => 'Minimum delay antara pesan external broadcast. Nilai lebih tinggi = lebih aman dari spam detection. Recommended: 2-3 detik.',
                'is_public' => false,
            ],
            [
                'key' => 'wa_external_broadcast_max_delay',
                'value' => '4',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Max Delay (detik)',
                'description' => 'Maximum delay antara pesan external broadcast. Random antara min dan max untuk tampil natural. Recommended: 4-5 detik.',
                'is_public' => false,
            ],
            [
                'key' => 'wa_external_broadcast_break_interval',
                'value' => '10',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Break Interval',
                'description' => 'Setiap berapa pesan akan ada jeda tambahan (untuk break pattern). 0 = disable. Recommended: 10-15.',
                'is_public' => false,
            ],
            [
                'key' => 'wa_external_broadcast_break_duration',
                'value' => '2',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Break Duration (detik)',
                'description' => 'Durasi jeda tambahan saat break interval tercapai. Recommended: 2-3 detik.',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('whatsapp_settings')->insertOrIgnore(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('whatsapp_settings')->whereIn('key', [
            'wa_external_broadcast_min_delay',
            'wa_external_broadcast_max_delay',
            'wa_external_broadcast_break_interval',
            'wa_external_broadcast_break_duration',
        ])->delete();
    }
};
