# 📖 User Manual - Sistem Absensi QR Code Scanner

**Versi:** 1.0  
**Tanggal:** 14 Juni 2026

---

## Daftar Isi

1. [Pengenalan Sistem](#pengenalan-sistem)
2. [Panduan untuk Siswa](#panduan-untuk-siswa)
3. [Panduan untuk Petugas Scanner](#panduan-untuk-petugas-scanner)
4. [Panduan untuk Admin/Operator](#panduan-untuk-adminoperator)
5. [Panduan untuk Wali Kelas](#panduan-untuk-wali-kelas)
6. [FAQ (Pertanyaan yang Sering Diajukan)](#faq)
7. [Troubleshooting](#troubleshooting)

---

## Pengenalan Sistem

### Apa itu Sistem Absensi QR Code?

Sistem Absensi QR Code adalah aplikasi web yang memudahkan pencatatan kehadiran siswa menggunakan QR Code. Setiap siswa memiliki QR Code unik yang dapat di-scan saat check-in (datang) dan check-out (pulang).

### Fitur Utama

✅ **Check-In & Check-Out Otomatis** - Scan QR Code untuk absen masuk dan pulang  
✅ **Foto Capture** - Sistem otomatis mengambil foto saat scan untuk verifikasi  
✅ **Notifikasi WhatsApp** - Orang tua menerima notifikasi real-time  
✅ **Dashboard Real-Time** - Monitoring kehadiran siswa langsung  
✅ **Laporan Lengkap** - Export Excel, laporan harian, bulanan, per siswa  
✅ **Auto Alpha** - Otomatis tandai siswa yang tidak hadir

### Kategori Pengguna

| Pengguna | Hak Akses | Fungsi Utama |
|----------|-----------|--------------|
| **Siswa** | - | Menerima QR Code untuk absensi |
| **Petugas Scanner** | Scanner Interface | Operasikan QR Scanner |
| **Admin/Operator** | Full Access | Kelola siswa, kelas, settings, laporan |
| **Wali Kelas** | Read Only | Lihat laporan kelas yang diampu |

---

## Panduan untuk Siswa

### 1. Mendapatkan QR Code Anda

#### Opsi A: Print QR Code

1. Minta QR Code Anda ke admin/operator sekolah
2. Admin akan memberikan print-out QR Code dengan informasi:
   - Nama Lengkap
   - NIS (Nomor Induk Siswa)
   - Kelas
3. **Simpan QR Code ini dengan baik!**

#### Opsi B: QR Code Digital (Smartphone)

1. Minta link QR Code digital dari admin
2. Format link: `http://[alamat-sekolah]/attendance/qr/[NIS-anda]`
3. Simpan link di Home Screen smartphone:
   - **Android:** Buka Chrome > Menu (⋮) > "Add to Home Screen"
   - **iPhone:** Buka Safari > Share (□↑) > "Add to Home Screen"

### 2. Cara Absen Check-In (Masuk Sekolah)

**Waktu:** 07:00 - 09:00 WIB

1. **Siapkan QR Code Anda**
   - Print-out, atau
   - Buka QR Code di smartphone

2. **Datangi Pos Scanner**
   - Biasanya di pintu masuk sekolah
   - Cari meja dengan monitor dan webcam

3. **Tunjukkan QR Code ke Webcam**
   - Jarak ideal: 10-30 cm dari webcam
   - Pastikan QR Code jelas terlihat (tidak blur)
   - Tunggu bunyi **"BEEP"** (tanda sukses)

4. **Lihat Hasil di Layar**
   - ✅ **Berhasil:** Nama & status (Hadir/Terlambat) ditampilkan
   - ❌ **Gagal:** Coba lagi atau hubungi petugas

5. **Foto Anda Akan Diambil Otomatis**
   - Smile! 😊
   - Foto ini untuk verifikasi dan akan dikirim ke orang tua

### 3. Cara Absen Check-Out (Pulang Sekolah)

**Waktu:** 15:00 - 17:00 WIB

1. Ulangi langkah yang sama seperti check-in
2. Sistem akan otomatis detect sebagai check-out
3. Notifikasi dikirim ke orang tua

### 4. Status Kehadiran

| Status | Keterangan | Waktu Check-In |
|--------|------------|----------------|
| 🟢 **Hadir** | Datang tepat waktu | 07:00 - 07:15 |
| 🟡 **Terlambat** | Datang terlambat | 07:16 - 09:00 |
| 🔴 **Alpha** | Tidak hadir tanpa keterangan | Tidak absen |
| 🟠 **Sakit** | Sakit dengan surat keterangan | Manual by admin |
| 🔵 **Izin** | Izin dengan surat keterangan | Manual by admin |

### 5. Notifikasi WhatsApp ke Orang Tua

Setiap kali Anda absen, orang tua akan menerima notifikasi WhatsApp:

**Contoh Notifikasi Check-In:**
```
🏫 SMK NEGERI 1 JAKARTA
✅ ABSENSI MASUK

Nama: BUDI SANTOSO
Kelas: 12 RPL A
Waktu: 07:10 WIB
Status: Hadir ✅

Terima kasih.
```

**Contoh Notifikasi Terlambat:**
```
🏫 SMK NEGERI 1 JAKARTA
⚠️ ABSENSI MASUK

Nama: BUDI SANTOSO
Kelas: 12 RPL A
Waktu: 07:25 WIB
Status: Terlambat ⚠️

Terlambat 10 menit. Mohon perhatian orang tua.
```

### 6. Tips untuk Siswa

✅ **DO:**
- Datang tepat waktu (sebelum 07:15)
- Simpan QR Code dengan baik
- Pastikan smartphone full battery (jika pakai QR digital)
- Check notifikasi WA orang tua untuk konfirmasi

❌ **DON'T:**
- Jangan berbagi QR Code ke teman lain
- Jangan edit/manipulasi QR Code
- Jangan scan QR Code orang lain (bisa kena sanksi!)
- Jangan lupa check-out saat pulang

---

## Panduan untuk Petugas Scanner

### 1. Membuka Scanner Interface

1. **Buka Browser** (Chrome/Edge recommended)
2. **Akses URL:** `http://[alamat-server]/attendance/scanner`
3. **Allow Webcam Access** saat browser meminta izin
4. **Scanner Siap!** - Layar akan menampilkan video webcam

### 2. Operasional Scanner

#### Mode Scanner

Ada 2 mode:
- **Check-In Mode** (default) - Untuk absen masuk
- **Check-Out Mode** - Untuk absen pulang

**Cara Ganti Mode:**
- Klik tombol **"CHECK IN"** atau **"CHECK OUT"** di atas video

#### Alur Scan Normal

1. Siswa tunjukkan QR Code
2. Arahkan QR Code ke tengah area video
3. Tunggu deteksi otomatis (1-2 detik)
4. **Beep Sound** - Scan berhasil
5. **Hasil Ditampilkan** di layar:
   - Foto siswa
   - Nama & Kelas
   - Status (Hadir/Terlambat)
   - Waktu scan
6. Hasil otomatis hilang setelah 3 detik

#### Jika Scan Gagal

**Error: "Siswa tidak ditemukan"**
- QR Code invalid atau rusak
- Minta siswa untuk print QR Code baru dari admin

**Error: "Sudah absen hari ini"**
- Siswa sudah check-in sebelumnya
- Cek dashboard untuk konfirmasi

**Error: "Di luar jam absen"**
- Waktu check-in sudah lewat (> 09:00)
- Hubungi admin untuk input manual

**Error: "Siswa tidak aktif"**
- Siswa sudah tidak aktif di sistem
- Hubungi admin

### 3. Fungsi REJECT

Jika ada scan yang mencurigakan (bukan orang yang seharusnya):

1. **Klik tombol "REJECT"** di hasil scan
2. Masukkan alasan reject (opsional)
3. Record absensi akan dihapus
4. Log reject tersimpan untuk audit

**Kapan Menggunakan REJECT:**
- Foto scan tidak sesuai dengan siswa
- QR Code dipinjamkan ke orang lain
- Aktivitas mencurigakan

### 4. Monitoring Real-Time

**Lihat Dashboard:**
- Buka tab baru: `http://[alamat-server]/attendance/dashboard`
- Lihat statistik hari ini
- Monitor siswa yang sudah/belum absen

**Shortcut Keyboard:**
- `Alt + S` - Kembali ke Scanner
- `Alt + D` - Buka Dashboard
- `Alt + R` - Refresh Dashboard

### 5. Shift Petugas

#### Shift Pagi (Check-In)
- **Waktu:** 06:45 - 09:15
- **Tugas:**
  - Setup scanner 15 menit sebelum jam masuk
  - Monitor scan siswa
  - Catat masalah/kendala

#### Shift Siang (Check-Out)
- **Waktu:** 14:45 - 17:00
- **Tugas:**
  - Setup scanner
  - Pastikan semua siswa check-out
  - Laporan ke admin jika ada anomali

### 6. Troubleshooting Petugas

| Masalah | Solusi |
|---------|--------|
| Webcam tidak aktif | Refresh halaman, allow webcam access |
| QR tidak terdeteksi | Minta siswa dekatkan/jauhkan QR Code |
| Scanner freeze | Refresh halaman (Ctrl+R atau F5) |
| Internet lambat | Cek koneksi, hubungi IT |
| Foto tidak muncul | Cek lighting ruangan (terlalu gelap?) |

---

## Panduan untuk Admin/Operator

### 1. Dashboard Admin

**URL:** `http://[alamat-server]/attendance/dashboard`

#### Statistik Hari Ini

Dashboard menampilkan:
- 🟢 Jumlah Hadir
- 🟡 Jumlah Terlambat
- 🔴 Jumlah Alpha
- 🟠 Jumlah Sakit
- 🔵 Jumlah Izin

#### Filter & Pencarian

- **Filter by Kelas:** Dropdown pilih kelas
- **Filter by Tanggal:** Pilih tanggal tertentu
- **Auto-Refresh:** Setiap 30 detik

#### Lihat Foto Check-In/Out

- Klik thumbnail foto di tabel
- Foto full-size muncul di lightbox
- Klik X atau di luar foto untuk tutup

### 2. Manajemen Siswa

**Menu:** Students

#### Tambah Siswa Baru

1. **Klik "Add New Student"**
2. **Isi Form:**
   - NIS (unique, wajib)
   - Nama Lengkap
   - Kelas (dropdown)
   - No HP Orang Tua (format: 628xxx)
   - Upload Foto Profil (opsional)
3. **Klik "Save"**
4. **QR Code Auto-Generated!**
5. **Print QR Code:**
   - Klik "View QR"
   - Klik tombol "Print"

#### Import Siswa dari Excel

**Untuk import banyak siswa sekaligus:**

1. **Download Template Excel**
   - Menu: Students > Import Excel
   - Klik "Download Template"

2. **Isi Template**
   - Kolom A: NIS (unik, no duplikat)
   - Kolom B: Nama Lengkap
   - Kolom C: Kelas ID (lihat referensi di halaman import)
   - Kolom D: No HP Orang Tua (628xxx)
   
   **Contoh:**
   ```
   NIS    | Nama           | Kelas ID | No HP Ortu
   24001  | Budi Santoso   | 1        | 628123456789
   24002  | Ani Wijaya     | 1        | 628987654321
   ```

3. **Upload File Excel**
   - Pilih file yang sudah diisi
   - Klik "Import"
   - Tunggu proses (progress bar muncul)

4. **QR Code Generated Otomatis**
   - Sistem akan generate QR untuk semua siswa baru
   - Gunakan command untuk batch print (lihat section Print Batch)

#### Print QR Batch

**Via Command Line:**

```bash
# Generate QR untuk semua siswa yang belum punya
php artisan attendance:generate-qr --missing

# Generate ulang QR untuk semua siswa
php artisan attendance:generate-qr --all

# Generate QR untuk NIS tertentu
php artisan attendance:generate-qr --nis=24001 --nis=24002
```

**Print Manual:**
1. Menu: Students
2. Klik icon QR di kolom actions
3. Halaman QR terbuka
4. Klik "Print" (Ctrl+P)
5. Ulangi untuk siswa lain

### 3. Manajemen Kelas

**Menu:** Classes

#### Tambah Kelas Baru

1. Klik "Add New Class"
2. Isi form:
   - Nama Kelas: (contoh: "12 RPL A")
   - Tingkat: 10/11/12
   - Jurusan: (contoh: "RPL", "AKL", "MPLB")
   - Wali Kelas (opsional)
3. Klik "Save"

#### Edit/Hapus Kelas

- Edit: Klik icon pensil
- Hapus: Klik icon trash (WARNING: siswa di kelas harus dipindah dulu!)
- Toggle Active: Klik switch di kolom "Active"

### 4. Input Manual Sakit/Izin

Jika siswa sakit/izin dan ada surat keterangan:

**Cara 1: Via Dashboard**
1. Menu: Dashboard
2. Cari siswa di daftar "Belum Absen"
3. Klik nama siswa
4. Pilih "Mark as Sakit" atau "Mark as Izin"
5. Masukkan keterangan (opsional)
6. Save

**Cara 2: Via Student Detail**
1. Menu: Students
2. Klik nama siswa
3. Tab "Attendance History"
4. Klik "Add Manual Attendance"
5. Pilih Status: Sakit/Izin
6. Masukkan notes
7. Save

### 5. Laporan & Export

#### Laporan Harian

**Menu:** Reports > Daily Report

1. Pilih tanggal
2. Filter kelas (opsional)
3. Klik "Generate"
4. Preview ditampilkan
5. **Export to Excel:** Klik "Export to Excel"

**File Excel akan berisi:**
- Daftar siswa hadir (dengan foto indicator)
- Daftar siswa terlambat
- Daftar siswa tidak hadir
- Summary statistik

#### Laporan Bulanan

**Menu:** Reports > Monthly Report

1. Pilih bulan & tahun
2. Filter kelas (opsional)
3. Klik "Generate"
4. **Rekapitulasi per siswa** ditampilkan:
   - Total Hadir
   - Total Terlambat
   - Total Sakit
   - Total Izin
   - Total Alpha
   - Persentase Kehadiran
5. Export to Excel

#### Laporan Per Siswa

**Menu:** Students > [Pilih Siswa] > View Details

- Tab "Attendance History" menampilkan:
  - History kehadiran lengkap
  - Foto check-in/out (thumbnail)
  - Status setiap hari
  - Statistik total
- Export history siswa to Excel

### 6. Settings / Konfigurasi

**Menu:** Settings

#### Pengaturan Waktu

| Setting | Default | Keterangan |
|---------|---------|------------|
| **Check-In Time** | 07:00 | Jam buka absen masuk |
| **Check-Out Time** | 15:00 | Jam buka absen pulang |
| **Tolerance Minutes** | 15 | Toleransi keterlambatan (menit) |
| **Cutoff Time** | 09:00 | Batas waktu mark alpha otomatis |

**Contoh Timeline:**
```
07:00 ─── Check-in opens (Hadir mulai)
07:15 ─── Tolerance ends (Terlambat mulai)
09:00 ─── Cutoff time (Auto Alpha)
15:00 ─── Check-out opens
```

#### Pengaturan Notifikasi

- **Enable Parent Notification:** ON/OFF
  - Matikan jika tidak ingin kirim notifikasi WA
- **Include Photo in Notification:** ON/OFF
  - Catatan: Gateway saat ini hanya support text message

#### Pengaturan Umum

- **School Name:** Nama sekolah (muncul di notifikasi)

#### Test Notifikasi WhatsApp

1. Scroll ke bagian "Test Notification"
2. Masukkan nomor HP test (628xxx)
3. Klik "Send Test Notification"
4. Cek WhatsApp apakah pesan masuk

**Jika gagal:**
- Cek WhatsApp Gateway status (lihat section Gateway)
- Pastikan nomor HP valid

### 7. WhatsApp Gateway Management

#### Start Gateway

**Windows:**
```cmd
cd whatsapp-server-absensi
npm start
```

**Linux/Mac:**
```bash
cd whatsapp-server-absensi
npm start
```

#### Scan QR Code

1. Gateway akan generate QR Code di terminal
2. **ATAU** akses di browser: `http://localhost:3001/qr`
3. Scan QR dengan WhatsApp:
   - Buka WhatsApp
   - Menu (⋮) > "Linked Devices"
   - "Link a Device"
   - Scan QR Code yang muncul

**Important:** Gunakan nomor WhatsApp khusus untuk Absensi (bukan nomor pribadi!)

#### Cek Gateway Status

**Via Browser:**
```
http://localhost:3001/status
```

**Expected Response:**
```json
{
  "status": "connected",
  "phone": "628xxx",
  "uptime": "2 hours"
}
```

#### Restart Gateway

**Jika gateway error/freeze:**

1. Stop process (Ctrl+C di terminal)
2. Start ulang: `npm start`
3. **ATAU** via API:
   ```bash
   curl -X POST http://localhost:3001/restart
   ```

### 8. Auto Alpha (Scheduled Job)

Sistem akan **otomatis mark siswa sebagai Alpha** jika belum check-in setelah **Cutoff Time** (default: 09:00).

#### Setup Cron Job

**Windows (Task Scheduler):**

Lihat panduan lengkap di: [`CRON_SETUP.md`](CRON_SETUP.md)

1. Open Task Scheduler
2. Create Basic Task:
   - Name: "Laravel Schedule - Attendance"
   - Trigger: Daily, every 1 minute
   - Action: Start a program
   - Program: `php`
   - Arguments: `artisan schedule:run`
   - Start in: `C:\path\to\absensi`

**Linux (Crontab):**

```bash
crontab -e
```

Tambahkan baris:
```
* * * * * cd /path/to/absensi && php artisan schedule:run >> /dev/null 2>&1
```

#### Test Manual

```bash
php artisan attendance:mark-absent
```

**Output:**
```
📊 Mark Absent Students Summary

Total Students: 100
✅ Marked as Absent: 15
⏭️  Already Recorded: 80
⏭️  Inactive (Skipped): 5

Students marked as absent today:
┌────────┬──────────────────┬─────────┐
│ NIS    │ Name             │ Class   │
├────────┼──────────────────┼─────────┤
│ 24001  │ Student Name 1   │ 12 RPL  │
│ 24002  │ Student Name 2   │ 11 AKL  │
...
```

---

## Panduan untuk Wali Kelas

### 1. Akses Dashboard

**URL:** `http://[alamat-server]/attendance/dashboard`

#### Lihat Kehadiran Kelas Hari Ini

1. Login dengan akun wali kelas
2. **Filter Kelas:** Pilih kelas yang diampu
3. Dashboard menampilkan:
   - Statistik kelas hari ini
   - Daftar siswa hadir (dengan foto & waktu)
   - Daftar siswa belum hadir

#### Monitoring Real-Time

- Dashboard auto-refresh setiap 30 detik
- Klik "Refresh" manual untuk update seketika

### 2. Laporan Kelas

#### Laporan Harian Kelas

**Menu:** Reports > Daily Report

1. Pilih tanggal
2. **Pilih Kelas:** Kelas yang diampu
3. Generate report
4. Export to Excel jika perlu

**Gunakan untuk:**
- Rekap harian kehadiran
- Report ke kepala sekolah
- Evaluasi keterlambatan siswa

#### Laporan Bulanan Kelas

**Menu:** Reports > Monthly Report

1. Pilih bulan
2. Pilih kelas
3. Generate
4. Lihat rekapitulasi:
   - Siswa dengan kehadiran tertinggi
   - Siswa dengan keterlambatan terbanyak
   - Siswa dengan alpha terbanyak

**Gunakan untuk:**
- Evaluasi bulanan
- Identifikasi siswa bermasalah
- Pembinaan siswa

### 3. Lihat Detail Siswa

**Menu:** Students

1. Filter by kelas yang diampu
2. Klik nama siswa
3. Tab "Attendance History":
   - Lihat history kehadiran lengkap
   - Foto check-in/check-out
   - Statistik kehadiran siswa

**Gunakan untuk:**
- Panggilan orang tua
- Pembinaan individu
- Evaluasi perkembangan siswa

### 4. Koordinasi dengan Admin

Wali kelas **tidak bisa** input/edit data, namun bisa:

✅ **Request ke Admin:**
- Input manual Sakit/Izin (jika ada surat)
- Update data siswa
- Print ulang QR Code siswa

---

## FAQ (Pertanyaan yang Sering Diajukan)

### Umum

**Q: Apakah bisa absen tanpa smartphone?**  
A: Ya! QR Code bisa di-print dan dibawa dalam bentuk fisik (kertas/kartu).

**Q: Bagaimana jika lupa bawa QR Code?**  
A: Hubungi petugas/admin untuk input manual. Siswa bisa print QR Code baru di sekolah.

**Q: Apakah QR Code bisa expire (kedaluwarsa)?**  
A: Tidak. QR Code berlaku selamanya selama siswa masih aktif.

**Q: Bagaimana jika pindah kelas?**  
A: Admin akan update data kelas. QR Code tetap sama, tidak perlu print ulang.

### Untuk Siswa

**Q: Bolehkah scan QR Code teman?**  
A: TIDAK! Ini termasuk **kecurangan** dan bisa kena sanksi. Sistem akan detect via foto.

**Q: Bagaimana jika sakit/izin?**  
A: Bawa surat keterangan ke wali kelas/admin. Mereka akan input manual.

**Q: Apakah orang tua selalu dapat notifikasi?**  
A: Ya, setiap kali absen (check-in dan check-out). Pastikan nomor HP orang tua benar.

**Q: Lupa check-out, apa yang terjadi?**  
A: Tidak masalah untuk hari itu, tapi check-out penting untuk tracking jam keluar. Usahakan tetap check-out.

### Untuk Petugas

**Q: QR Code tidak terdeteksi, kenapa?**  
A: Kemungkinan:
- QR blur/rusak → Print ulang
- Lighting kurang → Tambah cahaya
- QR terlalu jauh/dekat → Sesuaikan jarak (10-30cm)

**Q: Bagaimana jika scanner freeze?**  
A: Refresh halaman (F5 atau Ctrl+R).

**Q: Siswa scan berulang kali di hari yang sama, apa yang terjadi?**  
A: Sistem akan reject scan kedua dengan pesan "Sudah absen hari ini".

### Untuk Admin

**Q: Bagaimana cara backup data?**  
A: Backup database MySQL dan folder `storage/app/attendance/` secara berkala.

**Q: Bagaimana menambah user admin/petugas?**  
A: Via management user Laravel (sesuaikan dengan auth system yang dipakai).

**Q: Apakah bisa custom waktu absen per kelas?**  
A: Tidak untuk versi 1.0. Semua kelas menggunakan setting waktu yang sama.

**Q: Bagaimana cara export semua data absensi?**  
A: Menu Reports > Custom Report > Pilih date range full semester/tahun > Export Excel.

---

## Troubleshooting

### Scanner Issues

| Problem | Solution |
|---------|----------|
| Webcam tidak muncul | 1. Check browser permission<br>2. Pastikan tidak ada app lain yang pakai webcam<br>3. Coba browser lain (Chrome recommended) |
| QR tidak ke-detect | 1. Pastikan QR tidak blur<br>2. Adjust jarak (10-30cm)<br>3. Improve lighting<br>4. Print ulang QR jika rusak |
| Foto terlalu gelap | Tambah lampu di area scanner |
| Scanner lambat | 1. Check internet speed<br>2. Close aplikasi lain<br>3. Restart browser |

### WhatsApp Issues

| Problem | Solution |
|---------|----------|
| Notifikasi tidak terkirim | 1. Check gateway status<br>2. Pastikan gateway connected (scan QR)<br>3. Validate nomor HP format (628xxx) |
| Gateway disconnected | 1. Restart gateway: `npm start`<br>2. Scan ulang QR Code<br>3. Check internet connection |
| QR Code gateway tidak muncul | Akses via browser: `http://localhost:3001/qr` |

### Database/System Issues

| Problem | Solution |
|---------|----------|
| "Database connection error" | 1. Check MySQL service running<br>2. Check .env credentials<br>3. Run: `php artisan config:cache` |
| "500 Internal Server Error" | 1. Check Laravel logs: `storage/logs/laravel.log`<br>2. Run: `php artisan cache:clear`<br>3. Check file permissions: `chmod -R 775 storage` |
| Auto alpha tidak jalan | 1. Check cron job setup<br>2. Test manual: `php artisan attendance:mark-absent`<br>3. Check Laravel scheduler |

### Data Issues

| Problem | Solution |
|---------|----------|
| Data siswa tidak muncul | 1. Check filter (kelas, tanggal)<br>2. Refresh halaman<br>3. Check database: student is_active = 1 |
| Foto tidak tampil | 1. Check storage symlink: `php artisan storage:link`<br>2. Check file exists: `storage/app/attendance/photos/`<br>3. Check file permissions |
| Excel export error | 1. Check disk space<br>2. Check write permission on storage<br>3. Try smaller date range |

---

## Kontak Support

Jika masalah tidak bisa diselesaikan:

1. **Hubungi Admin IT Sekolah**
2. **Email Support:** [it-support@sekolah.ac.id]
3. **Telepon:** [021-xxx-xxxx] (jam kerja)

**Saat laporan, sertakan:**
- Screenshot error (jika ada)
- Waktu kejadian
- Browser yang digunakan
- Langkah-langkah yang sudah dicoba

---

## Lampiran

### Shortcut Keyboard

| Shortcut | Fungsi |
|----------|--------|
| `Alt + S` | Go to Scanner |
| `Alt + D` | Go to Dashboard |
| `Alt + R` | Refresh Dashboard |
| `Ctrl + P` | Print (di halaman QR) |
| `Esc` | Close modal/lightbox |

### Browser yang Didukung

✅ **Supported:**
- Google Chrome 90+
- Microsoft Edge 90+
- Firefox 88+
- Safari 14+ (Mac/iOS)

❌ **Not Recommended:**
- Internet Explorer (any version)
- Opera Mini
- UC Browser

### Minimum System Requirements

**For Scanner PC:**
- CPU: Dual Core 2.0 GHz
- RAM: 4 GB
- Webcam: 720p (minimum)
- Internet: 10 Mbps
- OS: Windows 10/11, Linux, macOS

**For Smartphone (QR Digital):**
- Android 8.0+ atau iOS 12+
- Screen: 4.5" minimum
- Browser: Chrome/Safari

---

**Manual Version:** 1.0  
**Last Updated:** 14 Juni 2026  
**Copyright © 2026 - All Rights Reserved**
