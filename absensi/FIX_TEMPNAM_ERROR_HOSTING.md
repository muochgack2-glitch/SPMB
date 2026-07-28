# 🔧 Fix Error: tempnam() - File Created in System's Temporary Directory

## 📋 Error Yang Terjadi

```
ErrorException
tempnam(): file created in the system's temporary directory
HTTP 500 Internal Server Error
```

**URL:** https://absensi.smkpgriblora.sch.id

---

## 🎯 Penyebab Error

Error ini terjadi karena Laravel tidak memiliki permission yang cukup untuk membuat file temporary di server hosting. Biasanya disebabkan oleh:

1. ❌ Permission direktori `storage/` tidak 775 atau 777
2. ❌ Permission direktori `bootstrap/cache/` tidak 775 atau 777
3. ❌ Ownership file tidak sesuai dengan user web server
4. ❌ System temp directory tidak accessible

---

## ✅ SOLUSI 1: Fix Permission via SSH/Terminal

### Langkah 1: Login ke Server via SSH

```bash
ssh username@your-server-ip
```

### Langkah 2: Masuk ke Direktori Project

```bash
cd /www/wwwroot/absensi/Absensi
# atau sesuaikan dengan path hosting Anda
```

### Langkah 3: Set Permission yang Benar

```bash
# Berikan permission write untuk storage dan cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Atau jika masih error, gunakan 777 (less secure tapi works)
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Langkah 4: Set Ownership yang Benar

```bash
# Ganti www-data dengan user web server Anda (bisa www, nginx, apache, dll)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# Cek user web server dengan command:
ps aux | grep nginx
# atau
ps aux | grep apache
```

### Langkah 5: Clear Cache Laravel

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Jika ada error "file_put_contents", jalankan manual:
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
```

---

## ✅ SOLUSI 2: Fix Permission via aaPanel/cPanel File Manager

### Jika Menggunakan aaPanel:

1. **Login ke aaPanel** → klik **Files**
2. **Navigate** ke folder `/www/wwwroot/absensi/Absensi`
3. **Klik kanan** folder `storage` → **Permission** → set ke **755** atau **777**
4. **Centang "Apply to subdirectories"**
5. **Klik OK**
6. **Ulangi** untuk folder `bootstrap/cache`

### Jika Menggunakan cPanel:

1. **Login ke cPanel** → **File Manager**
2. **Navigate** ke folder `public_html/absensi` (atau path project Anda)
3. **Klik kanan** folder `storage` → **Change Permissions**
4. **Set permission:**
   - ☑️ Read (4)
   - ☑️ Write (2)
   - ☑️ Execute (1)
   - Untuk Owner, Group, dan Others
   - Total = **777** (atau minimal 755)
5. **Centang "Recurse into subdirectories"**
6. **Click Change Permissions**
7. **Ulangi** untuk `bootstrap/cache`

---

## ✅ SOLUSI 3: Fix via .htaccess (Alternative)

Tambahkan di file `.htaccess` di root project:

```apache
<IfModule mod_rewrite.c>
    # Set custom temp directory
    php_value upload_tmp_dir /www/wwwroot/absensi/Absensi/storage/app/temp
    php_value sys_temp_dir /www/wwwroot/absensi/Absensi/storage/app/temp
</IfModule>
```

**Catatan:** Sesuaikan path dengan lokasi project Anda di hosting.

Lalu buat folder temp:

```bash
mkdir -p storage/app/temp
chmod 777 storage/app/temp
```

---

## ✅ SOLUSI 4: Update config/session.php (Laravel Config)

Edit file `config/session.php`, cari section `files` dan ubah path:

```php
'files' => env('SESSION_FILES_PATH', storage_path('framework/sessions')),
```

Pastikan folder `storage/framework/sessions` exist dan writable:

```bash
mkdir -p storage/framework/sessions
chmod 777 storage/framework/sessions
```

---

## ✅ SOLUSI 5: Set Environment Variable di .env

Tambahkan di file `.env`:

```env
# Temp Directory Configuration
SESSION_FILES_PATH="${APP_ROOT}/storage/framework/sessions"
TEMP_PATH="${APP_ROOT}/storage/app/temp"
```

---

## 🔍 VERIFIKASI - Cek Permission Sudah Benar

### Via SSH/Terminal:

```bash
# Cek permission storage
ls -la storage/

# Output yang benar:
# drwxrwxr-x  storage/
# atau
# drwxrwxrwx  storage/

# Cek permission bootstrap/cache
ls -la bootstrap/cache/

# Cek ownership
ls -la | grep storage
# Harus owned by www-data atau user web server
```

### Via Browser:

Buat file `check-permission.php` di root project:

```php
<?php
// check-permission.php

$directories = [
    'storage/framework/sessions' => storage_path('framework/sessions'),
    'storage/framework/cache' => storage_path('framework/cache'),
    'storage/framework/views' => storage_path('framework/views'),
    'storage/logs' => storage_path('logs'),
    'bootstrap/cache' => base_path('bootstrap/cache'),
];

echo "<h1>Permission Check</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Permission</th></tr>";

foreach ($directories as $name => $path) {
    $exists = is_dir($path) ? '✅ Yes' : '❌ No';
    $writable = is_writable($path) ? '✅ Yes' : '❌ No';
    $perms = file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>$exists</td>";
    echo "<td>$writable</td>";
    echo "<td>$perms</td>";
    echo "</tr>";
}

echo "</table>";

// Test tempnam
echo "<h2>Test tempnam()</h2>";
try {
    $temp = tempnam(sys_get_temp_dir(), 'test');
    echo "✅ tempnam() works: $temp";
    unlink($temp);
} catch (Exception $e) {
    echo "❌ tempnam() error: " . $e->getMessage();
}
?>
```

Akses: `https://absensi.smkpgriblora.sch.id/check-permission.php`

---

## 🚀 SOLUSI LENGKAP - Script Auto Fix

Buat file `fix-permissions.sh` di root project:

```bash
#!/bin/bash

echo "🔧 Fixing Laravel Permissions..."

# Set correct permissions
echo "📁 Setting storage permissions..."
chmod -R 775 storage
chown -R www-data:www-data storage

echo "📁 Setting bootstrap/cache permissions..."
chmod -R 775 bootstrap/cache
chown -R www-data:www-data bootstrap/cache

# Create necessary directories
echo "📁 Creating framework directories..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/temp

# Set permissions for framework directories
chmod -R 775 storage/framework
chmod -R 775 storage/logs
chmod -R 775 storage/app

# Clear Laravel cache
echo "🗑️ Clearing Laravel cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache

echo "✅ Permissions fixed!"
echo ""
echo "📊 Permission Status:"
ls -la storage/
echo ""
ls -la bootstrap/cache/
```

Jalankan:

```bash
chmod +x fix-permissions.sh
./fix-permissions.sh
```

---

## 🎯 CHECKLIST - Pastikan Semua Ini Sudah Dilakukan

- [ ] **Permission storage/** → 775 atau 777
- [ ] **Permission bootstrap/cache/** → 775 atau 777
- [ ] **Ownership** → www-data:www-data (atau user web server)
- [ ] **Directory exists:**
  - [ ] storage/framework/sessions
  - [ ] storage/framework/cache
  - [ ] storage/framework/views
  - [ ] storage/logs
  - [ ] bootstrap/cache
- [ ] **Cache cleared:**
  - [ ] php artisan cache:clear
  - [ ] php artisan config:clear
  - [ ] php artisan view:clear
- [ ] **Environment:**
  - [ ] APP_ENV=production
  - [ ] APP_DEBUG=false
  - [ ] APP_KEY generated

---

## 🆘 Jika Masih Error Setelah Semua Solusi

### 1. Cek PHP Configuration

Buat file `phpinfo.php`:

```php
<?php
phpinfo();
?>
```

Akses: `https://absensi.smkpgriblora.sch.id/phpinfo.php`

Cari:
- `sys_temp_dir` → Harus writable
- `upload_tmp_dir` → Harus writable
- `open_basedir` → Harus include temp dir

### 2. Cek Log Error

```bash
# Cek Laravel log
tail -f storage/logs/laravel.log

# Cek Nginx error log
tail -f /var/log/nginx/error.log

# Cek PHP-FPM error log
tail -f /var/log/php8.2-fpm.log
```

### 3. Kontak Hosting Support

Jika semua solusi di atas tidak work, kemungkinan ada restriction dari hosting provider. Hubungi support dan minta:

- Enable write access untuk `storage/` dan `bootstrap/cache/`
- Pastikan `sys_temp_dir` accessible
- Disable `open_basedir` restriction untuk Laravel
- Pastikan PHP-FPM pool user sama dengan file owner

---

## 📞 Need Help?

Jika masih ada masalah, kirimkan screenshot dari:

1. Output command `ls -la storage/`
2. Output command `ls -la bootstrap/cache/`
3. File `.env` configuration (hide sensitive data)
4. Output dari `phpinfo.php` (bagian temp dir)
5. Laravel log: `storage/logs/laravel.log`

---

**Document Version:** 1.0  
**Last Updated:** 2026-07-28  
**Status:** Ready to Use

