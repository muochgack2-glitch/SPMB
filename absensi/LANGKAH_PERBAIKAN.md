# 🔧 Langkah Perbaikan Gateway Start/Stop

## Masalah Saat Ini

1. **Nginx Error** - Configuration syntax error karena file `enable-php-84.conf` salah
2. **419 CSRF Error** - Token expired saat klik tombol Start/Stop Gateway

---

## ✅ Solusi Lengkap

### LANGKAH 1: Pull Update Terbaru

```bash
cd /www/wwwroot/absensi/Absensi/absensi
git pull origin main
```

**Update yang baru:**
- ✅ `bootstrap/app.php` - CSRF exception ditambahkan
- ✅ `FIX_NGINX_AND_CSRF.md` - Panduan troubleshooting lengkap
- ✅ `fix-nginx-server.sh` - Script otomatis perbaikan nginx

---

### LANGKAH 2: Perbaiki Nginx (PENTING!)

#### Opsi A: Gunakan Script Otomatis (Recommended)

```bash
cd /www/wwwroot/absensi/Absensi/absensi
bash fix-nginx-server.sh
```

Script ini akan:
1. Cari file `enable-php-84.conf` yang bermasalah
2. Backup dan hapus file tersebut
3. Test konfigurasi nginx
4. Restart nginx jika sukses

#### Opsi B: Manual

```bash
# 1. Cek error nginx
nginx -t

# 2. Jika error menyebutkan enable-php-84.conf, cari file tersebut
find /www/server -name "enable-php-84.conf" 2>/dev/null

# 3. Jika ada di /www/server/panel/vhost/nginx/, hapus
rm /www/server/panel/vhost/nginx/enable-php-84.conf

# 4. Atau cek vhost config dan hapus baris include-nya
nano /www/server/panel/vhost/nginx/absensi.smkpgriblora.sch.id.conf
# Cari baris: include /www/server/panel/vhost/nginx/enable-php-84.conf;
# Hapus atau comment dengan # di awal

# 5. Test lagi
nginx -t

# 6. Jika OK, restart
systemctl restart nginx
systemctl status nginx
```

---

### LANGKAH 3: Clear Cache Laravel

```bash
cd /www/wwwroot/absensi/Absensi/absensi
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### LANGKAH 4: Perbaiki CSRF di Browser

**Pilih salah satu:**

#### Cara 1: Incognito/Private Mode (Tercepat)
1. Buka browser **Incognito/Private mode**
2. Buka https://absensi.smkpgriblora.sch.id/whatsapp
3. Login
4. Coba klik tombol **Start Gateway Server**

#### Cara 2: Clear Cache Browser
1. **Chrome/Edge:**
   - Tekan `Ctrl+Shift+Delete`
   - Pilih "Cookies and other site data" + "Cached images and files"
   - Pilih time range: "All time" atau "Last 7 days"
   - Klik "Clear data"

2. **Firefox:**
   - Tekan `Ctrl+Shift+Delete`
   - Pilih "Cookies" + "Cache"
   - Klik "Clear Now"

3. Tutup semua tab untuk situs absensi
4. Buka lagi dan login fresh
5. Coba tombol Start/Stop

---

### LANGKAH 5: Test Gateway Control

1. **Buka halaman:** https://absensi.smkpgriblora.sch.id/whatsapp

2. **Klik "Start Gateway Server (PM2)"**
   - Seharusnya muncul alert: "Gateway berhasil distart!"
   - **TIDAK ada error 419 lagi!**

3. **Refresh status** (tombol Refresh di pojok kanan atas)

4. **Cek via CLI** (untuk memastikan):
   ```bash
   pm2 list
   # Seharusnya ada proses "whatsapp-gateway-absensi" dengan status "online"
   ```

5. **Cek gateway health:**
   ```bash
   curl http://localhost:3001/health
   # Seharusnya return JSON dengan status connected
   ```

---

## 📋 Checklist Keberhasilan

- [ ] Nginx start tanpa error: `nginx -t` → OK
- [ ] Situs bisa diakses: https://absensi.smkpgriblora.sch.id/whatsapp
- [ ] Tombol Start/Stop tidak error 419
- [ ] PM2 list menunjukkan gateway online
- [ ] Gateway health endpoint response OK
- [ ] Bisa send test message

---

## 🚨 Jika Masih Error

### Error 419 Masih Muncul?

1. **Pastikan pull sudah dilakukan:**
   ```bash
   cd /www/wwwroot/absensi/Absensi/absensi
   git log --oneline -1
   # Harus menunjukkan: "fix: add CSRF exception..."
   ```

2. **Pastikan cache sudah clear:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Gunakan browser BERBEDA atau incognito**

4. **Cek Laravel log:**
   ```bash
   tail -50 /www/wwwroot/absensi/Absensi/absensi/storage/logs/laravel.log
   ```

### Nginx Masih Error?

1. **Lihat detail error:**
   ```bash
   nginx -t
   journalctl -xeu nginx.service | tail -30
   ```

2. **Cari semua file enable-php:**
   ```bash
   find /www/server -name "enable-php*.conf"
   ```

3. **Cek vhost config:**
   ```bash
   cat /www/server/panel/vhost/nginx/absensi.smkpgriblora.sch.id.conf | grep include
   ```

4. **Kill dan restart manual:**
   ```bash
   pkill nginx
   systemctl start nginx
   ```

---

## ✨ Setelah Berhasil

Anda bisa:

1. **Start Gateway via UI:**
   - Klik tombol "Start Gateway Server (PM2)"
   - Gateway akan start otomatis

2. **Stop Gateway via UI:**
   - Klik tombol "Stop Gateway Server (PM2)"
   - Gateway akan stop

3. **Monitor Status:**
   - Status connection real-time
   - Uptime dan memory usage
   - QR Code untuk login WhatsApp

4. **Atau tetap gunakan CLI:**
   ```bash
   # Start
   pm2 start /www/wwwroot/absensi/Absensi/whatsapp-server-absensi/server.js --name whatsapp-gateway-absensi
   
   # Stop
   pm2 stop whatsapp-gateway-absensi
   
   # Restart
   pm2 restart whatsapp-gateway-absensi
   
   # Status
   pm2 list
   pm2 show whatsapp-gateway-absensi
   ```

---

## 📞 Bantuan Lanjutan

Jika masih ada masalah, berikan informasi:

```bash
# 1. Nginx status
nginx -t
systemctl status nginx --no-pager | head -10

# 2. PHP version
php -v

# 3. Laravel log terakhir
tail -30 storage/logs/laravel.log

# 4. Git commit terbaru
git log --oneline -3

# 5. PM2 status
pm2 list

# 6. Gateway health
curl http://localhost:3001/health
```

Kirim output dari command di atas untuk diagnosis lebih lanjut.
