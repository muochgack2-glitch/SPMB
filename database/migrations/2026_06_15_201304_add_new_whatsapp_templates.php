<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();
        
        // Check if templates already exist before inserting
        $adminRegExists = DB::table('whatsapp_templates')->where('name', 'admin_registration')->exists();
        $phoneAddedExists = DB::table('whatsapp_templates')->where('name', 'phone_number_added')->exists();
        
        $templates = [];
        
        // Add admin_registration template if not exists
        if (!$adminRegExists) {
            $templates[] = [
                'name' => 'admin_registration',
                'label' => 'Notifikasi Pendaftaran oleh Panitia (Manual Entry)',
                'message' => "Assalamu'alaikum {nama},\n\nAnda telah didaftarkan oleh panitia SPMB {sekolah}.\n\n📋 *Detail Pendaftaran:*\nNo. Pendaftaran: {no_pendaftaran}\nNama: {nama}\nJurusan: {jurusan}\n\n✅ Silakan akses portal SPMB untuk melihat detail pendaftaran dan melengkapi data Anda.\n\n🔗 Portal: {portal_url}\n\nJika ada pertanyaan, silakan hubungi panitia.\n\nTerima kasih.\n\nWassalamu'alaikum",
                'description' => 'Pesan otomatis saat petugas/panitia menambahkan pendaftar baru secara manual',
                'type' => 'registration',
                'is_active' => true,
                'auto_send' => true,
                'variables' => json_encode([
                    'nama' => 'Nama lengkap pendaftar',
                    'no_pendaftaran' => 'Nomor pendaftaran',
                    'jurusan' => 'Nama jurusan yang dipilih',
                    'portal_url' => 'URL portal SPMB',
                    'sekolah' => 'Nama sekolah',
                ]),
                'usage_count' => 0,
                'last_used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Add phone_number_added template if not exists
        if (!$phoneAddedExists) {
            $templates[] = [
                'name' => 'phone_number_added',
                'label' => 'Notifikasi Nomor HP Ditambahkan',
                'message' => "Assalamu'alaikum {nama},\n\nNomor HP Anda telah berhasil ditambahkan ke sistem SPMB {sekolah}.\n\n📋 *Detail Pendaftaran:*\nNo. Pendaftaran: {no_pendaftaran}\nNama: {nama}\nJurusan: {jurusan}\nNomor HP: {nomor_hp}\n\n✅ Anda sekarang dapat menerima notifikasi penting terkait pendaftaran.\n\n🔗 Portal: {portal_url}\n\nTerima kasih.\n\nWassalamu'alaikum",
                'description' => 'Pesan otomatis saat petugas menambahkan nomor HP siswa via edit data',
                'type' => 'notification',
                'is_active' => true,
                'auto_send' => true,
                'variables' => json_encode([
                    'nama' => 'Nama lengkap pendaftar',
                    'no_pendaftaran' => 'Nomor pendaftaran',
                    'jurusan' => 'Nama jurusan yang dipilih',
                    'nomor_hp' => 'Nomor HP yang baru ditambahkan',
                    'portal_url' => 'URL portal SPMB',
                    'sekolah' => 'Nama sekolah',
                ]),
                'usage_count' => 0,
                'last_used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Insert templates if any
        if (!empty($templates)) {
            DB::table('whatsapp_templates')->insert($templates);
        }
        
        // Update label for welcome_registration to be more specific
        DB::table('whatsapp_templates')
            ->where('name', 'welcome_registration')
            ->update([
                'label' => 'Pesan Selamat Datang - Pendaftaran Online (Self-Registration)',
                'description' => 'Pesan otomatis untuk pendaftar yang registrasi sendiri via portal online',
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new templates
        DB::table('whatsapp_templates')
            ->whereIn('name', ['admin_registration', 'phone_number_added'])
            ->delete();
            
        // Revert welcome_registration label
        DB::table('whatsapp_templates')
            ->where('name', 'welcome_registration')
            ->update([
                'label' => 'Pesan Selamat Datang - Pendaftaran Berhasil',
                'description' => 'Pesan otomatis yang dikirim setelah pendaftar berhasil registrasi',
                'updated_at' => Carbon::now(),
            ]);
    }
};
