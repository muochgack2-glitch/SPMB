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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->enum('action', ['qr_scan', 'check_in', 'check_out', 'notification', 'reject', 'error']);
            $table->text('message')->nullable();
            $table->text('response')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('student_id', 'idx_logs_student');
            $table->index('action', 'idx_logs_action');
            $table->index('created_at', 'idx_logs_created_at');
            
            // Foreign Key
            $table->foreign('student_id', 'fk_logs_student')
                  ->references('id')
                  ->on('attendance_students')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
