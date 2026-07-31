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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('check_in_photo', 255)->nullable(); // Path to check-in photo
            $table->string('check_out_photo', 255)->nullable(); // Path to check-out photo
            $table->enum('status', ['hadir', 'terlambat', 'alpha', 'izin', 'sakit']);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Constraints
            $table->unique(['student_id', 'date'], 'unique_student_date');
            
            // Indexes
            $table->index('date', 'idx_date');
            $table->index('status', 'idx_status');
            $table->index(['student_id', 'date'], 'idx_student_date');
            
            // Foreign Key
            $table->foreign('student_id', 'fk_records_student')
                  ->references('id')
                  ->on('attendance_students')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
