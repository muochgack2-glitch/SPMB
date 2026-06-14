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
        Schema::table('setting_system', function (Blueprint $table) {
            $table->string('google_drive_folder_id')->nullable()->after('updated_at');
            $table->string('google_drive_service_account_json')->nullable()->after('google_drive_folder_id');
            $table->boolean('google_drive_auto_upload')->default(false)->after('google_drive_service_account_json');
            $table->boolean('google_drive_keep_local')->default(true)->after('google_drive_auto_upload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setting_system', function (Blueprint $table) {
            $table->dropColumn([
                'google_drive_folder_id',
                'google_drive_service_account_json',
                'google_drive_auto_upload',
                'google_drive_keep_local'
            ]);
        });
    }
};
