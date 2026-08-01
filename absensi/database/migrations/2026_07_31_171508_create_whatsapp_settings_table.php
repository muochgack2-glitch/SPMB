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
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Setting key');
            $table->text('value')->nullable()->comment('Setting value');
            $table->enum('type', ['string', 'integer', 'boolean', 'json'])->default('string')->comment('Value type');
            $table->string('group', 50)->default('general')->comment('Setting group');
            $table->text('description')->nullable()->comment('Setting description');
            $table->timestamps();
            
            // Indexes
            $table->index('key');
            $table->index('group');
        });

        // Insert default settings
        DB::table('whatsapp_settings')->insert([
            // Connection Settings
            [
                'key' => 'gateway_url',
                'value' => 'http://localhost:3001',
                'type' => 'string',
                'group' => 'connection',
                'description' => 'WhatsApp Gateway base URL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'gateway_timeout',
                'value' => '10',
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Request timeout in seconds',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'retry_attempts',
                'value' => '3',
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Number of retry attempts on failure',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Rate Limiting
            [
                'key' => 'rate_limit_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'rate_limiting',
                'description' => 'Enable rate limiting for message sending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'messages_per_minute',
                'value' => '20',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'description' => 'Maximum messages per minute',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'delay_between_messages',
                'value' => '3',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'description' => 'Delay in seconds between messages',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Feature Toggles
            [
                'key' => 'auto_send_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable automatic notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'send_on_checkin',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Send notification on check-in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'send_on_checkout',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Send notification on check-out',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'send_on_alpha',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Send notification when marked as alpha',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Notification Templates
            [
                'key' => 'checkin_message_template',
                'value' => 'Siswa {nama} (NIS: {nis}) telah CHECK-IN pada {waktu}.',
                'type' => 'string',
                'group' => 'templates',
                'description' => 'Check-in notification message template',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'checkout_message_template',
                'value' => 'Siswa {nama} (NIS: {nis}) telah CHECK-OUT pada {waktu}.',
                'type' => 'string',
                'group' => 'templates',
                'description' => 'Check-out notification message template',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'alpha_message_template',
                'value' => 'Siswa {nama} (NIS: {nis}) tidak hadir (ALPHA) pada {tanggal}.',
                'type' => 'string',
                'group' => 'templates',
                'description' => 'Alpha notification message template',
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
        Schema::dropIfExists('whatsapp_settings');
    }
};
