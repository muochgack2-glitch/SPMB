# WhatsApp Gateway - User Guide

**Sistem Absensi SMK PGRI Blora**  
**Versi:** 1.0  
**Tanggal:** 01 Agustus 2026

---

## 📱 Pengenalan

WhatsApp Gateway adalah fitur untuk mengirim notifikasi otomatis kepada orang tua siswa melalui WhatsApp. Sistem ini terintegrasi dengan absensi QR Code.

### Fitur Baru:
- ✅ **Message Logs** - Lihat riwayat semua pesan yang terkirim
- ✅ **Settings** - Atur konfigurasi gateway tanpa edit kode
- ✅ **Dashboard** - Monitor status koneksi real-time

---

## 🚀 Quick Start

### 1. Akses Dashboard
1. Login ke sistem absensi
2. Klik menu **"WA Gateway"** di sidebar
3. Anda akan melihat dashboard dengan status koneksi

### 2. Hubungkan WhatsApp
Jika status menampilkan "Disconnected":
1. Klik tombol **"Lihat QR Code"**
2. Buka WhatsApp di HP → **Linked Devices** → **Link a Device**
3. Scan QR Code yang muncul
4. Tunggu hingga status berubah menjadi "Connected" (hijau)

### 3. Test Pengiriman
1. Scroll ke section **"Kirim Pesan Manual"**
2. Masukkan nomor WhatsApp (format: 628xxx)
3. Tulis pesan test
4. Klik **"Kirim Pesan"**
5. Cek HP untuk memastikan pesan terkirim

---

## 📨 Message Logs

### Akses Message Logs
Dashboard → Klik tombol **"Message Logs"** (ungu)

### Apa yang Bisa Dilakukan:
- ✅ Lihat semua pesan yang pernah dikirim
- ✅ Filter berdasarkan status (Terkirim/Gagal/Pending)
- ✅ Filter berdasarkan tipe (Manual/Auto Check-In/Check-Out/Alpha)
- ✅ Cari berdasarkan nomor HP atau nama siswa
- ✅ Filter berdasarkan tanggal
- ✅ Klik "Detail" untuk melihat isi pesan lengkap

### Statistik yang Ditampilkan:
- **Total** - Jumlah semua pesan
- **Terkirim** (hijau) - Pesan yang berhasil
- **Gagal** (merah) - Pesan yang error
- **Pending** (kuning) - Pesan dalam antrian
- **Hari Ini** (ungu) - Pesan hari ini

### Tips Troubleshooting:
- Jika banyak pesan "Gagal" → Cek koneksi gateway
- Jika pesan "Pending" lama → Restart gateway
- Klik "Detail" pada pesan gagal untuk lihat error message

---

## ⚙️ Settings

### Akses Settings
Dashboard → Klik tombol **"Settings"** (abu-abu)

### Section 1: Koneksi Gateway
**Gateway URL**
- Default: `http://localhost:3001`
- Ubah jika gateway berjalan di server lain
- Format: `http://IP:PORT`

**Timeout (detik)**
- Rentang: 5-60 detik
- Default: 10 detik
- Naikkan jika koneksi lambat

**Retry Attempts**
- Rentang: 1-5 kali
- Default: 3 kali
- Jumlah percobaan ulang jika gagal kirim

### Section 2: Rate Limiting
**Enable Rate Limiting**
- Toggle ON/OFF
- Default: ON
- Mencegah spam dan batasi kecepatan kirim

**Messages per Minute**
- Rentang: 1-60 pesan
- Default: 20 pesan/menit
- Sesuaikan dengan kapasitas gateway

**Delay Between Messages**
- Rentang: 0-30 detik
- Default: 3 detik
- Jeda antar pengiriman pesan

### Section 3: Fitur Notifikasi
**Auto Send Enabled** (Toggle utama)
- Aktifkan/nonaktifkan semua notifikasi otomatis

**Send on Check-In**
- Kirim notifikasi saat siswa check-in
- Default: ON

**Send on Check-Out**
- Kirim notifikasi saat siswa check-out
- Default: ON

**Send on Alpha**
- Kirim notifikasi saat siswa alpha
- Default: ON

### Section 4: Template Pesan
Customize format pesan dengan variabel:

**Check-In Template**
- Default: `"Siswa {nama} (NIS: {nis}) telah CHECK-IN pada {waktu}."`
- Variabel: `{nama}`, `{nis}`, `{waktu}`

**Check-Out Template**
- Default: `"Siswa {nama} (NIS: {nis}) telah CHECK-OUT pada {waktu}."`
- Variabel: `{nama}`, `{nis}`, `{waktu}`

**Alpha Template**
- Default: `"Siswa {nama} (NIS: {nis}) tidak hadir (ALPHA) pada {tanggal}."`
- Variabel: `{nama}`, `{nis}`, `{tanggal}`

### Menyimpan Pengaturan
1. Ubah setting yang diinginkan
2. Klik **"Simpan Pengaturan"** (biru, kanan bawah)
3. Tunggu notifikasi "Pengaturan berhasil disimpan!"

### Reset ke Default
1. Klik **"Reset ke Default"** (abu-abu, kiri bawah)
2. Konfirmasi dialog yang muncul
3. Semua setting kembali ke nilai awal

---

## 🔄 Alur Kerja Otomatis

### Check-In
```
Siswa scan QR → Sistem catat check-in → Kirim WA ke ortu
```
**Isi Pesan:**
- "Siswa BUDI SANTOSO (NIS: 12345) telah CHECK-IN pada 01/08/2026 07:15:00."
- Nomor tujuan: Dari field "No HP Ortu" di data siswa

### Check-Out
```
Siswa scan QR → Sistem catat check-out → Kirim WA ke ortu
```
**Isi Pesan:**
- "Siswa BUDI SANTOSO (NIS: 12345) telah CHECK-OUT pada 01/08/2026 14:30:00."

### Alpha (Tidak Hadir)
```
Jam batas lewat → Sistem mark alpha → Kirim WA ke ortu
```
**Isi Pesan:**
- "Siswa BUDI SANTOSO (NIS: 12345) tidak hadir (ALPHA) pada 01/08/2026."
- Dijalankan otomatis via cron job

---

## 🛠️ Troubleshooting

### Gateway Disconnected
**Gejala:** Status "Disconnected" (merah)

**Solusi:**
1. Klik **"Lihat QR Code"**
2. Scan ulang dengan WhatsApp
3. Tunggu 5-10 detik
4. Klik **"Refresh"** untuk cek status

### Pesan Tidak Terkirim
**Gejala:** Banyak pesan "Gagal" di logs

**Solusi:**
1. Cek status gateway (harus "Connected")
2. Cek nomor HP siswa (format: 628xxx)
3. Cek Message Logs → Detail → Lihat error message
4. Restart gateway jika perlu

### Gateway Lemot
**Gejala:** Pesan "Pending" lama

**Solusi:**
1. Buka Settings
2. Naikkan "Timeout" menjadi 15-20 detik
3. Turunkan "Messages per Minute" menjadi 10-15
4. Simpan pengaturan

### WhatsApp Ter-banned
**Gejala:** Nomor WA tidak bisa digunakan

**Pencegahan:**
1. Aktifkan Rate Limiting
2. Set "Messages per Minute" maksimal 20
3. Set "Delay Between Messages" minimal 3 detik
4. Jangan kirim broadcast dalam jumlah besar sekaligus

---

## 🎯 Best Practices

### Pengaturan Optimal
```
✅ Rate Limiting: ON
✅ Messages per Minute: 20
✅ Delay Between Messages: 3 detik
✅ Timeout: 10 detik
✅ Retry Attempts: 3
```

### Format Nomor HP
```
✅ BENAR: 6281234567890 (mulai dengan 62)
❌ SALAH: 081234567890 (mulai dengan 08)
❌ SALAH: +6281234567890 (ada tanda +)
```

### Maintenance Rutin
**Harian:**
- Cek status gateway (harus "Connected")
- Cek Message Logs untuk pesan gagal

**Mingguan:**
- Review jumlah pesan terkirim vs gagal
- Bersihkan pesan logs lama (jika perlu)

**Bulanan:**
- Backup database (termasuk tabel whatsapp_messages)
- Review settings dan sesuaikan jika perlu

---

## 📞 Dukungan

### Jika Butuh Bantuan:
1. Cek dokumentasi ini terlebih dahulu
2. Lihat Message Logs untuk error detail
3. Screenshot error dan status gateway
4. Hubungi IT Support

### Informasi Sistem:
- **Gateway URL:** http://localhost:3001
- **Database:** whatsapp_messages, whatsapp_settings
- **Cron Job:** MarkAbsentStudents (untuk alpha)

---

## 🔐 Keamanan

### Data Pribadi
- Nomor HP orang tua tersimpan aman di database
- Pesan tidak disimpan di server WhatsApp
- Hanya admin yang bisa akses Message Logs

### Akses Gateway
- Gateway berjalan lokal (localhost)
- Tidak bisa diakses dari luar
- Session WhatsApp terenkripsi

### Backup
- Backup database secara berkala
- Termasuk tabel whatsapp_messages
- Simpan backup di tempat aman

---

## 📊 Statistik Penggunaan

### Metrik yang Bisa Dipantau:
- Jumlah pesan terkirim per hari
- Success rate (% pesan berhasil)
- Siswa yang sering alpha (dari logs alpha)
- Waktu peak pengiriman (check-in/out)

### Cara Akses Statistik:
1. Buka Message Logs
2. Gunakan filter tanggal
3. Lihat kartu statistik di atas tabel
4. Export data jika perlu analisis lebih lanjut

---

## ❓ FAQ

**Q: Apakah gratis?**  
A: Ya, menggunakan WhatsApp pribadi (tidak ada biaya API)

**Q: Berapa limit pengiriman?**  
A: Tergantung nomor WA, disarankan maksimal 20 pesan/menit

**Q: Apakah harus online terus?**  
A: Gateway server harus running, tapi HP bisa offline (pakai WhatsApp Web)

**Q: Bisa kirim gambar?**  
A: Saat ini hanya support text message

**Q: Bisa broadcast ke semua ortu?**  
A: Fitur broadcast akan ditambahkan di update berikutnya

**Q: Bagaimana cara ganti nomor WA?**  
A: Logout dari gateway → Scan QR dengan nomor WA baru

**Q: Data logs disimpan berapa lama?**  
A: Permanent (sampai di-delete manual)

**Q: Bisa export data logs?**  
A: Ya, melalui filter dan pagination (akan ditambahkan export Excel)

---

## 📝 Changelog

### Version 1.0 (01 Agustus 2026)
- ✅ Message Logs dengan filter dan search
- ✅ Settings management lengkap
- ✅ Auto-logging semua pesan
- ✅ Statistics dashboard
- ✅ Dark mode support
- ✅ Mobile responsive

---

**Butuh Bantuan?**  
Hubungi IT Support atau buka dokumentasi teknis di folder project.

**Happy Messaging! 💬**
