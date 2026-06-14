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
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            
            // File information
            $table->string('filename', 255);
            $table->string('path', 500);
            $table->bigInteger('size_bytes')->unsigned();
            $table->string('md5_hash', 32);
            $table->string('database_name', 100);
            
            // Google Drive fields (integrated)
            $table->string('google_drive_file_id')->nullable();
            $table->text('google_drive_web_link')->nullable();
            $table->timestamp('uploaded_to_drive_at')->nullable();
            
            // Source tracking
            $table->enum('source_type', ['manual', 'auto', 'scheduled', 'pre_operation']);
            $table->text('source_context')->nullable()->comment('Context like: before_new_year, before_delete_ta, etc');
            $table->string('tahun_ajaran_context', 20)->nullable()->comment('Active tahun ajaran during backup');
            
            // Metadata
            $table->integer('total_tables')->unsigned()->default(0);
            $table->bigInteger('estimated_records')->unsigned()->default(0);
            $table->text('backup_notes')->nullable();
            
            // Cloud storage (untuk future enhancement)
            $table->string('cloud_storage_provider', 50)->nullable()->comment('google_drive, dropbox, etc');
            $table->text('cloud_storage_url')->nullable();
            $table->enum('cloud_upload_status', ['pending', 'uploading', 'completed', 'failed'])->nullable();
            $table->timestamp('cloud_uploaded_at')->nullable();
            $table->text('cloud_error_message')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes
            $table->index('created_at');
            $table->index('created_by');
            $table->index('source_type');
            $table->index('cloud_upload_status');
            $table->index('tahun_ajaran_context');
            $table->index('google_drive_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
