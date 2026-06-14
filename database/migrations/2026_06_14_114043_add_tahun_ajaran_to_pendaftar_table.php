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
        if (!Schema::hasColumn('pendaftar', 'tahun_ajaran')) {
            Schema::table('pendaftar', function (Blueprint $table) {
                $table->string('tahun_ajaran', 20)->nullable()->after('gelombang')->index();
            });
            
            // Set default value untuk existing records
            DB::table('pendaftar')->whereNull('tahun_ajaran')->update([
                'tahun_ajaran' => '2026/2027'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pendaftar', 'tahun_ajaran')) {
            Schema::table('pendaftar', function (Blueprint $table) {
                $table->dropColumn('tahun_ajaran');
            });
        }
    }
};
