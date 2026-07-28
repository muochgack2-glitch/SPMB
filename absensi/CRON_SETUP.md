# Cron Job Setup - Sistem Absensi QR Code Scanner

## Deskripsi

Dokumen ini menjelaskan cara setup cron job untuk menjalankan scheduled tasks Laravel pada sistem absensi QR Code Scanner.

## Scheduled Tasks

Sistem ini menggunakan Laravel Scheduler untuk menjalankan tugas terjadwal. Saat ini ada 1 scheduled task yang aktif:

### 1. Mark Absent Students (attendance:mark-absent)

**Deskripsi:** Menandai siswa sebagai alpha (absent) jika mereka belum melakukan check-in sebelum cutoff time.

**Jadwal:** Setiap hari pada jam 09:00 WIB (default, bisa diubah via settings)

**Command Manual:**
```bash
php artisan attendance:mark-absent
```

**Output:**
- Jumlah siswa yang dicek
- Jumlah siswa yang ditandai sebagai absent
- Jumlah siswa yang sudah tercatat
- Daftar siswa yang ditandai absent (NIS, Nama, Kelas)

---

## Setup Cron Job

### Windows (Task Scheduler)

Untuk Windows, gunakan Task Scheduler untuk menjalankan Laravel Scheduler setiap menit:

1. **Buka Task Scheduler**
   - Tekan `Win + R`
   - Ketik `taskschd.msc` dan Enter

2. **Buat Task Baru**
   - Klik "Create Basic Task"
   - Name: "Laravel Scheduler - Attendance System"
   - Description: "Run Laravel scheduled tasks every minute"

3. **Trigger**
   - Trigger: Daily
   - Start: Today
   - Recur every: 1 days
   - Advanced settings:
     - [x] Repeat task every: 1 minute
     - [x] For a duration of: Indefinitely
     - [x] Enabled

4. **Action**
   - Action: Start a program
   - Program/script: `php`
   - Add arguments: `artisan schedule:run`
   - Start in: `C:\Users\DMCenter\Music\SPMB2\SPMB\absensi`

5. **Settings**
   - [x] Allow task to be run on demand
   - [x] Run task as soon as possible after a scheduled start is missed
   - [x] If the task fails, restart every: 1 minute
   - Attempt to restart up to: 3 times

### Linux/Unix (Crontab)

Untuk Linux/Unix, tambahkan entry berikut ke crontab:

```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut
* * * * * cd /path/to/absensi && php artisan schedule:run >> /dev/null 2>&1
```

**Contoh dengan full path:**
```bash
* * * * * cd /var/www/absensi && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### Verifikasi Setup

Untuk memverifikasi bahwa cron job berjalan dengan baik:

1. **Lihat daftar scheduled tasks:**
   ```bash
   php artisan schedule:list
   ```

2. **Test scheduled task secara manual:**
   ```bash
   php artisan schedule:run
   ```

3. **Lihat log schedule:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## Troubleshooting

### Scheduled task tidak berjalan

**Problem:** Task tidak jalan otomatis meskipun sudah setup cron.

**Solution:**
1. Pastikan cron job sudah terdaftar dan aktif
2. Periksa permission file artisan: `chmod +x artisan`
3. Periksa PHP path: `which php` atau `where php`
4. Periksa log error: `storage/logs/laravel.log`

### Command berhasil manual tapi gagal via cron

**Problem:** Command berhasil saat dijalankan manual (`php artisan attendance:mark-absent`) tapi gagal via scheduler.

**Solution:**
1. Periksa environment variables di cron
2. Pastikan working directory benar
3. Pastikan database connection tersedia
4. Periksa log: `tail -f storage/logs/laravel.log`

### Waktu schedule tidak sesuai

**Problem:** Task berjalan pada waktu yang salah (misalnya timezone berbeda).

**Solution:**
1. Periksa timezone di `routes/console.php`: `->timezone('Asia/Jakarta')`
2. Periksa timezone di `config/app.php`: `'timezone' => 'Asia/Jakarta'`
3. Periksa waktu server: `date` (Linux) atau `Get-Date` (PowerShell)
4. Update cutoff_time di halaman Settings Attendance

---

## Monitoring

### Melihat status scheduled tasks

```bash
# Daftar semua scheduled tasks
php artisan schedule:list

# Test scheduled task tanpa menunggu
php artisan schedule:run

# Test command specific
php artisan attendance:mark-absent
```

### Melihat log hasil scheduled task

```bash
# Linux/Unix
tail -f storage/logs/laravel.log

# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

### Log yang dicatat

Setiap kali scheduled task berhasil/gagal, akan dicatat log:
- Success: `Successfully marked absent students at [timestamp]`
- Failure: `Failed to mark absent students at [timestamp]`

---

## Mengubah Jadwal

Untuk mengubah jadwal mark absent students:

### Method 1: Via Settings Page (Recommended)

1. Login ke aplikasi
2. Buka menu **Settings**
3. Ubah nilai **Cutoff Time** (misalnya dari 09:00 ke 10:00)
4. Save

⚠️ **Note:** Scheduler menggunakan waktu default 09:00. Jika Anda mengubah cutoff_time via settings, Anda perlu update `routes/console.php` secara manual.

### Method 2: Edit Kode

Edit file `routes/console.php`:

```php
Schedule::command('attendance:mark-absent')
    ->dailyAt('10:00') // Ubah jam di sini
    ->timezone('Asia/Jakarta')
    // ...
```

Setelah edit, restart scheduler (untuk Task Scheduler Windows, stop/start task).

---

## Production Checklist

- [x] Cron job terdaftar dan berjalan setiap menit
- [x] Scheduled task terdaftar: `php artisan schedule:list`
- [x] Command manual berhasil: `php artisan attendance:mark-absent`
- [x] Log success/failure tercatat di `storage/logs/laravel.log`
- [x] Timezone sudah sesuai (Asia/Jakarta)
- [x] Cutoff time sudah sesuai (default 09:00)
- [x] Database connection tersedia dari cron environment
- [x] Notification WhatsApp (opsional) sudah berjalan

---

## Contoh Output

### Schedule List

```
$ php artisan schedule:list

  0 9 * * *  php artisan attendance:mark-absent .................... Next Due: 2 hours from now
```

### Command Manual Run

```
$ php artisan attendance:mark-absent

Starting to mark absent students...

✓ Total students checked: 42
✓ Marked as absent: 5
✓ Already recorded: 37
✓ Inactive students skipped: 0

✓ Successfully marked 5 students as absent

Students marked as absent:
  • 24001 - Adi Nugroho (10 RPL)
  • 24010 - Muhammad Rizki (11 RPL)
  • 24022 - Ulfah Hasanah (10 TKJ)
  • 24030 - Budi Santoso (11 TKJ)
  • 24040 - Gilang Pratama (12 TKJ)
```

---

## Referensi

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/11.x/scheduling)
- [Windows Task Scheduler Guide](https://docs.microsoft.com/en-us/windows/win32/taskschd/task-scheduler-start-page)
- [Linux Crontab Guide](https://man7.org/linux/man-pages/man5/crontab.5.html)

---

**Last Updated:** 2026-06-14
**Version:** 1.0
