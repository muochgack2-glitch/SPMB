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
        Schema::create('backup_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Operation details
            $table->enum('operation_type', [
                'backup_created',
                'backup_deleted',
                'restore_started',
                'restore_completed',
                'restore_failed',
                'cloud_upload',
                'cloud_download',
                'integrity_check'
            ]);
            
            // References
            $table->unsignedBigInteger('backup_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Status
            $table->enum('status', ['success', 'failed', 'in_progress']);
            
            // Details
            $table->text('details')->nullable()->comment('JSON encoded operation details');
            $table->text('error_message')->nullable();
            $table->decimal('duration_seconds', 10, 2)->nullable();
            
            // Request info
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at')->nullable();
            // No updated_at - logs are immutable
            
            // Foreign keys
            $table->foreign('backup_id')->references('id')->on('database_backups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('created_at');
            $table->index('user_id');
            $table->index('operation_type');
            $table->index('backup_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_activity_logs');
    }
};
