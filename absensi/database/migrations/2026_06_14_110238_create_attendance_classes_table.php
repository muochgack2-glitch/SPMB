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
        Schema::create('attendance_classes', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 10); // '10', '11', '12'
            $table->string('jurusan', 100)->nullable(); // 'RPL', 'TKJ', etc
            $table->unsignedBigInteger('wali_kelas_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint on (nama_kelas, tingkat)
            $table->unique(['nama_kelas', 'tingkat'], 'unique_class');
            
            // Indexes
            $table->index('tingkat', 'idx_tingkat');
            $table->index('is_active', 'idx_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_classes');
    }
};
