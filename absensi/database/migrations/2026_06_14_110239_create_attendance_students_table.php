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
        Schema::create('attendance_students', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 50)->unique();
            $table->string('nama', 255);
            $table->unsignedBigInteger('kelas_id');
            $table->string('no_hp_ortu', 20)->nullable(); // Parent's phone for notifications
            $table->string('qr_code_path', 255)->nullable(); // Path to QR Code image
            $table->string('foto_profil', 255)->nullable(); // Student profile photo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('nis', 'idx_students_nis');
            $table->index('kelas_id', 'idx_students_kelas');
            $table->index('is_active', 'idx_students_active');
            
            // Foreign Key
            $table->foreign('kelas_id', 'fk_students_kelas')
                  ->references('id')
                  ->on('attendance_classes')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_students');
    }
};
