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
        Schema::table('pendaftar', function (Blueprint $table) {
            // Track apakah WA welcome sudah berhasil terkirim
            $table->boolean('wa_welcome_sent')->default(false)->after('tahun_ajaran');
            $table->timestamp('wa_welcome_sent_at')->nullable()->after('wa_welcome_sent');
            $table->string('wa_welcome_sent_to', 20)->nullable()->after('wa_welcome_sent_at');
            $table->enum('wa_welcome_recipient_type', ['siswa', 'ortu', 'wali'])->nullable()->after('wa_welcome_sent_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn([
                'wa_welcome_sent',
                'wa_welcome_sent_at',
                'wa_welcome_sent_to',
                'wa_welcome_recipient_type'
            ]);
        });
    }
};
