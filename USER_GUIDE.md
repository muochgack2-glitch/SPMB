# 📖 User Guide - Backup & Restore System

## Panduan Lengkap Penggunaan Sistem Backup & Restore Database

---

## 🚀 Quick Start

### Akses Halaman Backup
1. Login ke admin panel
2. Klik menu **"Backup & Restore"** di sidebar
3. Anda akan melihat halaman dengan daftar backup dan statistik

---

## 📦 Membuat Backup

### Cara 1: Manual Backup (Buat Backup Baru)
1. Klik tombol **"Create Backup"** di kanan atas
2. Isi catatan/notes (opsional)
3. Klik **"Create Backup"**
4. Tunggu proses selesai
5. Backup baru akan muncul di daftar

**Kapan menggunakan**: Sebelum melakukan perubahan besar pada sistem

### Cara 2: Upload Backup (Import dari File)
1. Klik tombol **"Upload"** di kanan atas
2. Pilih file backup (.sql atau .sql.gz)
3. Isi notes (opsional)
4. Klik **"Upload & Import"**
5. Tunggu upload selesai
6. File akan dianalisis dan ditambahkan ke daftar

**Kapan menggunakan**: Mengimport backup dari server lain atau backup lama

---

## 👁️ Preview Backup

### Melihat Isi Backup Sebelum Restore
1. Cari backup yang ingin dilihat di tabel
2. Klik tombol **"mata" (Preview)** pada baris backup
3. Modal akan terbuka menampilkan:
   - Info backup (nama file, ukuran, umur)
   - Jumlah tabel dan records
   - Jumlah pendaftar, users, dll
   - Perbandingan dengan database saat ini
   - Warning jika ada

**Gunakan preview untuk**:
- Memastikan backup yang tepat
- Melihat umur backup
- Membandingkan data

---

## 🔄 Restore Database

### ⚠️ PERINGATAN: Restore akan menghapus semua data saat ini!

### Langkah-langkah Restore:
1. **Preview dulu** - Klik tombol mata untuk preview
2. **Klik Restore** - Klik tombol **"Restore"** (icon history)
3. **Baca warning** - Baca peringatan dengan teliti
4. **Cek checkbox** - Pastikan "Buat backup otomatis" tercentang (RECOMMENDED!)
5. **Ketik konfirmasi** - Ketik nama database persis: `spmb-laravel`
6. **Klik Restore Now** - Klik tombol merah "Restore Sekarang"
7. **Tunggu proses** - Jangan tutup browser!
8. **Selesai** - Akan redirect ke halaman backup

### Yang Terjadi Saat Restore:
1. ✅ Sistem membuat backup otomatis dari database saat ini
2. ✅ Database saat ini dihapus
3. ✅ Database dari backup di-restore
4. ✅ Migrasi otomatis dijalankan (untuk tabel baru)
5. ✅ Session table dibuat ulang
6. ✅ Backup records dikembalikan

### Setelah Restore:
- Data berubah sesuai backup
- Login mungkin perlu refresh
- Cek data apakah sesuai

---

## 📥 Download Backup

### Cara Download Backup ke Komputer:
1. Cari backup di tabel
2. Klik tombol **"Download"** (icon download hijau)
3. File akan terdownload ke komputer Anda
4. File dalam format `.sql.gz` (compressed)

**Gunakan untuk**:
- Backup ke storage external
- Kirim backup ke server lain
- Arsip offline

---

## 🔒 Verify Backup

### Cek Integritas Backup:
1. Cari backup di tabel
2. Klik tombol **"Verify"** (icon shield kuning)
3. Sistem akan cek:
   - File ada atau tidak
   - MD5 hash cocok atau tidak
   - File corrupted atau tidak
4. Hasil akan ditampilkan

**Lakukan verify jika**:
- Backup sudah lama
- Ragu dengan integritas file
- Sebelum restore penting

---

## 🗑️ Delete Backup

### Cara Hapus Backup:
1. Cari backup yang ingin dihapus
2. Klik tombol **"Delete"** (icon trash merah)
3. Konfirmasi dengan klik OK
4. Ketik **"DELETE"** (huruf besar semua)
5. Backup akan dihapus

**⚠️ PERHATIAN**:
- Tidak bisa dihapus jika backup terbaru
- Harus ada minimal 1 backup di sistem
- Tidak bisa di-undo!

---

## 🔍 Filter & Cari Backup

### Filter by Source Type:
- **All Sources**: Semua backup
- **Manual**: Backup yang dibuat manual
- **Auto**: Backup otomatis dari sistem
- **Pre-Operation**: Backup sebelum restore

### Search:
- Ketik nama file atau notes
- Tekan Enter atau klik tombol cari

### Sort:
- **By Date**: Urutkan berdasarkan tanggal
- **By Size**: Urutkan berdasarkan ukuran
- **By Filename**: Urutkan berdasarkan nama file

### Order:
- **Descending**: Terbaru di atas
- **Ascending**: Terlama di atas

---

## 📊 Statistik Dashboard

### Total Backups
Jumlah total semua backup di sistem

### Total Size
Total ukuran semua file backup (dalam MB/GB)

### Manual Backups
Jumlah backup yang dibuat manual oleh user

### Auto Backups
Jumlah backup yang dibuat otomatis oleh sistem

---

## 📋 Activity Logs

### Akses Activity Logs:
1. Scroll ke bawah halaman backup
2. Klik tombol **"View Activity Logs"**
3. Atau klik tombol **"Back to Backups"** untuk kembali

### Apa yang Dicatat:
- ✅ Backup Created
- ✅ Backup Deleted
- ✅ Restore Started
- ✅ Restore Completed
- ✅ Restore Failed
- ✅ Integrity Check

### Filter Activity Logs:
- **Operation Type**: Filter by jenis operasi
- **Status**: Success / Failed / In Progress
- **From Date**: Dari tanggal
- **To Date**: Sampai tanggal
- **Per Page**: Jumlah per halaman (25/50/100)

### View Details:
- Klik tombol **"Details"** untuk log success
- Klik tombol **"Error"** untuk log failed
- Modal akan terbuka dengan detail lengkap

### Copy to Clipboard:
- Buka details/error modal
- Klik tombol **"Copy to Clipboard"**
- Paste ke notepad untuk simpan

---

## 💡 Tips & Best Practices

### ✅ DO (Lakukan):
1. **Selalu buat backup** sebelum perubahan besar
2. **Preview backup** sebelum restore
3. **Centang "buat backup otomatis"** saat restore
4. **Verify backup** secara berkala
5. **Hapus backup lama** yang tidak diperlukan (hemat space)
6. **Download backup penting** ke storage external
7. **Beri notes** saat buat backup (mudah ingat)

### ❌ DON'T (Jangan):
1. **Jangan restore tanpa preview** dulu
2. **Jangan tutup browser** saat restore
3. **Jangan hapus semua backup** (minimal 1)
4. **Jangan restore backup sangat lama** tanpa cek
5. **Jangan lupa konfirmasi** saat restore
6. **Jangan panic** jika ada error (cek logs)

---

## 🚨 Troubleshooting

### Problem: Backup gagal dibuat
**Solusi**:
- Cek koneksi database
- Cek disk space
- Cek permission folder storage
- Lihat error di activity logs

### Problem: Upload backup gagal
**Solusi**:
- Cek ukuran file (max 500MB)
- Pastikan format .sql atau .sql.gz
- Cek internet connection
- Coba compress file dulu

### Problem: Restore gagal
**Solusi**:
- Cek activity logs untuk error
- Verify backup dulu
- Pastikan backup tidak corrupt
- Coba backup lain
- Contact admin jika masih error

### Problem: Data tidak sesuai setelah restore
**Solusi**:
- Cek backup yang di-restore (benar atau tidak)
- Lihat preview backup dulu
- Restore lagi dengan backup yang tepat
- Gunakan pre-restore backup untuk rollback

### Problem: Session error setelah restore
**Solusi**:
- Clear browser cache
- Logout dan login lagi
- Refresh halaman (Ctrl+F5)
- Close dan buka browser lagi

---

## 🎯 Workflow Umum

### Workflow 1: Backup Rutin
```
1. Login admin panel
2. Klik Backup & Restore
3. Klik Create Backup
4. Isi notes: "Backup rutin [tanggal]"
5. Create
6. Selesai
```

### Workflow 2: Upload Backup Production
```
1. Download backup dari production server
2. Login ke admin panel development
3. Klik Upload
4. Pilih file backup
5. Isi notes: "Production backup [tanggal]"
6. Upload & Import
7. Tunggu selesai
8. Preview untuk cek isi
```

### Workflow 3: Restore ke State Sebelumnya
```
1. Cari backup yang tepat
2. Klik Preview (cek tanggal, pendaftar count)
3. Klik Restore
4. Baca warning
5. Pastikan "buat backup otomatis" tercentang
6. Ketik nama database
7. Restore Now
8. Tunggu selesai
9. Cek data
```

### Workflow 4: Test dengan Backup Production
```
1. migrate:fresh (kosongkan database)
2. Upload backup production
3. Preview untuk cek isi
4. Restore backup production
5. System auto create tabel baru (migrations)
6. Test fitur dengan data real
```

---

## 🔐 Keamanan

### Konfirmasi Diperlukan Untuk:
- ✅ Restore database (ketik nama database)
- ✅ Delete backup (ketik "DELETE")

### Backup Otomatis Dibuat Saat:
- ✅ Sebelum restore (pre-restore backup)

### Data yang Dilindungi:
- ✅ Backup records tidak hilang saat restore
- ✅ Activity logs tidak hilang saat restore
- ✅ MD5 hash untuk verify integrity

---

## 📞 Bantuan

### Jika Mengalami Masalah:
1. **Cek Activity Logs** - Lihat error message
2. **Copy error** - Gunakan tombol copy di modal
3. **Contact Admin** - Kirim error message
4. **Cek dokumentasi** - Baca guide ini lagi

### Informasi yang Perlu Disertakan:
- Screenshot error
- Error message (dari activity logs)
- Apa yang sedang dilakukan
- Backup file yang digunakan
- Browser dan OS yang dipakai

---

## 📚 Referensi

### File Locations:
```
Backups: storage/app/backups/
Logs: storage/logs/
Database: database/database.sqlite
```

### Backup Naming:
```
Manual: manual_spmb-laravel_2026-06-14_120530.sql.gz
Auto: auto_spmb-laravel_2026-06-14_000000.sql.gz
Pre-restore: pre_restore_spmb-laravel_2026-06-14_153045.sql.gz
Uploaded: uploaded_spmb-laravel_2026-06-14_090000.sql.gz
```

### Database Name:
```
spmb-laravel
```

### Max File Size:
```
500 MB (untuk upload)
```

---

## ✅ Checklist Harian

### Setiap Hari:
- [ ] Cek jumlah backup (tidak terlalu banyak)
- [ ] Hapus backup lama jika perlu
- [ ] Buat manual backup sebelum perubahan

### Setiap Minggu:
- [ ] Download backup terbaru ke storage external
- [ ] Verify backup terpenting
- [ ] Cek activity logs untuk error

### Setiap Bulan:
- [ ] Cleanup backup yang sangat lama
- [ ] Test restore di environment development
- [ ] Review storage usage

---

**🎉 Selamat menggunakan sistem Backup & Restore!**

**Note**: Jika ada pertanyaan atau masalah, jangan ragu untuk bertanya atau contact admin sistem.

---

**Version**: 1.0.0  
**Last Updated**: June 14, 2026  
**Author**: Development Team
