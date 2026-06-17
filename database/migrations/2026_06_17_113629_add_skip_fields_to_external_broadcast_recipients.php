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
        Schema::table('external_broadcast_recipients', function (Blueprint $table) {
            $table->string('matched_status', 50)->nullable()->after('matched_pendaftar_id');
            $table->boolean('will_be_skipped')->default(false)->after('matched_status');
            
            $table->index('will_be_skipped');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_broadcast_recipients', function (Blueprint $table) {
            $table->dropIndex(['will_be_skipped']);
            $table->dropColumn(['matched_status', 'will_be_skipped']);
        });
    }
};
