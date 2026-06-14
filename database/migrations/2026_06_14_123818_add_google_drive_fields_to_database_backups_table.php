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
        // Check if table exists first
        if (!Schema::hasTable('database_backups')) {
            // Table doesn't exist yet, skip this migration
            // It will be created by 2026_06_14_140000_create_database_backups_table.php
            return;
        }
        
        Schema::table('database_backups', function (Blueprint $table) {
            // Check if columns don't exist before adding (for backward compatibility)
            if (!Schema::hasColumn('database_backups', 'google_drive_file_id')) {
                $table->string('google_drive_file_id')->nullable()->after('md5_hash');
            }
            if (!Schema::hasColumn('database_backups', 'google_drive_web_link')) {
                $table->text('google_drive_web_link')->nullable()->after('google_drive_file_id');
            }
            if (!Schema::hasColumn('database_backups', 'uploaded_to_drive_at')) {
                $table->timestamp('uploaded_to_drive_at')->nullable()->after('google_drive_web_link');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('database_backups', function (Blueprint $table) {
            // Check if columns exist before dropping
            if (Schema::hasColumn('database_backups', 'google_drive_file_id')) {
                $table->dropColumn('google_drive_file_id');
            }
            if (Schema::hasColumn('database_backups', 'google_drive_web_link')) {
                $table->dropColumn('google_drive_web_link');
            }
            if (Schema::hasColumn('database_backups', 'uploaded_to_drive_at')) {
                $table->dropColumn('uploaded_to_drive_at');
            }
        });
    }
};
