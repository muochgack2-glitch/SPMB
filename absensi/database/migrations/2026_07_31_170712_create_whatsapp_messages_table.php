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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable()->comment('FK to attendance_students');
            $table->string('phone', 20)->comment('Phone number (628xxx format)');
            $table->string('phone_normalized', 20)->nullable()->comment('Normalized phone');
            $table->text('message')->comment('Message content');
            $table->string('type', 50)->default('manual')->comment('manual, auto_checkin, auto_checkout, auto_alpha, broadcast');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->comment('Message status');
            $table->json('response')->nullable()->comment('Gateway response data');
            $table->text('error_message')->nullable()->comment('Error message if failed');
            $table->timestamp('sent_at')->nullable()->comment('Timestamp when sent');
            $table->timestamps();
            
            // Indexes
            $table->index('student_id');
            $table->index('status');
            $table->index('type');
            $table->index('phone');
            $table->index('sent_at');
            
            // Foreign key
            $table->foreign('student_id')
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
        Schema::dropIfExists('whatsapp_messages');
    }
};
