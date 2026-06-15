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
        
        $templates = [];
        
        // Template 1: daftar_ulang_verified
        if (!DB::table('whatsapp_templates')->where('name', 'daftar_ulang_verified')->exists()) {
            $templates[] = [
                'name' => 'daftar_ulang_verified',
                'label' => 'Konfirmasi Verifikasi Daftar Ulang',
                'message' => "Assalamu'alaikum {nama},\n\n✅ *Verifikasi Daftar Ulang Berhasil*\n\nSelamat! Daftar ulang Anda telah diverifikasi oleh panitia.\n\n📋 *Detail:*\nNo. Pendaftaran: {no_pendaftaran}\nNama: {nama}\nJurusan: {jurusan}\nUkuran Kaos: {ukuran_kaos}\n\n📝 *Langkah Selanjutnya:*\n1. Lengkapi biodata di portal SPMB\n2. Siapkan dokumen yang diperlukan\n3. Tunggu informasi jadwal orientasi siswa baru\n\n🔗 Portal: {portal_url}\n\nSelamat bergabung dengan keluarga besar {sekolah}!\n\nTerima kasih.\n\nWassalamu'alaikum",
                'description' => 'Notifikasi otomatis saat petugas memverifikasi daftar ulang siswa',
                'type' => 'notification',
                'is_active' => true,
                'auto_send' => true,
                'variables' => json_encode([
                    'nama' => 'Nama lengkap pendaftar',
                    'no_pendaftaran' => 'Nomor pendaftaran',
                    'jurusan' => 'Nama jurusan yang dipilih',
                    'ukuran_kaos' => 'Ukuran kaos yang dipilih (S/M/L/XL/XXL/JUMBO)',
                    'portal_url' => 'URL portal SPMB',
                    'sekolah' => 'Nama sekolah',
                ]),
                'usage_count' => 0,
                'last_used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Template 2: student_accepted
        if (!DB::table('whatsapp_templates')->where('name', 'student_accepted')->exists()) {
            $templates[] = [
                'name' => 'student_accepted',
                'label' => 'Notifikasi Siswa Diterima',
                'message' => "Assalamu'alaikum {nama},\n\n🎉 *SELAMAT! Anda DITERIMA*\n\nKami dengan senang hati mengumumkan bahwa Anda telah DITERIMA di {sekolah}!\n\n📋 *Detail Penerimaan:*\nNo. Pendaftaran: {no_pendaftaran}\nNama: {nama}\nJurusan: {jurusan}\n\n📝 *Langkah Selanjutnya:*\n1. Lakukan daftar ulang sesuai jadwal\n2. Lengkapi biodata di portal SPMB\n3. Siapkan dokumen yang diperlukan\n4. Tunggu informasi jadwal orientasi\n\n🔗 Portal: {portal_url}\n\nSelamat bergabung dengan keluarga besar {sekolah}!\n\nWassalamu'alaikum",
                'description' => 'Notifikasi saat siswa diterima oleh panitia',
                'type' => 'notification',
                'is_active' => true,
                'auto_send' => false,
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
        
        // Template 3: enrollment_reminder
        if (!DB::table('whatsapp_templates')->where('name', 'enrollment_reminder')->exists()) {
            $templates[] = [
                'name' => 'enrollment_reminder',
                'label' => 'Pengingat Daftar Ulang',
                'message' => "Assalamu'alaikum {nama},\n\n⏰ *Pengingat Daftar Ulang*\n\nAnda telah DITERIMA di {sekolah}, namun belum melakukan daftar ulang.\n\n📋 *Detail:*\nNo. Pendaftaran: {no_pendaftaran}\nNama: {nama}\nJurusan: {jurusan}\n\n⚠️ *Penting:*\nSegera lakukan daftar ulang untuk mengamankan kursi Anda.\n\n📝 *Cara Daftar Ulang:*\n1. Kunjungi portal SPMB\n2. Upload bukti pembayaran\n3. Pilih ukuran seragam\n4. Tunggu verifikasi panitia\n\n🔗 Portal: {portal_url}\n\nJangan sampai terlewat!\n\nWassalamu'alaikum",
                'description' => 'Pengingat untuk siswa yang sudah diterima tapi belum daftar ulang',
                'type' => 'notification',
                'is_active' => true,
                'auto_send' => false,
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
        
        // Template 4: orientation_info
        if (!DB::table('whatsapp_templates')->where('name', 'orientation_info')->exists()) {
            $templates[] = [
                'name' => 'orientation_info',
                'label' => 'Informasi Jadwal Orientasi (MPLS)',
                'message' => "Assalamu'alaikum {nama},\n\n📅 *Informasi Masa Orientasi Siswa Baru*\n\nBerikut jadwal Masa Pengenalan Lingkungan Sekolah (MPLS):\n\n📋 *Peserta:*\nNama: {nama}\nNo. Pendaftaran: {no_pendaftaran}\nJurusan: {jurusan}\n\n📅 *Jadwal MPLS:*\nTanggal: {tanggal_mpls}\nWaktu: {waktu_mpls}\nTempat: {sekolah}\n\n📝 *Yang Perlu Dibawa:*\n1. Kartu peserta (download di portal)\n2. Alat tulis\n3. Seragam (jika sudah diambil)\n\n🔗 Portal: {portal_url}\n\nSampai jumpa!\n\nWassalamu'alaikum",
                'description' => 'Informasi jadwal masa orientasi siswa baru (MPLS)',
                'type' => 'notification',
                'is_active' => true,
                'auto_send' => false,
                'variables' => json_encode([
                    'nama' => 'Nama lengkap pendaftar',
                    'no_pendaftaran' => 'Nomor pendaftaran',
                    'jurusan' => 'Nama jurusan yang dipilih',
                    'tanggal_mpls' => 'Tanggal MPLS',
                    'waktu_mpls' => 'Waktu MPLS',
                    'portal_url' => 'URL portal SPMB',
                    'sekolah' => 'Nama sekolah',
                ]),
                'usage_count' => 0,
                'last_used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Template 5: uniform_ready
        if (!DB::table('whatsapp_templates')->where('name', 'uniform_ready')->exists()) {
            $templates[] = [
                'name' => 'uniform_ready',
                'label' => 'Notifikasi Seragam Siap Diambil',
                'message' => "Assalamu'alaikum {nama},\n\n👕 *Seragam Siap Diambil*\n\nSeragam Anda sudah siap untuk diambil!\n\n📋 *Detail:*\nNama: {nama}\nNo. Pendaftaran: {no_pendaftaran}\nUkuran Kaos: {ukuran_kaos}\n\n📍 *Lokasi Pengambilan:*\n{sekolah}\n{alamat_sekolah}\n\n⏰ *Jam Operasional:*\nSenin - Jumat: 08.00 - 14.00\nSabtu: 08.00 - 12.00\n\n📝 *Dokumen yang Dibawa:*\n- KTP/Kartu Pelajar\n- Bukti pembayaran\n- Nomor pendaftaran: {no_pendaftaran}\n\nTerima kasih.\n\nWassalamu'alaikum",
                'description' => 'Notifikasi bahwa seragam siswa sudah siap diambil',
                'type' => 'notification',
                'is_active' => true,
                'auto_send' => false,
                'variables' => json_encode([
                    'nama' => 'Nama lengkap pendaftar',
                    'no_pendaftaran' => 'Nomor pendaftaran',
                    'ukuran_kaos' => 'Ukuran kaos',
                    'sekolah' => 'Nama sekolah',
                    'alamat_sekolah' => 'Alamat sekolah',
                ]),
                'usage_count' => 0,
                'last_used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Insert all templates
        if (!empty($templates)) {
            DB::table('whatsapp_templates')->insert($templates);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('whatsapp_templates')
            ->whereIn('name', [
                'daftar_ulang_verified',
                'student_accepted',
                'enrollment_reminder',
                'orientation_info',
                'uniform_ready',
            ])
            ->delete();
    }
};
